<?php
namespace PhpCoinD\Protocol\Payload;


use PhpCoinD\Protocol\Packet\Payload;
class Ping implements Payload
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     * @var int
     */{
    public $nonce;
} 