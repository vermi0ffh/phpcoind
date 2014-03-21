<?php

namespace Vermi0ffh\Coin\Component;

use Vermi0ffh\Coin\Component\Hash;
use Vermi0ffh\Coin\Annotation\Serializable;

class BlockHeaderShort {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $version;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\Hash")
     * @var Hash
     */
    public $prev_block;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\Hash")
     * @var Hash
     */
    public $merkle_root;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $timestamp;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $bits;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $nonce;
}
