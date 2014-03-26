<?php
namespace PhpCoinD\Protocol\Component;

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
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $type;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\Hash")
     * @var Hash
     */
    public $hash;
} 