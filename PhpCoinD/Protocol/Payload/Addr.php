<?php
namespace PhpCoinD\Protocol\Payload;


use PhpCoinD\Protocol\Component\NetworkAddressTimestamp;
use PhpCoinD\Protocol\Packet\Payload;

class Addr implements Payload {

    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Component\NetworkAddressTimestamp")
     * @var NetworkAddressTimestamp[]
     */
    public $addr_list;
} 