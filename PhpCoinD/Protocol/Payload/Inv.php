<?php
namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Component\InvVect;
use PhpCoinD\Protocol\Packet\Payload;

class Inv implements Payload {
    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Component\InvVect")
     * @var InvVect[]
     */
    public $inventory;
} 