<?php
namespace Vermi0ffh\Coin\Storage\Impl;


use Vermi0ffh\Coin\Component\NetworkAddressTimestamp;
use Vermi0ffh\Coin\Payload\Block;
use Vermi0ffh\Coin\Storage\Store;

class Pdo implements Store {
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
    public function ReadPeers($skip = 0, $size = 10) {
        // TODO: Implement ReadPeers() method.
    }

    /**
     * @param Block $bloc
     */
    public function WriteBlock($bloc) {
        // TODO: Implement WriteBlock() method.
    }

    /**
     * Add a Peer to the database
     * @param NetworkAddressTimestamp $networkAddressTimestamp
     */
    public function WritePeer(NetworkAddressTimestamp $networkAddressTimestamp) {
        // TODO: Implement WritePeer() method.
    }
}