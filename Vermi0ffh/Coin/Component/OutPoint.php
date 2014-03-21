<?php
namespace Vermi0ffh\Coin\Component;

use Vermi0ffh\Coin\Annotation\Serializable;

class OutPoint {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\Hash")
     * @var Hash
     */
    public $hash;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $index;
} 