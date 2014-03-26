<?php
namespace PhpCoinD\Protocol\Network;


use PhpCoinD\Protocol\Network;
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
     * The client version advertised
     * @return int
     */
    public function getClientVersion() {
        return 1 * 1000000 + 6 * 10000 + 0 * 100 + 0;
    }

    /**
     * The binary representation of the genesis block
     * @return string
     */
    public function getGenesisBlock() {
        return hex2bin('9156352c1818b32e90c9e792efd6a11a82fe7956a630f03bbee236cedae3911a');
    }

    /**
     * Get the number of blocks currentrly stored
     * @return int
     */
    public function getHeight() {
        return 0;
    }

    /**
     * The magic value for packet header
     * @return int
     */
    public function getMagicValue() {
        return 0xc0c0c0c0;
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