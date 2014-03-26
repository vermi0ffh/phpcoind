<?php
namespace PhpCoinD\Exception;

use Exception;

class ClassNameFunctionNotDefined extends Exception {
    /**
     * @param string $class_name
     * @param int $property_name
     * @param string $interface_name
     */
    public function __construct($class_name, $property_name, $interface_name) {
        parent::__construct(sprintf("Can't get class name for property %s.%s . Interface is %s", $class_name, $property_name, $interface_name));
    }
} 