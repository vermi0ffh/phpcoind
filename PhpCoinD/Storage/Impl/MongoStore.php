<?php

namespace PhpCoinD\Storage\Impl;


use MongoClient;
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
        $mongo_block = $this->_object_transformer->toMongo($bloc);
        $mongo_block['_id'] = bin2hex($bloc->block_hash->value);

        $this->getMongoDb()->selectCollection(self::BLOCK_COLLECTION)
            ->insert($mongo_block);
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

        return $ret;
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