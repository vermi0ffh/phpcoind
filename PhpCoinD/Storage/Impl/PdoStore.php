<?php
namespace PhpCoinD\Storage\Impl;


use PhpCoinD\Protocol\Component\NetworkAddressTimestamp;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Storage\Store;

class PdoStore implements Store {
    /**
     * This method initialize the store. Creatre tables, etc...
     */
    public function initializeStore() {
        // TODO: Implement initializeStore() method.
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
    public function addBlock($bloc) {
        // TODO: Implement WriteBlock() method.
    }

    /**
     * Add a Peer to the database
     * @param NetworkAddressTimestamp $networkAddressTimestamp
     */
    public function addPeer(NetworkAddressTimestamp $networkAddressTimestamp) {
        // TODO: Implement WritePeer() method.
    }
}