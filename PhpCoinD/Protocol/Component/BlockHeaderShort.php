<?php

namespace PhpCoinD\Protocol\Component;

use PhpCoinD\Protocol\Util\Impl\DSha256ChecksumComputer;

class BlockHeaderShort {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $version;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\Hash")
     * @var Hash
     */
    public $prev_block;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\Hash")
     * @var Hash
     */
    public $merkle_root;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $timestamp;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $bits;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $nonce;


    /**
     * Compute the hash of this block
     * @return Hash
     */
    public function computeBlockHash() {
        // We use a DoubleSHA256 Hasher
        $hasher = new DSha256ChecksumComputer();

        // Convert block header to raw string
        $header_str = pack('V', $this->version)
            . $this->prev_block->value
            . $this->merkle_root->value
            . pack('V', $this->timestamp)
            . pack('V', $this->bits)
            . pack('V', $this->nonce);

        return new Hash($hasher->hash($header_str));
    }
}
