<?php
namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Component\BlockHeader;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Packet\Payload;
use PhpCoinD\Protocol\Component\BlockHeaderShort;

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
     * Compute block hash
     * @param \PhpCoinD\Protocol\Component\BlockHeaderShort $block_header
     */
    public function setBlockHeader($block_header) {
        $this->block_header = $block_header;

        $this->block_hash = $this->block_header->computeBlockHash();
    }


    /**
     * Build a Block object containing only a block header
     * @param BlockHeader $block_header
     * @return \PhpCoinD\Protocol\Payload\Block
     */
    public static function fromBlockHeader($block_header) {
        // Create the new Block object
        $ret = new self();

        // Prepare new header for this block
        $block_header_short = new BlockHeaderShort();
        $block_header_short->version = $block_header->version;
        $block_header_short->prev_block = $block_header->prev_block;
        $block_header_short->merkle_root = $block_header->merkle_root;
        $block_header_short->timestamp = $block_header->timestamp;
        $block_header_short->bits = $block_header->bits;
        $block_header_short->nonce = $block_header->nonce;

        // Set the block header
        $ret->setBlockHeader($block_header_short);

        return $ret;
    }
}