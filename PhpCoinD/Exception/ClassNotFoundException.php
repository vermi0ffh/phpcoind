<?php

namespace PhpCoinD\Exception;


use Exception;

class ClassNotFoundException extends Exception {
    public function __construct($class_name) {
        parent::__construct(sprintf("Class not found : ùs", $class_name));
    }
} 