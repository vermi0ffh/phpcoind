<?php
namespace Vermi0ffh\Coin\Util\Impl;
use Addendum\ReflectionAnnotatedClass;
use Addendum\ReflectionAnnotatedMethod;
use Addendum\ReflectionAnnotatedProperty;
use Exception;
use Vermi0ffh\Coin\Annotation\MinVersion;
use Vermi0ffh\Coin\Annotation\Serializable;
use Vermi0ffh\Coin\Util\Serializer;
use Vermi0ffh\Exception\ClassNameFunctionNotDefined;
use Vermi0ffh\Exception\ClassNotFoundException;


/**
 * Serialize and unzerialize objects based on annotations
 * @package Vermi0ffh\Coin\Util
 */
abstract class AnnotatorObjectSerializer implements Serializer {
    /**
     * Transform name of properties like this : test_property => TestProperty
     * @param string $property_name
     * @return string
     */
    protected function propertyNameTransformer($property_name) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property_name)));
    }

    /**
     * Get the value of a property of an object. If a method getPropertyName is present
     * this method is used. Else, the value is taken directly.
     * @param ReflectionAnnotatedProperty $property
     * @param object $object
     * @return mixed
     */
    protected function getValue($property, $object) {
        $getValueFuncName = 'get' . $this->propertyNameTransformer($property->getName());

        // Call the getXXX method
        if (method_exists($object, $getValueFuncName)) {
            return call_user_func(array($object, $getValueFuncName));
        }

        // Return value directly
        return $property->getValue($object);
    }

    /**
     * Set the value of a property of an object. If a method setPropertyName is present
     * this method is used. Else, the value is put directly.
     * @param ReflectionAnnotatedProperty $property
     * @param object $object
     * @param mixed $value
     */
    protected function setValue($property, $object, $value) {
        $setValueFuncName = 'set' . $this->propertyNameTransformer($property->getName());

        // Call the getXXX method
        if (method_exists($object, $setValueFuncName)) {
            call_user_func(array($object, $setValueFuncName), $value);
        } else {
            $property->setValue($object, $value);
        }
    }


    /**
     * Read an object with the Reader Functions
     * @param resource $stream
     * @param string $class_name The name of the class we want to read
     * @throws \Vermi0ffh\Exception\ClassNotFoundException
     * @throws \Vermi0ffh\Exception\ClassNameFunctionNotDefined
     * @return mixed An instance of the asked class
     */
    public function read_object($stream, $class_name) {
        // Parse annotations on class
        $reflection = new ReflectionAnnotatedClass($class_name);

        // Create a new instance of the given class
        $object = $reflection->newInstance();

        /////////////////////////////////////////
        // Find if serialization is handled by the object itself
        $methods = $reflection->getMethods();
        /** @var $method ReflectionAnnotatedMethod */
        foreach($methods as $method) {
            if ($method->hasAnnotation('Vermi0ffh\Coin\Annotation\Unserialize')) {
                // We found the method handling the unserialization
                $unserialized = call_user_func(array(
                    $object,
                    $method->getName(),
                ), $this, $stream);

                // Object was unserialized successfully by the method, time to stop
                if ($unserialized) {
                    return $object;
                }
            }

        }

        /////////////////////////////////////////
        // Get all properties of the classe
        $properties = $reflection->getProperties();

        /** @var $property ReflectionAnnotatedProperty */
        foreach($properties as $property) {
            $annotations = $property->getAllAnnotations();

            /** @var $type_annotation Serializable */
            $type_annotation = null;

            // Parse all annotations to find a usable one
            foreach($annotations as $annotation) {
                if (is_a($annotation, 'Vermi0ffh\Coin\Annotation\Serializable') ) {
                    // We found it !
                    $type_annotation = $annotation;
                    break;
                }
            }

            // We didn't found an annotation containing the type
            if ($type_annotation == null) {
                continue;
            }

            /////////////////////////////////////
            // Check if this property is conditional
            if ($property->hasAnnotation('Vermi0ffh\Coin\Annotation\MinVersion')) {
                /** @var $min_version_annotation MinVersion */
                $min_version_annotation = $property->getAnnotation('Vermi0ffh\Coin\Annotation\MinVersion');

                /** @var $current_version_field ReflectionAnnotatedProperty */
                try {
                    $current_version_field = $reflection->getProperty($min_version_annotation->field);
                    // Get current value of the field
                    $current_version = $this->getValue($current_version_field, $object);

                    // Skip the field, the version is not sufficient
                    if ($current_version < $min_version_annotation->min) {
                        continue;
                    }
                } catch (Exception $e) {
                    /* Exception ? We just skip this */
                }
            }

            // Get Serialized type
            $type = $type_annotation->type;

            // Init value to null
            $value = null;

            if (!method_exists($this, 'read_' .$type)) {
                if (interface_exists($type)) {
                    // We try to load an interface, we must find the correct class
                    $get_class_name_func = 'get' . $this->propertyNameTransformer($property->getName()) . 'ClassName';

                    if (!method_exists($object, $get_class_name_func)) {
                        // Can't get class name !
                        throw new ClassNameFunctionNotDefined($reflection->getName(), $property->getName(), $type);
                    }

                    // Get the class name from the method
                    $type = call_user_func(array(
                        $object,
                        $get_class_name_func,
                    ));
                }

                // Get current classe namespace
                $current_namespace = $reflection->getNamespaceName();

                if (class_exists($type, true) || class_exists($current_namespace . '\\' . $type, true)) {
                    // Add current namespace if needed
                    if (!class_exists($type, true)) {
                        $type = $current_namespace . '\\' . $type;
                    }

                    // If the type is a class, we use the object reader
                    $value = $this->read_object($stream, $type);
                } else {
                    // Type not defined
                    throw new ClassNotFoundException($type);
                }
            } else {
                // Compute parameters for the read function
                $params = get_object_vars($type_annotation);
                if (isset($params['type'])) {
                    unset($params['type']);
                }
                unset($params['value']);

                // Get only values of parameters
                $params = count($params) > 0 ? array_values($params) : array();
                $params = array_merge(array($stream), $params);

                // Known type, unserialize the content
                $value = call_user_func_array(array(
                    $this,
                    'read_' .$type
                ), $params);
            }

            // Set the value
            $this->setValue($property, $object, $value);
        }

        // Return the new instance
        return $object;
    }


    /**
     * Write an object with the Writer Functions
     * @param resource $stream
     * @param object $object An instance of a class we want to serialize
     * @return mixed
     */
    public function write_object($stream, $object) {
        // Parse annotations on object
        $reflection = new ReflectionAnnotatedClass($object);

        /////////////////////////////////////////
        // Find if serialization is handled by the object itself
        $methods = $reflection->getMethods();
        /** @var $method ReflectionAnnotatedMethod */
        foreach($methods as $method) {
            if ($method->hasAnnotation('Vermi0ffh\Coin\Annotation\Serialize')) {
                // We found the method handling the serialization
                $serialized = call_user_func(array(
                    $object,
                    $method->getName(),
                ), $this, $stream);

                // Object was serialized successfully by the method, time to stop
                if ($serialized) {
                    return;
                }
            }

        }

        /////////////////////////////////////////
        // Get all properties of the classe
        $properties = $reflection->getProperties();

        /** @var $property ReflectionAnnotatedProperty */
        foreach($properties as $property) {
            $annotations = $property->getAllAnnotations();

            /** @var $type_annotation Serializable */
            $type_annotation = null;

            // Parse all annotations to find a usable one
            foreach($annotations as $annotation) {
                if (is_a($annotation, 'Vermi0ffh\Coin\Annotation\Serializable') ) {
                    // We found it !
                    $type_annotation = $annotation;
                    break;
                }
            }

            // We didn't found an annotation containing the type
            if ($type_annotation == null) {
                continue;
            }

            /////////////////////////////////////
            // Get Serialized type
            $type = $type_annotation->type;
            $value = $this->getValue($property, $object);

            /////////////////////////////////////
            // Padding if needed
            if (isset($type_annotation->length)) {
                // Ensure the value has the right length
                if (strlen($value) < $type_annotation->length) {
                    // Pad with null
                    $value = str_pad($value, $type_annotation->length, "\0", STR_PAD_RIGHT);
                } else if (strlen($value) > $type_annotation->length) {
                    // Trucate data
                    $value = substr($value, 0, $type_annotation->length);
                }
            }

            // If needed, use the object serializer
            if (!method_exists($this, 'write_' .$type) && is_object($value)) {
                $type = 'object';
            }

            if (method_exists($this, 'write_' .$type)) {
                // Serialize the content
                call_user_func(array(
                    $this,
                    'write_' .$type
                ), $stream, $value);
            }
        }
    }
}