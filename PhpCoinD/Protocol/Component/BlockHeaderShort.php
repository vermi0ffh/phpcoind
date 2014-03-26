<?php

namespace PhpCoinD\Protocol\Component;

class BlockHeaderShort {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $version;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\Hash")
     * @var Hash
     */
    public $prev_block;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\Hash")
     * @var Hash
     */
    public $merkle_root;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $timestamp;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $bits;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $nonce;
}
