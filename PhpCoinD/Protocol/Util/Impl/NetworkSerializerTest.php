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

        // Test class
        $this->assertTrue($genesis_block_networkized instanceof Block);
        $this->assertTrue(get_class($genesis_block) == get_class($genesis_block_networkized));

        // Test computed hash
        $this->assertTrue($genesis_block->block_hash == $genesis_block_networkized->block_hash);
    }
} 