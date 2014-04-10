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
 * Created 09/04/14 09:45 by Aurélien RICHAUD
 */


namespace PhpCoinD\Network\Impl;


use Monolog\Logger;
use PhpCoinD\Network\CoinPacketHandler;
use PhpCoinD\Network\Socket\Peer;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;

/**
 * The default packet handler : knows how to handle packets
 * @package PhpCoinD\Network\Impl
 */
class DefaultPacketHandler implements CoinPacketHandler {
    /**
     * @var Network
     */
    protected $_network;


    /////////////////////////////////////////////
    // Constructor
    /**
     * @param Network $network
     */
    public function __construct($network) {
        $this->_network = $network;
    }


    /**
     * Create an empty packet
     * @param string $command
     * @return Packet
     */
    public function createPacket($command) {
        $packet = new Packet();
        $packet->header->magic = $this->getNetwork()->getMagicValue();
        $packet->header->command = $command;

        $payload_class = $packet->getPayloadClassName();
        $packet->payload = new $payload_class();

        return $packet;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_network->getLogger();
    }

    /**
     * Callback : When a peer connect to ourself
     * @param Peer $peer
     */
    public function onPeerAccept($peer) {

    }

    /**
     * Callback : When we connect to a new peer
     * @param Peer $peer
     */
    public function onPeerConnect($peer) {
        $peer->sendVersionPacket();
    }

    /**
     * Callback : When a message is received
     * @param Peer $peer The peer that sent us the packet
     * @param Packet $packet The packet
     */
    public function onPacketReceived($peer, $packet) {
        switch($packet->header->command) {
            case 'version':
                if ($peer->isVersionRecieved()) {
                    $this->getLogger()->addWarning("Version packet already received once !");
                }

                // Reply to "version" with a "verack" packet
                $verack_packet = $this->createPacket('verack');
                $peer->writePacket($verack_packet);

                // We got the packet
                $peer->setVersionRecieved();

                // Send version back if needed (if version already sent, only verack is sent !)
                if ($peer) {
                    $peer->sendVersionPacket();
                }

                // Store the peer version payload
                $peer->setPeerVersion($packet->payload);
                break;

            case 'verack':
                // Just ignore it
                break;
        }
    }

    /**
     * Get the network we are currently handling packets for
     * @return Network
     */
    public function getNetwork() {
        return $this->_network;
    }
}