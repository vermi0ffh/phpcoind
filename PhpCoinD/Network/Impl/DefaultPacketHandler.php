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


use Exception;
use Monolog\Logger;
use PhpCoinD\Network\CoinPacketHandler;
use PhpCoinD\Network\Socket\Peer;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Component\InvVect;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Protocol\Payload\GetData;
use PhpCoinD\Protocol\Payload\GetHeaders;
use PhpCoinD\Protocol\Payload\Headers;

/**
 * The default packet handler : knows how to handle packets
 * @package PhpCoinD\Network\Impl
 */
class DefaultPacketHandler implements CoinPacketHandler {
    /**
     * @var Network
     */
    protected $_network;

    /**
     * @var bool
     */
    protected $_waiting_headers = false;

    /**
     * A list a block headers to download
     * @var Block[]
     */
    protected $_sync_headers = array();


    /**
     * A list a hash of waited blocks (during blockchain sync)
     * @var string[]
     */
    protected $_waited_block = array();


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
     * @throws \Exception
     */
    public function onPacketReceived($peer, $packet) {
        switch($packet->header->command) {
            case 'block':
                if ($packet->payload instanceof Block) {
                    // If sync is happening, ignore unwaited blocks !
                    if (count($this->_waited_block) > 0 && in_array($packet->payload->block_hash->value, $this->_waited_block)) {
                        // Remove the hash from the wait list
                        unset($this->_waited_block[array_search($packet->payload->block_hash->value, $this->_waited_block)]);

                        // Add the new block
                        $this->getNetwork()->addBlock($packet->payload);
                    }
                }
                break;

            case 'headers':
                if ($packet->payload instanceof Headers && $this->_waiting_headers) {
                    $this->_sync_headers = $packet->payload->block_header;
                }
                break;

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

    /**
     * Method used to do stuff needed for the network.
     * This method should return "quickly" to prevent blocking of the other networks
     */
    public function run() {
        // Run every network connectors sending & receiving packets
        foreach($this->getNetwork()->getNetworkConnectors() as $network_connector) {
            $network_connector->run();
        }

        /////////////////////////////////////////
        // Blockchain sync
        if (!$this->getNetwork()->getSynchronized()) {

            if (!$this->_waiting_headers) {
                $last_block = $this->getNetwork()->getLastBlock();

                $getheaders_packet = $this->createPacket('getheaders');

                if (!($getheaders_packet->payload instanceof GetHeaders)) {
                    throw new Exception("Payload type mismatch");
                }

                $getheaders_packet->payload->version = $this->getNetwork()->getProtocolVersion();
                $getheaders_packet->payload->block_locator_hashes = $this->getNetwork()->getStore()->blockLocator($last_block->block_hash);
                $getheaders_packet->payload->hash_stop = new Hash(hex2bin('0000000000000000000000000000000000000000000000000000000000000000'));
                try {
                    $this->getNetwork()->getNetworkConnectors()[0]->writePacket($getheaders_packet);

                    $this->_waiting_headers = true;
                } catch (Exception $e) {
                    /* Exception inhibitor */
                }
            } else if (count($this->_sync_headers) > 0 && count($this->_waited_block) == 0) {
                /////////////////////////////////////
                // Handle block headers
                $getdata_packet = $this->createPacket('getdata');
                if (!($getdata_packet->payload instanceof GetData)) {
                    throw new Exception("Payload type mismatch");
                }
                $getdata_packet->payload->inventory = array();
                foreach($this->_sync_headers as $header) {
                    // Max 3 per request
                    if (count($getdata_packet->payload->inventory) >= 3) {
                        continue;
                    }

                    // Create a new vector
                    $inv_vect = new InvVect();
                    $inv_vect->hash = $header->block_hash;
                    $inv_vect->type = InvVect::OBJECT_MSG_BLOCK;

                    // Add a new vector to the list
                    $getdata_packet->payload->inventory[] = $inv_vect;

                    // We are awiting for this block
                    $this->_waited_block[] = $header->block_hash->value;
                }

                // Slice the header array
                $this->_sync_headers = array_slice($this->_sync_headers, count($getdata_packet->payload->inventory));

                $this->getNetwork()->getNetworkConnectors()[0]->writePacket($getdata_packet);
            }
        }
    }
}