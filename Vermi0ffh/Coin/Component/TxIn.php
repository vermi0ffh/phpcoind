<?php
namespace Vermi0ffh\Coin\Component;

use Vermi0ffh\Coin\Annotation\Serializable;

class TxIn {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\OutPoint")
     * @var OutPoint
     */
    public $outpoint;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
     * @var string
     */
    public $signature;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $sequence;
} 