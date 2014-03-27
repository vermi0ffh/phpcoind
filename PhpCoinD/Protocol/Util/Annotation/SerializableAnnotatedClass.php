<?php
namespace PhpCoinD\Protocol\Util\Annotation;


use Addendum\ReflectionAnnotatedClass;
use ReflectionClass;
use ReflectionProperty;

class SerializableAnnotatedClass extends ReflectionAnnotatedClass {
    /**
     * @param ReflectionClass $class
     * @return bool|SerializableAnnotatedClass
     */
    protected function createSerializableAnnotatedClass($class) {
        return ($class !== false) ? new SerializableAnnotatedClass($class->getName()) : false;
    }

    /**
     * @param ReflectionProperty $property
     * @return null|SerializableProperty
     */
    protected function createSerializableProperty($property) {
        return ($property !== null) ? new SerializableProperty($this->getName(), $property->getName()) : null;
    }

    /**
     * Defautl constructor
     * @param mixed $class
     */
    public function __construct($class) {
        parent::__construct($class);
    }

    public function getProperty($name) {
        return $this->createSerializableProperty(parent::getProperty($name));
    }

    public function getProperties($filter = -1) {
        $result = array();
        foreach(parent::getProperties($filter) as $property) {
            $result[] = $this->createSerializableProperty($property);
        }
        return $result;
    }

    /**
     * Get all properties with an annotation instance of 'Serializable'
     * @return SerializableProperty[]
     */
    public function getSerializableProperties() {
        $ret = array();

        /////////////////////////////////////////
        // Get all properties of the classe
        $properties = $this->getProperties();

        /** @var $property SerializableProperty */
        foreach($properties as $property) {
            $type_annotation = $property->getSerializableAnnotation();

            // We didn't found an annotation containing the type
            if ($type_annotation == null) {
                continue;
            }

            /////////////////////////////////////
            // Add property to the list
            $ret[] = $property;
        }

        return $ret;
    }
} 