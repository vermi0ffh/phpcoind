<?php
namespace Vermi0ffh\Coin\Storage;


use Vermi0ffh\Coin\Component\NetworkAddressTimestamp;
use Vermi0ffh\Coin\Payload\Block;

/**
 * Define a Store used by DogeCoinPhp
 * @package Vermi0ffh\Coin\Storage
 */
interface Store {
    /**
     * This method initialize the store. Creatre tables, etc...
     */
    public function initializeStore();

    /**
     * Read a block from the database
     * @param string $block_id
     * @return Block
     */
    public function readBlock($block_id);


    /**
     * Read peers from the database
     * @param int $skip
     * @param int $size
     * @return NetworkAddressTimestamp[]
     */
    public function ReadPeers($skip = 0, $size = 10);


    /**
     * @param Block $bloc
     */
    public function WriteBlock($bloc);


    /**
     * Add a Peer to the database
     * @param NetworkAddressTimestamp $networkAddressTimestamp
     */
    public function WritePeer(NetworkAddressTimestamp $networkAddressTimestamp);
} 