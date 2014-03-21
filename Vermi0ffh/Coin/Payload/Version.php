<?php

namespace Vermi0ffh\Coin\Payload;

use Vermi0ffh\Coin\Annotation\Serializable;
use Vermi0ffh\Coin\Component\NetworkAddress;
use Vermi0ffh\Coin\Network\Payload;

class Version implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $version;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint64")
     */
    public $services;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint64")
     */
    public $timestamp;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\NetworkAddress")
     * @var $addr_recv NetworkAddress;
     */
    public $addr_recv;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\NetworkAddress")
     * @var $addr_recv NetworkAddress;
     */
    public $addr_from;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint64")
     */
    public $nonce;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
     */
    public $user_agent;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $start_height;

} 