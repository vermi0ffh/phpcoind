<?php
namespace Vermi0ffh\Coin\Payload;


use Vermi0ffh\Coin\Component\NetworkAddressTimestamp;
use Vermi0ffh\Coin\Network\Payload;
use Vermi0ffh\Coin\Annotation\Set;

class Addr implements Payload {

    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Component\NetworkAddressTimestamp")
     * @var NetworkAddressTimestamp[]
     */
    public $addr_list;
} 