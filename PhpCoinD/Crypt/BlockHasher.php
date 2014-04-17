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
 * Created 12/04/14 12:20 by Aurélien RICHAUD
 */

namespace PhpCoinD\Crypt;


use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Protocol\Util\Impl\DSha256ChecksumComputer;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;
use Pleo\Merkle\FixedSizeTree;

class BlockHasher {
    /**
     * @var NetworkSerializer
     */
    protected $network_serializer;

    /**
     * @var DSha256ChecksumComputer
     */
    protected $checksummer;

    /**
     * The hash function used to check block
     * @var BlockHashFunc
     */
    protected $hash_func;

    /**
     * The MerkleTree root hash lastly computed
     * @var Hash
     */
    protected $root_hash;


    public function __construct() {
        $this->network_serializer = new NetworkSerializer();
        $this->checksummer = new DSha256ChecksumComputer();
    }

    /**
     * @return BlockHashFunc
     */
    public function getHashFunc() {
        return $this->hash_func;
    }

    /**
     * @return \PhpCoinD\Protocol\Component\Hash
     */
    public function getRootHash() {
        return $this->root_hash;
    }

    /**
     * @param mixed $data
     * @return string
     */
    public function computeHash($data) {
        // Pass-through for SHA256
        if (strlen($data) == 32) {
            return $data;
        }

        return $this->getHashFunc()->hasher($data);
    }

    /**
     * Hash a block to check it's validity
     * @param Block $block
     * @return Hash
     */
    public function hashBlock($block) {
        $tree = new FixedSizeTree(count($block->tx) + (count($block->tx) % 2), array($this, 'computeHash'), array($this, 'setRootHash'));
        $last_tx_hash = null;

        // Compute hash of each transaction
        foreach($block->tx as $i => $transaction) {
            $last_tx_hash = $transaction->getHash();

            // Set value of a leaf of the merkle tree
            $tree->set($i, $last_tx_hash->value);
        }

        // Check if we need to repeat the last hash !
        if (count($block->tx) % 2) {
            $tree->set(count($block->tx), $last_tx_hash->value);
        }

        return $this->getRootHash();
    }

    /**
     * @param BlockHashFunc $hash_func
     */
    public function setHashFunc($hash_func) {
        $this->hash_func = $hash_func;
    }

    /**
     * Set the last computed MerkleRoot Hash
     * @param string $hash_raw
     */
    public function setRootHash($hash_raw) {
        $this->root_hash = new Hash($hash_raw);
    }
} 