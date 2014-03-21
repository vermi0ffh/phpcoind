<?php
namespace Vermi0ffh\Coin\Component;

use Vermi0ffh\Coin\Annotation\Serializable;

class TxOut {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint64")
     * @var int
     */
    public $value;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
     * @var string
     */
    public $pk_script;
} 