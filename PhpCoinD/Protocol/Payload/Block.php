<?php
/**
 * Copyright (c) 2014 Aurélien RICHAUD
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Created 31/03/14 16:05 by Aurélien RICHAUD
 */

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
     * @var int
     */
    public $height;

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