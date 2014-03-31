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

namespace PhpCoinD\Storage\Impl;

use MongoClient;
use MongoCursorException;
use MongoDB;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Component\NetworkAddressTimestamp;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Storage\Impl\MongoStore\ObjectTransformer;
use PhpCoinD\Storage\Store;

class MongoStore implements Store {
    const BLOCK_COLLECTION = 'blocks';

    /**
     * @var MongoClient
     */
    protected $_mongo_client;

    /**
     * @var MongoDB
     */
    protected $_mongo_db;


    /**
     * @var Network
     */
    protected $_network;


    /**
     * @var ObjectTransformer
     */
    protected $_object_transformer;

    /**
     * @param array $config
     * @param $network
     */
    public function __construct($config, $network) {
        // Connect to Mongo
        $this->_mongo_client = new MongoClient($config['url']);
        // Select DB
        $this->_mongo_db = $this->_mongo_client->selectDB($config['db']);
        // Network for this store
        $this->_network = $network;
        $this->_object_transformer = new ObjectTransformer();
    }


    /**
     * @param Block $bloc
     */
    public function addBlock($bloc) {
        if ($bloc == null) {
            return;
        }

        $mongo_block = $this->_object_transformer->toMongo($bloc);
        $mongo_block['_id'] = bin2hex($bloc->block_hash->value);

        try {
            $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
                ->insert($mongo_block);
        } catch (MongoCursorException $e) {
            /* Block is already in the store ! Just skip the insert part */
        }
    }

    /**
     * Add a Peer to the database
     * @param NetworkAddressTimestamp $networkAddressTimestamp
     */
    public function addPeer(NetworkAddressTimestamp $networkAddressTimestamp) {
        // TODO: Implement WritePeer() method.
    }


    /**
     * Compute the block locator for a bloc_id
     * @param Hash $block_id
     * @return Hash[]
     */
    public function blockLocator($block_id) {
        $ret = array();

        $block = $this->readBlock($block_id);
        if ($block != null) {
            $ret[] = $block->block_hash;
        }

        // Add previous block
        if ($block->block_header->prev_block->value != hex2bin('0000000000000000000000000000000000000000000000000000000000000000')) {
            $ret[] = $block->block_header->prev_block;
        }

        // Add genesis block hash at the end
        $ret[] = $this->getNetwork()->getGenesisBlockHash();

        return $ret;
    }


    /**
     * Get the number of blocks stored
     * @return int
     */
    public function countBlocks() {
        return $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->count();
    }


    /**
     * Return the last block received
     * @return Block
     */
    public function getLastBlock() {
        $block = $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->find()
            ->sort(array('block_header.timestamp' => -1))
            ->getNext();

        // No block found
        if ($block == null) {
            return null;
        }

        // Get the object back from the mongo object
        return $this->_object_transformer->fromMongo($block);
    }


    /**
     * @return \MongoDB
     */
    public function getMongoDb() {
        return $this->_mongo_db;
    }


    /**
     * This method initialize the store. Creatre tables, etc...
     */
    public function initializeStore() {
        $genesis_block = $this->readBlock($this->getNetwork()->getGenesisBlockHash());

        // We need to insert the genesis block into the store
        if ($genesis_block == null) {
            $this->addBlock($this->getNetwork()->createGenesisBlock());
        }

        /////////////////////////////////////////
        // Indexes
        // Index timestamp of blocks
        $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->ensureIndex(array('block_header.timestamp' => -1));
        // Index transaction (to find empty blocks quickly)
        $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->ensureIndex(array('tx' => 1));
    }

    /**
     * Read a block from the database
     * @param Hash $block_id
     * @return Block|null
     */
    public function readBlock($block_id) {
        $block = $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->findOne(array(
                '_id' => bin2hex($block_id->value),
            ));

        // No block found
        if ($block == null) {
            return null;
        }

        // Get the object back from the mongo object
        return $this->_object_transformer->fromMongo($block);
    }

    /**
     * Read peers from the database
     * @param int $skip
     * @param int $size
     * @return NetworkAddressTimestamp[]
     */
    public function readPeers($skip = 0, $size = 10) {
        // TODO: Implement ReadPeers() method.
    }


    /**
     * @return \PhpCoinD\Protocol\Network
     */
    public function getNetwork() {
        return $this->_network;
    }
}