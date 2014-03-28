<?php

namespace PhpCoinD\Storage\Impl\MongoStore;
use MongoBinData;
use PhpCoinD\Protocol\Util\Annotation\SerializableAnnotatedClass;
use PhpCoinD\Protocol\Util\Annotation\SerializableProperty;


/**
 * This class handle object transformation for MongoDB
 * Annotations are used to know wich fields are keeped
 * @package PhpCoinD\Storage\Impl\MongoStore
 */
class ObjectTransformer {
    /**
     * Take an object as input, transform it to an object ready to give to Mongo
     * @param object $object
     * @return array
     */
    public function toMongo($object) {
        $ret = array();

        // Parse annotations on object
        $reflection = new SerializableAnnotatedClass($object);

        $ret['_class'] = $reflection->getName();

        /////////////////////////////////////////
        // Get all properties of the classe
        $properties = $reflection->getSerializableProperties();

        /** @var $property SerializableProperty */
        foreach($properties as $property) {
            $annotations = $property->getSerializableAnnotation();
            $type = $annotations->type;

            if ($type == 'set') {
                // Convert set to array
                $value = array();

                // Add set elements
                $obj_val = $property->getValue($object);
                if (is_array($obj_val)) {
                    foreach($property->getValue($object) as $set_val) {
                        $value[] = $this->toMongo($set_val);
                    }
                }
            } else if (class_exists($type)) {
                // It's a sub-class !
                $value = $this->toMongo($property->getValue($object));
            } else {
                $value = $property->getValue($object);

                // Convert non-UTF8 strings to MongoBinData
                if (is_string($value) && (!mb_check_encoding($value, 'UTF-8') || strpos($value, "\0") !== false) ) {
                    $value = new MongoBinData($value, MongoBinData::CUSTOM);
                }
            }

            // Set value of property
            $ret[ $property->getName() ] = $value;
        }

        return $ret;
    }


    /**
     * Convert an object read from MongoDB to it's original class
     * @param array $object
     * @return mixed
     */
    public function fromMongo($object) {
        $reflection = new SerializableAnnotatedClass($object['_class']);

        // Create a new instance of the object
        $ret = $reflection->newInstance();

        /////////////////////////////////////////
        // Get all properties of the classe
        $properties = $reflection->getSerializableProperties();

        /** @var $property SerializableProperty */
        foreach($properties as $property) {
            $annotations = $property->getSerializableAnnotation();
            $type = $annotations->type;

            if ($type == 'set') {
                // Convert set to array
                $value = array();

                // Add set elements
                foreach($object[$property->getName()] as $set_val) {
                    $value[] = $this->fromMongo($set_val);
                }
            } else if (class_exists($type)) {
                // It's a sub-class !
                $value = $this->fromMongo($object[$property->getName()]);
            } else {
                $value = $object[$property->getName()];

                // Convert non-UTF8 strings to MongoBinData
                if (is_object($value) && $value instanceof MongoBinData) {
                    $value = $value->bin;
                }
            }

            // Set value of property
            $property->setValue($ret, $value);
        }

        return $ret;
    }
} 