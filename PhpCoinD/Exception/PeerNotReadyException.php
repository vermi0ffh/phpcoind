<?php

namespace PhpCoinD\Exception;


class PeerNotReadyException extends \Exception{
    public function __construct() {
        parent::__construct("The peer is not ready yet");
    }
} 