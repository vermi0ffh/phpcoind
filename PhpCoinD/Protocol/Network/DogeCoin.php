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

use Monolog\Logger;
use PhpCoinD\Crypt\BlockHasher;
use PhpCoinD\Crypt\ScryptZend;
use PhpCoinD\Network\CoinNetworkConnector;
use PhpCoinD\Network\CoinPacketHandler;
use PhpCoinD\Network\Impl\DefaultPacketHandler;
use PhpCoinD\Network\Impl\SocketCoinNetworkConnector;
use PhpCoinD\Protocol\Component\BlockHeaderShort;
use PhpCoinD\Protocol\Component\CScript;
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
     * @var BlockHasher
     */
    protected $_block_hasher;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var CoinNetworkConnector[]
     */
    protected $_network_connectors;

    /**
     * @var int
     */
    protected $_nonce;

    /**
     * @var CoinPacketHandler
     */
    protected $_packet_handler;

    /**
     * @var Store
     */
    protected $_store;

    /**
     * Flag : is the blockchain in sync ?
     * @var bool
     */
    protected $_synchronized = false;


    /**
     * @param Logger $logger
     * @param bool $default_connectors
     */
    public function __construct($logger, $default_connectors=true) {
        $this->_logger = $logger;
        $this->_nonce = rand(0, PHP_INT_MAX);

        // Init packet handler
        $this->_packet_handler = new DefaultPacketHandler($this);
        // Init connectors
        $this->_network_connectors = array();
        if ($default_connectors) {
            $this->addNetworkConnector(new SocketCoinNetworkConnector($this->_packet_handler));
        }

        // Prepare the block hasher
        $this->_block_hasher = new BlockHasher();
        $this->_block_hasher->setHashFunc(new ScryptZend());
    }

    public function __destruct() {
        $this->_packet_handler = null;
        $this->_network_connectors = array();
    }


    /**
     * Add a new block to the blockchain
     * @param Block $block
     * @return mixed
     */
    public function addBlock($block) {
        if ($this->isBlockValid($block)) {
            $this->getStore()->addBlock($block);
        }
    }

    /**
     * Register a new way to connect to the coin network
     * @param CoinNetworkConnector $connector
     */
    public function addNetworkConnector($connector) {
        $this->_network_connectors[] = $connector;
    }

    /**
     * Create the genesis block for the network
     * @return Block
     */
    public function createGenesisBlock() {
        // CBlock(hash=1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691, input=010000000000000000000000000000000000000000000000000000000000000000000000696ad20e2dd4365c7459b4a4a5af743d5e92c6da3229e6532cd605f6533f2a5b24a6a152f0ff0f1e67860100, PoW=0000026f3f7874ca0c251314eaed2d2fcf83d7da3acfaacf59417d485310b448, ver=1, hashPrevBlock=0000000000000000000000000000000000000000000000000000000000000000, hashMerkleRoot=5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69, nTime=1386325540, nBits=1e0ffff0, nNonce=99943, vtx=1)
        //   CTransaction(hash=5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69, ver=1, vin.size=1, vout.size=1, nLockTime=0)
        //    CTxIn(COutPoint(0000000000000000000000000000000000000000000000000000000000000000, 4294967295), coinbase 04ffff001d0104084e696e746f6e646f)
        //    CTxOut(nValue=88.00000000, scriptPubKey=040184710fa689ad5023690c80f3a4)
        //  vMerkleTree: 5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69

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
        $tx->version = 1;
        // Lock time set to origin
        $tx->lock_time = 0;

        // Input transaction
        $tx_in = new TxIn();
        $tx_in->outpoint = new OutPoint();
        $tx_in->outpoint->hash = new Hash(hex2bin('0000000000000000000000000000000000000000000000000000000000000000'));
        $tx_in->outpoint->index = 4294967295;
        $tx_in->signature = new CScript();
        $tx_in->signature->raw_data = hex2bin('04ffff001d0104084e696e746f6e646f');
        $tx_in->sequence = 4294967295;

        // Output transaction
        $tx_out = new TxOut();
        $tx_out->value = 88 * 100000000;
        $tx_out->pk_script = new CScript();
        $tx_out->pk_script->raw_data = hex2bin('41040184710fa689ad5023690c80f3a49c8f13f8d45b8c857fbcbc8bc4a8e4d3eb4b10f4d4604fa08dce601aaf0f470216fe1b51850b4acf21b179c45070ac7b03a9ac');


        // Add input/output to transaction
        $tx->addTxIn($tx_in);
        $tx->addTxOut($tx_out);

        // Add 1 transaction
        $genesis_block->tx = array($tx);

        return $genesis_block;
    }

    /**
     * Get a block by it's hash
     * @param Hash $hash
     * @return Block|null
     */
    public function getBlockByHash($hash) {
        return $this->getStore()->getBlockByHash($hash);
    }

    /**
     * Get a block by it's height in the blockchain
     * @param int $height
     * @return Block|null
     */
    public function getBlockByHeight($height) {
        // TODO: Implement getBlockByHeight() method.
    }

    /**
     * Get the block hasher for this coin network
     * @return BlockHasher
     */
    public function getBlockHasher() {
        return $this->_block_hasher;
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
     * Get the height of the blockchain
     * @return int
     */
    public function getCurrentHeight() {
        return $this->getStore()->getHeight();
    }

    /**
     * Return the last block of the blockchain
     * @return Block
     */
    public function getLastBlock() {
        return $this->getStore()->getLastBlock();
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * The magic value for packet header
     * @return int
     */
    public function getMagicValue() {
        return 0xc0c0c0c0;
    }

    /**
     * @return \PhpCoinD\Network\CoinNetworkConnector[]
     */
    public function getNetworkConnectors() {
        return $this->_network_connectors;
    }

    /**
     * Return the hash of the next checkpoint (if possible)
     * @return Hash
     */
    public function getNextCheckPoint() {
        $height = $this->getCurrentHeight();

        if ($height == 0) {
            return $this->getGenesisBlockHash();
        } else if ($height <= 42279) {
            return new Hash(hex2bin('3a4d13c36ea8b9e4e8518bbd781540efc9d26a95ef8475e82262a439efc34484'));
        } else if ($height <= 42400) {
            return new Hash(hex2bin('b45272501fb44274161970af94c3bdc01f7cdffd1c36f9a6d4e6d97ec1b77b55'));
        } else if ($height <= 104679) {
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
     * @return \PhpCoinD\Network\CoinPacketHandler
     */
    public function getPacketHandler() {
        return $this->_packet_handler;
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
     * @return boolean
     */
    public function getSynchronized() {
        return $this->_synchronized;
    }

    /**
     * Check if a block is valid
     * @param Block $block
     * @return bool true is the block is valid, false is not or if the function can't answer
     */
    public function isBlockValid($block) {
        $parent_block = $this->getBlockByHash($block->block_header->prev_block);

        return ($parent_block != null);
    }

    /**
     * Method used to do stuff needed for the network.
     * This method should return "quickly" to prevent blocking of the other networks
     */
    public function run() {
        // Can't run if we have no store
        if ($this->getStore() == null) {
            return;
        }

        $this->getPacketHandler()->run();
    }

    /**
     * @param Store $store
     */
    public function setStore($store) {
        $this->_store = $store;
        $this->_store->initializeStore();
    }

    /**
     * @param boolean $synchronized
     */
    public function setSynchronized($synchronized) {
        $this->_synchronized = $synchronized;
    }
}