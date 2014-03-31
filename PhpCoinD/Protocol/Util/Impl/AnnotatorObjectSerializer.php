<?php
/**
 * Copyright (c) 2014 Aurélien RICHAUD
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Created 31/03/14 16:05 by Aurélien RICHAUD
 */

namespace PhpCoinD\Protocol\Util\Impl;

use Addendum\ReflectionAnnotatedMethod;
use Exception;
use PhpCoinD\Annotation\MinVersion;
use PhpCoinD\Protocol\Util\Annotation\SerializableAnnotatedClass,
    PhpCoinD\Protocol\Util\Annotation\SerializableProperty;
use PhpCoinD\Protocol\Util\Serializer;
use PhpCoinD\Exception\ClassNameFunctionNotDefined,
    PhpCoinD\Exception\ClassNotFoundException;

/**
 * Serialize and unzerialize objects based on annotations
 * @package PhpCoinD\Protocol\Util
 */
abstract class AnnotatorObjectSerializer implements Serializer {
    /**
     * Read an object with the Reader Functions
     * @param resource $stream
     * @param string $class_name The name of the class we want to read
     * @throws \PhpCoinD\Exception\ClassNotFoundException
     * @throws \PhpCoinD\Exception\ClassNameFunctionNotDefined
     * @return mixed An instance of the asked class
     */
    public function read_object($stream, $class_name) {
        // Parse annotations on class
        $reflection = new SerializableAnnotatedClass($class_name);

        // Create a new instance of the given class
        $object = $reflection->newInstance();

        /////////////////////////////////////////
        // Find if serialization is handled by the object itself
        $methods = $reflection->getMethods();
        /** @var $method ReflectionAnnotatedMethod */
        foreach($methods as $method) {
            if ($method->hasAnnotation('PhpCoinD\Annotation\Unserialize')) {
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
        $properties = $reflection->getSerializableProperties();

        /** @var $property SerializableProperty */
        foreach($properties as $property) {
            $type_annotation = $property->getSerializableAnnotation();

            /////////////////////////////////////
            // Check if this property is conditional
            if ($property->hasAnnotation('PhpCoinD\Annotation\MinVersion')) {
                /** @var $min_version_annotation MinVersion */
                $min_version_annotation = $property->getAnnotation('PhpCoinD\Annotation\MinVersion');

                try {
                    // Get current value of the field
                    $current_version = $property->getValue($object);

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
                    $get_class_name_func = 'get' . $property->propertyNameTransformed() . 'ClassName';

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
            $property->setValue($object, $value);
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
        $reflection = new SerializableAnnotatedClass($object);

        /////////////////////////////////////////
        // Find if serialization is handled by the object itself
        $methods = $reflection->getMethods();
        /** @var $method ReflectionAnnotatedMethod */
        foreach($methods as $method) {
            if ($method->hasAnnotation('PhpCoinD\Annotation\Serialize')) {
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
        $properties = $reflection->getSerializableProperties();

        /** @var $property SerializableProperty */
        foreach($properties as $property) {
            $type_annotation = $property->getSerializableAnnotation();

            /////////////////////////////////////
            // Get Serialized type
            $type = $type_annotation->type;
            $value = $property->getValue($object);

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

            // Compute parameters for the write function
            $params = get_object_vars($type_annotation);
            if (isset($params['type'])) {
                unset($params['type']);
            }
            unset($params['value']);

            $params = array_merge(array(
                $stream,
                $value,
            ), $params);


            // If needed, use the object serializer
            if (!method_exists($this, 'write_' .$type) && is_object($value)) {
                $type = 'object';
            }

            if (method_exists($this, 'write_' .$type)) {
                // Serialize the content
                call_user_func_array(array(
                    $this,
                    'write_' .$type
                ), $params);
            }
        }
    }
}