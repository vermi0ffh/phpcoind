<?php

namespace PhpCoinD\Storage\Impl;


use MongoClient;
use MongoDB;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Component\NetworkAddressTimestamp;
use PhpCoinD\Protocol\Component\OutPoint;
use PhpCoinD\Protocol\Component\TxIn;
use PhpCoinD\Protocol\Component\TxOut;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Protocol\Payload\Tx;
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
        // Create the genesis block
        $genesis_block = new Block();
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

        // Add 1 transaction
        $genesis_block->setTx(array($tx));

        // Lock time set to origin
        $tx->lock_time = 0;

        var_dump(bin2hex($genesis_block->block_hash->value));



            /*
             * CBlock(hash=1a91e3dace36e2be3bf030a65679fe821aa1d6ef92e7c9902eb318182c355691,
             *  input=010000000000000000000000000000000000000000000000000000000000000000000000696ad20e2dd4365c7459b4a4a5af743d5e92c6da3229e6532cd605f6533f2a5b24a6a152f0ff0f1e67860100, PoW=0000026f3f7874ca0c251314eaed2d2fcf83d7da3acfaacf59417d485310b448,
             * ver=1,
             * hashPrevBlock=0000000000000000000000000000000000000000000000000000000000000000,
             *  hashMerkleRoot=5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69,
             * nTime=1386325540,
             * nBits=1e0ffff0, nNonce=99943, vtx=1)
        // CTransaction(hash=5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69, ver=1, vin.size=1, vout.size=1, nLockTime=0)
        // CTxIn(COutPoint(0000000000000000000000000000000000000000000000000000000000000000, 4294967295), coinbase 04ffff001d0104084e696e746f6e646f)
        // CTxOut(nValue=88.00000000, scriptPubKey=040184710fa689ad5023690c80f3a4)
        // vMerkleTree: 5b2a3f53f605d62c53e62932dac6925e3d74afa5a4b459745c36d42d0ed26a69
             */
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