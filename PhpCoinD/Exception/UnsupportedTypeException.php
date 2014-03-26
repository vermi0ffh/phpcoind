<?php
namespace PhpCoinD\Exception;


use Aza\Components\Socket\Exceptions\Exception;

class UnsupportedTypeException extends Exception {
    public function __construct($type) {

        parent::__construct('Type not supported : ' . $type);
    }
} 