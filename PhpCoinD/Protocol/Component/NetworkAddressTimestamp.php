<?php
namespace PhpCoinD\Protocol\Component;

class NetworkAddressTimestamp {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $time;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\NetworkAddress")
     * @var NetworkAddress
     */
    public $network_address;
} 