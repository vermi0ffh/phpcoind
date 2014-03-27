<?php
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
        $genesis_block->block_header = new BlockHeaderShort();
        $genesis_block->block_header->version = 1;
        $genesis_block->block_header->prev_block = new Hash(hex2bin('0000000000000000000000000000000000000000000000000000000000000000'));
        $genesis_block->block_header->merkle_root = new Hash(hex2bin('696ad20e2dd4365c7459b4a4a5af743d5e92c6da3229e6532cd605f6533f2a5b'));
        $genesis_block->block_header->timestamp = 1386325540;
        $genesis_block->block_header->bits = 0x1e0ffff0;
        $genesis_block->block_header->nonce = 99943;

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
        $genesis_block->setTx(array($tx));

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