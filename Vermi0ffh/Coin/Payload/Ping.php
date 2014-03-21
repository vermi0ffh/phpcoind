<?php
namespace Vermi0ffh\Coin\Payload;


use Vermi0ffh\Coin\Network\Payload;
use Vermi0ffh\Coin\Annotation\Serializable;

class Ping implements Payload
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint64")
     * @var int
     */{
    public $nonce;
} 