<?php
namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Packet\Payload;
use PhpCoinD\Protocol\Component\BlockHeaderShort;
use PhpCoinD\Protocol\Util\Impl\DSha256ChecksumComputer;

class Block implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\BlockHeaderShort")
     * @var BlockHeaderShort
     */
    public $block_header;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Payload\Tx")
     * @var Tx[]
     */
    public $tx;

    /**
     * @var Hash
     */
    public $block_hash;

    /**
     * This method is a trick to compute the block hash
     * @param \PhpCoinD\Protocol\Payload\Tx[] $tx
     */
    public function setTx($tx) {
        // We use a DoubleSHA256 Hasher
        $hasher = new DSha256ChecksumComputer();

        // Convert block header to raw string
        $header_str = pack('V', $this->block_header->version)
            . $this->block_header->prev_block->value
            . $this->block_header->merkle_root->value
            . pack('V', $this->block_header->timestamp)
            . pack('V', $this->block_header->bits)
            . pack('V', $this->block_header->nonce);

        $this->block_hash = new Hash($hasher->hash($header_str));

        $this->tx = $tx;
    }
}