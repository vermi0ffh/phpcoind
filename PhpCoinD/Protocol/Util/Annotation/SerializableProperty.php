<?php
/**
 * Created by PhpStorm.
 * User: ari
 * Date: 27/03/14
 * Time: 13:31
 */

namespace PhpCoinD\Protocol\Util\Annotation;


use Addendum\ReflectionAnnotatedProperty;
use PhpCoinD\Annotation\Serializable;
use ReflectionProperty;

class SerializableProperty extends ReflectionAnnotatedProperty {
    /**
     * Transform name of properties like this : test_property => TestProperty
     * @param string $property_name
     * @return string
     */
    protected function propertyNameTransformer($property_name) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $property_name)));
    }


    public function __construct($class, $name) {
        parent::__construct($class, $name);
    }

    /**
     * Get the value of a property of an object. If a method getPropertyName is present
     * this method is used. Else, the value is taken directly.
     * @param mixed $object
     * @return mixed
     */
    public function getValue($object) {
        $getValueFuncName = 'get' . $this->propertyNameTransformer($this->getName());

        // Call the getXXX method
        if (method_exists($object, $getValueFuncName)) {
            return call_user_func(array($object, $getValueFuncName));
        }

        // Return value directly
        return ReflectionProperty::getValue($object);
    }

    /**
     * Get the first annotation implementation Serializable
     * @return null|Serializable
     */
    public function getSerializableAnnotation() {
        $annotations = $this->getAllAnnotations();

        /** @var $type_annotation Serializable */
        $type_annotation = null;

        // Parse all annotations to find a usable one
        foreach($annotations as $annotation) {
            if (is_a($annotation, 'PhpCoinD\Annotation\Serializable') ) {
                // We found it !
                return $annotation;
            }
        }

        return null;
    }

    /**
     * Set the value of a property of an object. If a method setPropertyName is present
     * this method is used. Else, the value is put directly.
     * @param object $object
     * @param mixed $value
     */
    public function setValue( $object, $value) {
        $setValueFuncName = 'set' . $this->propertyNameTransformer($this->getName());

        // Call the getXXX method
        if (method_exists($object, $setValueFuncName)) {
            call_user_func(array($object, $setValueFuncName), $value);
        } else {
            ReflectionProperty::setValue($object, $value);
        }
    }
} 