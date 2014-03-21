<?php
namespace Vermi0ffh\Coin\Component;

use Vermi0ffh\Coin\Annotation\Serializable;

class InvVect {
    /**
     * Any data of this type should be ignored
     */
    const OBJECT_ERROR = 0;

    /**
     * Hash is related to a transaction
     */
    const OBJECT_MSG_TX = 1;

    /**
     * Hash is related to a data block
     */
    const OBJECT_MSG_BLOCK = 2;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $type;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\Hash")
     * @var Hash
     */
    public $hash;
} 