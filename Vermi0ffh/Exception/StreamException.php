<?php
namespace Vermi0ffh\Exception;

use Exception;

class StreamException extends Exception{
    public function __construct($message = null) {
        if ($message === null) {
            $message = "Error reading the stream";
        }

        parent::__construct($message);
    }
} 