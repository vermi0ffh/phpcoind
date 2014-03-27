<?php
namespace PhpCoinD\Protocol\Util\Impl;


use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Network\DogeCoin;
use PhpCoinD\Protocol\Payload\Block;
use PHPUnit_Framework_TestCase;

class NetworkSerializerTest extends PHPUnit_Framework_TestCase{
    /**
     * @var NetworkSerializer
     */
    protected $network_transformer;

    /**
     * @var Network
     */
    protected $network;

    public function setUp() {
        $this->network_transformer = new NetworkSerializer();
        // Use DogeCoin genesis block for testing
        $this->network = new DogeCoin();
    }

    public function testTransformation() {
        // Create a genesis block
        $genesis_block = $this->network->createGenesisBlock();

        $stream = fopen('php://memory', 'r+');
        $this->network_transformer->write_object($stream, $genesis_block);
        fseek($stream, 0);
        $genesis_block_networkized = $this->network_transformer->read_object($stream, 'PhpCoinD\Protocol\Payload\Block');
        fclose($stream);

        $this->assertTrue(get_class($genesis_block) == get_class($genesis_block_networkized));
        $this->assertTrue($genesis_block_networkized instanceof Block);
        $this->assertTrue($genesis_block->block_hash == $genesis_block_networkized->block_hash);
    }
} 