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
        if (class_exists('MongoDB')) {
            $this->object_transformer = new ObjectTransformer();
            // Use DogeCoin genesis block for testing
            $this->network = new DogeCoin(null, false);
        }
    }

    public function testTransformation() {
        if (class_exists('MongoDB')) {
            $genesis_block = $this->network->createGenesisBlock();

            $genesis_block_mongoed = $this->object_transformer->fromMongo($this->object_transformer->toMongo($genesis_block));

            // Test class
            $this->assertTrue($genesis_block_mongoed instanceof Block);
            $this->assertTrue(get_class($genesis_block) == get_class($genesis_block_mongoed));

            // Test computed hash
            $this->assertTrue($genesis_block->block_hash == $genesis_block_mongoed->block_hash);
        }
    }
} 