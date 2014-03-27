<?php
namespace PhpCoinD\Storage\Impl\MongoStore;


use PhpCoinD\Protocol\Network\DogeCoin;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Payload\Block;
use PHPUnit_Framework_TestCase;

class ObjectTransformerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var ObjectTransformer
     */
    protected $object_transformer;

    /**
     * @var Network
     */
    protected $network;

    public function setUp() {
        $this->object_transformer = new ObjectTransformer();
        // Use DogeCoin genesis block for testing
        $this->network = new DogeCoin();
    }

    public function testTransformation() {
        $genesis_block = $this->network->createGenesisBlock();

        $genesis_block_mongoed = $this->object_transformer->fromMongo($this->object_transformer->toMongo($genesis_block));

        // Test class
        $this->assertTrue($genesis_block_mongoed instanceof Block);
        $this->assertTrue(get_class($genesis_block) == get_class($genesis_block_mongoed));

        // Test computed hash
        $this->assertTrue($genesis_block->block_hash == $genesis_block_mongoed->block_hash);
    }
} 