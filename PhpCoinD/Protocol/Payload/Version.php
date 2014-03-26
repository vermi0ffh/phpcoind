<?php

namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Component\NetworkAddress;
use PhpCoinD\Protocol\Packet\Payload;

class Version implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $version;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $services;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $timestamp;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\NetworkAddress")
     * @var $addr_recv NetworkAddress;
     */
    public $addr_recv;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\NetworkAddress")
     * @var $addr_recv NetworkAddress;
     */
    public $addr_from;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $nonce;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $user_agent;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $start_height;

} 