<?php

namespace PhpCoinD\Storage\Impl;


use MongoClient;
use MongoDB;
use PhpCoinD\Protocol\Component\NetworkAddressTimestamp;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Storage\Store;

class MongoStore implements Store {
    /**
     * @var MongoClient
     */
    protected $_mongo_client;

    /**
     * @var MongoDB
     */
    protected $_mongo_db;

    /**
     * @param array $config
     */
    public function __construct($config) {
        // Connect to Mongo
        $this->_mongo_client = new MongoClient($config['url']);
        // Select DB
        $this->_mongo_db = $this->_mongo_client->selectDB($config['db']);
    }

    /**
     * This method initialize the store. Creatre tables, etc...
     */
    public function initializeStore() {
        // Nothing to do
    }

    /**
     * Read a block from the database
     * @param string $block_id
     * @return Block
     */
    public function readBlock($block_id) {
        // TODO: Implement readBlock() method.
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
     * @param Block $bloc
     */
    public function WriteBlock($bloc) {
        // TODO: Implement WriteBlock() method.
    }

    /**
     * Add a Peer to the database
     * @param NetworkAddressTimestamp $networkAddressTimestamp
     */
    public function WritePeer(NetworkAddressTimestamp $networkAddressTimestamp) {
        // TODO: Implement WritePeer() method.
    }
}