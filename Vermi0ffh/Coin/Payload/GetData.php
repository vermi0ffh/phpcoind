<?php
namespace Vermi0ffh\Coin\Payload;


use Vermi0ffh\Coin\Network\Payload;
use Vermi0ffh\Coin\Annotation\Set;

class GetData implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Network\Payload\Inv")
     * @var Inv[]
     */
    public $set_inv;
} 