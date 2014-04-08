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
 * Created 05/04/14 17:35 by Aurélien RICHAUD
 */

namespace PhpCoinD\Protocol;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Storage\Store;


/**
 * Interface Blockchain
 * @package PhpCoinD\Protocol
 */
interface Blockchain {
    /**
     * Add a new block to the blockchain
     * @param Block $block
     * @return mixed
     */
    public function addBlock($block);

    /**
     * Get a block by it's hash
     * @param Hash $hash
     * @return Block|null
     */
    public function getBlockByHash($hash);

    /**
     * Get a block by it's height in the blockchain
     * @param int $height
     * @return Block|null
     */
    public function getBlockByHeight($height);

    /**
     * Get the height of the blockchain
     * @return int
     */
    public function getCurrentHeight();


    /**
     * Return the last block of the blockchain
     * @return Block
     */
    public function getLastBlock();

    /**
     * @return Store
     */
    public function getStore();

    /**
     * Check if a block is valid
     * @param Block $block
     * @return bool true is the block is valid, false is not or if the function can't answer
     */
    public function isBlockValid($block);
}