<?php
namespace PhpCoinD\Protocol\Network;


use PHPUnit_Framework_TestCase;

class DogeCoinTest extends PHPUnit_Framework_TestCase {
    /**
     * @var DogeCoin
     */
    protected $network;


    public function setUp() {
        $this->network = new DogeCoin();
    }


    /**
     * Test integrity of the genesis block
     */
    public function testGenesisBlock() {
        $genesis_block = $this->network->createGenesisBlock();
        $this->assertTrue($genesis_block->block_hash->value == $this->network->getGenesisBlockHash());
    }
}