<?php
namespace Vermi0ffh\Coin\Payload;

use Vermi0ffh\Coin\Component\Hash;
use Vermi0ffh\Coin\Network\Payload;
use Vermi0ffh\Coin\Component\BlockHeaderShort;

use Vermi0ffh\Coin\Annotation\Set;

class Block extends BlockHeaderShort implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Component\Tx")
     * @var Tx[]
     */
    public $tx;

    /**
     * @var Hash
     */
    public $block_hash;

    /**
     * This method is a trick to compute the block hash
     * @param \Vermi0ffh\Coin\Payload\Tx[] $tx
     */
    public function setTx($tx) {
        $this->block_hash = new Hash();

        $this->tx = $tx;
    }
} 