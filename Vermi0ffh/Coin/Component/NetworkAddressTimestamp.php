<?php
namespace Vermi0ffh\Coin\Component;

use Vermi0ffh\Coin\Annotation\Serializable;
use Vermi0ffh\Coin\Component\NetworkAddress;

class NetworkAddressTimestamp {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $time;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\NetworkAddress")
     * @var NetworkAddress
     */
    public $network_address;
} 