<?php
namespace PhpCoinD\Network;
use Monolog\Logger;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;


/**
 * A connection with a peer on a coin network
 * @package PhpCoinD\Network
 */
interface Peer extends AsyncSocket {
    /**
     * Get the coin network associated with the peer
     * @return Network
     */
    public function getCoinNetworkSocketmanager();


    /**
     * @return Logger
     */
    public function getLogger();

    /**
     * Callback when a packet is received
     * @param Packet $packet
     */
    public function onPacket($packet);


    /**
     * Return the height of the peer (given in the version message)
     * @return int
     */
    public function getHeight();
} 