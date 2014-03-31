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