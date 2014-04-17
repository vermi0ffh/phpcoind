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

use Monolog\Logger;
use PhpCoinD\Crypt\BlockHasher;
use PhpCoinD\Network\CoinNetworkConnector;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Storage\Store;

/**
 * All information needed about a network
 * @package PhpCoinD\Coin
 */
interface Network extends Blockchain {
    /**
     * Register a new way to connect to the coin network
     * @param CoinNetworkConnector $connector
     */
    public function addNetworkConnector($connector);

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
     * @return \PhpCoinD\Network\CoinNetworkConnector[]
     */
    public function getNetworkConnectors();

    /**
     * Get the block hasher for this coin network
     * @return BlockHasher
     */
    public function getBlockHasher();

    /**
     * The binary representation of the genesis block
     * @return Hash
     */
    public function getGenesisBlockHash();

    /**
     * @return Logger
     */
    public function getLogger();

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
     * A flag telling if the blockchain is sync or not
     * @return boolean
     */
    public function getSynchronized();

    /**
     * Method used to do stuff needed for the network.
     * This method should return "quickly" to prevent blocking of the other networks
     */
    public function run();

    /**
     * @param Store $store
     */
    public function setStore($store);


    /**
     * Change the sync flag of the blockchain
     * @param boolean $synchronized
     */
    public function setSynchronized($synchronized);
} 