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

namespace PhpCoinD\Protocol;

use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Storage\Store;

/**
 * All information needed about a network
 * @package PhpCoinD\Coin
 */
interface Network {
    /**
     * Create the genesis block for the network
     * @return Block
     */
    public function createGenesisBlock();

    /**
     * The client version advertised
     * @return int
     */
    public function getClientVersion();

    /**
     * The binary representation of the genesis block
     * @return Hash
     */
    public function getGenesisBlockHash();


    /**
     * Get the number of blocks currently stored
     * @return int
     */
    public function getHeight();


    /**
     * Return the Hash of the last block received
     * @return Hash
     */
    public function getLastBlockHash();


    /**
     * The magic value for packet header
     * @return int
     */
    public function getMagicValue();


    /**
     * Return the hash of the next checkpoint (if possible)
     * @return Hash
     */
    public function getNextCheckPoint();


    /**
     * Get the current nonce
     * @return int
     */
    public function getNonce();

    /**
     * The protocol version
     * @return int
     */
    public function getProtocolVersion();

    /**
     * @return Store
     */
    public function getStore();


    /**
     * @param Store $store
     */
    public function setStore($store);
} 