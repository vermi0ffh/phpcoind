<?php

namespace Vermi0ffh\Exception;


use Aza\Components\Socket\Exceptions\Exception;

class PayloadParseException extends \Exception {
    public function __construct(Exception $e) {
        parent::__construct("An error occured while parsing the payload", 0, $e);
    }
} 