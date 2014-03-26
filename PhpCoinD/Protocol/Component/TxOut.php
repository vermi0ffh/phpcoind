<?php
namespace PhpCoinD\Protocol\Component;

class TxOut {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     * @var int
     */
    public $value;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     * @var string
     */
    public $pk_script;
} 