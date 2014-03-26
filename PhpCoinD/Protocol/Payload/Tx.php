<?php
namespace PhpCoinD\Protocol\Payload;


use PhpCoinD\Protocol\Component\TxIn;
use PhpCoinD\Protocol\Component\TxOut;
use PhpCoinD\Protocol\Packet\Payload;

class Tx implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $version;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Component\TxIn")
     * @var TxIn[]
     */
    public $tx_in;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Component\TxOut")
     * @var TxOut[]
     */
    public $tx_out;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $lock_time;
} 