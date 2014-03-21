<?php
namespace Vermi0ffh\Coin\Payload;

use Vermi0ffh\Coin\Component\InvVect;
use Vermi0ffh\Coin\Network\Payload;
use Vermi0ffh\Coin\Annotation\Set;

class Inv implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Component\InvVect")
     * @var InvVect[]
     */
    public $inventory;
} 