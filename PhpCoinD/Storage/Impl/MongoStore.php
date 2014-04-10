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
    const PEER_COLLECTION = 'peers';

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
     * Add a new block to the store
     * @param Block $bloc
     */
    public function addBlock($bloc) {
        if ($bloc == null) {
            return;
        }

        // Compute block height
        if ($bloc->block_hash->value == $this->getNetwork()->getGenesisBlockHash()->value) {
            $bloc->height = 0;
        } else {
            // Get the parent-block height
            $parent_block = $this->getBlockByHash($bloc->block_header->prev_block);
            if ($parent_block == null) {
                // Orphaned block ?
                $bloc->height = -1;
            } else {
                $bloc->height = $parent_block->height+1;
            }
        }

        // Transform the Block object into a BSON object
        $mongo_block = $this->_object_transformer->toMongo($bloc);
        $mongo_block['_id'] = bin2hex($bloc->block_hash->value);
        $mongo_block['height'] = $bloc->height;

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
    public function addPeer($networkAddressTimestamp) {
        // TODO: Implement addPeer() method.
    }


    /**
     * Compute the block locator for a bloc_id
     * @param Hash $block_id
     * @return Hash[]
     */
    public function blockLocator($block_id) {
        $ret = array();

        $block = $this->getBlockByHash($block_id);
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
     * Get a block by it's hash
     * @param Hash $hash
     * @return Block|null
     */
    public function getBlockByHash($hash) {
        $block = $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->findOne(array(
                '_id' => bin2hex($hash->value),
            ));

        // No block found
        if ($block == null) {
            return null;
        }

        // Get the object back from the mongo bson object
        return $this->_object_transformer->fromMongo($block);
    }

    /**
     * Get a block by it's height in the blockchain
     * @param int $height
     * @return Block|null
     */
    public function getBlockByHeight($height) {
        $block = $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->findOne(array(
                'height' => $height,
            ));

        // No block found
        if ($block == null) {
            return null;
        }

        // Get the object back from the mongo bson object
        return $this->_object_transformer->fromMongo($block);
    }

    /**
     * Get the number of blocks stored
     * @return int
     */
    public function getHeight() {
        $block = $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->find()
            ->sort(array('height' => -1))
            ->limit(1)
            ->getNext();

        // No block found
        if ($block == null) {
            // // Really empty !
            return -1;
        }

        // Return the height of the last block
        return $block['height'];
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
     * @return \PhpCoinD\Protocol\Network
     */
    public function getNetwork() {
        return $this->_network;
    }

    /**
     * Get a random peer against all available peers
     * @return NetworkAddressTimestamp
     */
    public function getRandomPeer() {
        // TODO: Implement getRandomPeer() method.
    }

    /**
     * This method initialize the store. Creatre tables, etc...
     */
    public function initializeStore() {
        // Check if we need to insert the genesis block
        if ($this->getHeight() == -1) {
            $this->addBlock($this->getNetwork()->createGenesisBlock());
        }

        /////////////////////////////////////////
        // Indexes
        // Index timestamp of blocks
        $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->ensureIndex(array('block_header.timestamp' => -1));
        // Index block htight
        $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->ensureIndex(array('height' => -1));
        // Index transactions
        $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->ensureIndex(array('tx' => 1));
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
     * Remove a peer from the store
     * @param NetworkAddressTimestamp $networkAddressTimestamp
     */
    public function removePeer($networkAddressTimestamp) {
        // TODO: Implement removePeer() method.
    }
}