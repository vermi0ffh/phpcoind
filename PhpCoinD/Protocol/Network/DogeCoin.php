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

namespace PhpCoinD\Protocol\Network;

use PhpCoinD\Protocol\Component\BlockHeaderShort;
use PhpCoinD\Protocol\Component\Hash,
    PhpCoinD\Protocol\Component\OutPoint,
    PhpCoinD\Protocol\Component\TxIn,
    PhpCoinD\Protocol\Component\TxOut;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Payload\Block,
    PhpCoinD\Protocol\Payload\Tx;
use PhpCoinD\Storage\Store;

class DogeCoin implements Network {
    /**
     * @var int
     */
    protected $_nonce;


    /**
     * @var Store
     */
    protected $_store;


    public function __construct() {
        $this->_nonce = rand(0, PHP_INT_MAX);
    }

    /**
     * Create the genesis block for the network
     * @return Block
     */
    public function createGenesisBlock() {
        // Create the genesis block
        $genesis_block = new Block();
        $block_header = new BlockHeaderShort();
        $block_header->version = 1;
        $block_header->prev_block = new Hash(hex2bin('0000000000000000000000000000000000000000000000000000000000000000'));
        $block_header->merkle_root = new Hash(hex2bin('696ad20e2dd4365c7459b4a4a5af743d5e92c6da3229e6532cd605f6533f2a5b'));
        $block_header->timestamp = 1386325540;
        $block_header->bits = 0x1e0ffff0;
        $block_header->nonce = 99943;
        $genesis_block->setBlockHeader($block_header);

        // Transaction
        $tx = new Tx();
        $tx->version = 2;

        // Input transaction
        $tx_in = new TxIn();
        $tx_in->outpoint = new OutPoint();
        $tx_in->outpoint->hash = new Hash(hex2bin('0000000000000000000000000000000000000000000000000000000000000000'));
        $tx_in->outpoint->index = 486604799;

        // Output transaction
        $tx_out = new TxOut();
        $tx_out->value = 88 * 100000000;
        $tx_out->pk_script = hex2bin('040184710fa689ad5023690c80f3a49c8f13f8d45b8c857fbcbc8bc4a8e4d3eb4b10f4d4604fa08dce601aaf0f470216fe1b51850b4acf21b179c45070ac7b03a9');

        $tx->addTxIn($tx_in);
        $tx->addTxOut($tx_out);

        // Lock time set to origin
        $tx->lock_time = 0;

        // Add 1 transaction
        $genesis_block->tx = array($tx);

        return $genesis_block;
    }

    /**
     * The client version advertised
     * @return int
     */
    public function getClientVersion() {
        return 1 * 1000000 + 6 * 10000 + 0 * 100 + 0;
    }

    /**
     * The binary representation of the genesis block
     * @return Hash
     */
    public function getGenesisBlockHash() {
        return new Hash(hex2bin('9156352c1818b32e90c9e792efd6a11a82fe7956a630f03bbee236cedae3911a'));
    }

    /**
     * Get the number of blocks currentrly stored
     * @return int
     */
    public function getHeight() {
        return $this->getStore()->countBlocks();
    }

    /**
     * Return the Hash of the last block received
     * @return Hash
     */
    public function getLastBlockHash() {
        return $this->getStore()->getLastBlock()->block_hash;
    }

    /**
     * The magic value for packet header
     * @return int
     */
    public function getMagicValue() {
        return 0xc0c0c0c0;
    }

    /**
     * Return the hash of the next checkpoint (if possible)
     * @return Hash
     */
    public function getNextCheckPoint() {
        if ($this->getHeight() == 0) {
            return $this->getGenesisBlockHash();
        } else if ($this->getHeight() <= 42279) {
            return new Hash(hex2bin('3a4d13c36ea8b9e4e8518bbd781540efc9d26a95ef8475e82262a439efc34484'));
        } else if ($this->getHeight() <= 42400) {
            return new Hash(hex2bin('b45272501fb44274161970af94c3bdc01f7cdffd1c36f9a6d4e6d97ec1b77b55'));
        } else if ($this->getHeight() <= 104679) {
            return new Hash(hex2bin('cf011d9acec80607c1cf61128eb01ecb767b57398cec8f89984bd490ae87eb35'));
        }

        // No more checkpoints !
        return null;
    }

    /**
     * Get the current nonce
     * @return int
     */
    public function getNonce() {
        return $this->_nonce;
    }

    /**
     * The protocol version
     * @return int
     */
    public function getProtocolVersion() {
        return 70002;
    }

    /**
     * @return Store
     */
    public function getStore() {
        return $this->_store;
    }

    /**
     * @param Store $store
     */
    public function setStore($store) {
        $this->_store = $store;
    }
}