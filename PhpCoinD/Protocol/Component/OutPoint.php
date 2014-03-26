<?php
namespace PhpCoinD\Protocol\Component;

class OutPoint {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\Hash")
     * @var Hash
     */
    public $hash;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $index;
} 