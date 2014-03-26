<?php
namespace PhpCoinD\Protocol\Component;

class TxIn {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\OutPoint")
     * @var OutPoint
     */
    public $outpoint;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     * @var string
     */
    public $signature;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $sequence;
} 