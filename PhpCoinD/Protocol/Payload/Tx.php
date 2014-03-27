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


    /**
     * Add a new input transaction
     * @param TxIn $tx_in
     */
    public function addTxIn($tx_in) {
        // Init array if needed
        if (!is_array($this->tx_in)) {
            $this->tx_in = array();
        }

        // Add the new input transaction
        $this->tx_in[] = $tx_in;
    }


    /**
     * Add a new input transaction
     * @param TxOut $tx_out
     */
    public function addTxOut($tx_out) {
        // Init array if needed
        if (!is_array($this->tx_out)) {
            $this->tx_out = array();
        }

        // Add the new input transaction
        $this->tx_out[] = $tx_out;
    }
} 