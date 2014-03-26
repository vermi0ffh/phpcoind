<?php
namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Packet\Payload;
use PhpCoinD\Protocol\Component\BlockHeaderShort;
use PhpCoinD\Protocol\Util\Impl\DSha256ChecksumComputer;

class Block implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\BlockHeaderShort")
     * @var BlockHeaderShort
     */
    public $block_header;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Payload\Tx")
     * @var Tx[]
     */
    public $tx;

    /**
     * @var Hash
     */
    public $block_hash;

    /**
     * This method is a trick to compute the block hash
     * @param \PhpCoinD\Protocol\Payload\Tx[] $tx
     */
    public function setTx($tx) {
        $hasher = new DSha256ChecksumComputer();

        // TODO : Use the right hashing algorithm
        $this->block_hash = new Hash($hasher->hash_sha256($hasher->hash_sha256(pack('V', $this->block_header->version))));

        $this->tx = $tx;
    }
} 