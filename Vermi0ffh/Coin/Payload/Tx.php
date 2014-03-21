<?php
namespace Vermi0ffh\Coin\Payload;


use Vermi0ffh\Coin\Component\TxIn;
use Vermi0ffh\Coin\Component\TxOut;
use Vermi0ffh\Coin\Network\Payload;
use Vermi0ffh\Coin\Annotation\Serializable,
    Vermi0ffh\Coin\Annotation\Set;

class Tx implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $version;

    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Component\TxIn")
     * @var TxIn[]
     */
    public $tx_in;

    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Component\TxOut")
     * @var TxOut[]
     */
    public $tx_out;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $lock_time;
} 