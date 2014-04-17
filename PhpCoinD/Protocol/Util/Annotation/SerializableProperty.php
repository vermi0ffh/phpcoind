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

use Addendum\ReflectionAnnotatedProperty;
use PhpCoinD\Annotation\Serializable;
use ReflectionProperty;

class SerializableProperty extends ReflectionAnnotatedProperty {
    public function __construct($class, $name) {
        parent::__construct($class, $name);
    }

    /**
     * Get the value of a property of an object. If a method getPropertyName is present
     * this method is used. Else, the value is taken directly.
     * @param mixed $object
     * @return mixed
     */
    public function getValue($object = null) {
        $getValueFuncName = 'get' . $this->propertyNameTransformed();

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
     * Transform name of properties like this : test_property => TestProperty
     * @return string
     */
    public function propertyNameTransformed() {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getName())));
    }

    /**
     * Set the value of a property of an object. If a method setPropertyName is present
     * this method is used. Else, the value is put directly.
     * @param object $object
     * @param mixed $value
     */
    public function setValue($object, $value = null) {
        $setValueFuncName = 'set' . $this->propertyNameTransformed();

        // Call the getXXX method
        if (method_exists($object, $setValueFuncName)) {
            call_user_func(array($object, $setValueFuncName), $value);
        } else {
            ReflectionProperty::setValue($object, $value);
        }
    }
} 