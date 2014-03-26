<?php

namespace PhpCoinD\Exception;


use \Exception;

class PayloadParseException extends \Exception {
    public function __construct(Exception $e) {
        parent::__construct("An error occured while parsing the payload", 0, $e);
    }
} 