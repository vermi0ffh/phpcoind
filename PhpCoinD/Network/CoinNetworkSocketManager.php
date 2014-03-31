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
 * Created 31/03/14 16:05 by Aurélien RICHAUD
 */

namespace PhpCoinD\Network;

use Aza\Components\Socket\SocketStream;
use Exception;
use Monolog\Logger;
use PhpCoinD\Exception\PeerNotReadyException;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Component\InvVect;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Network\Peer\CoinPeer;
use PhpCoinD\Protocol\Packet;
use PhpCoinD\Protocol\Payload\Addr,
    PhpCoinD\Protocol\Payload\Alert,
    PhpCoinD\Protocol\Payload\Block,
    PhpCoinD\Protocol\Payload\Headers,
    PhpCoinD\Protocol\Payload\Inv,
    PhpCoinD\Protocol\Payload\NotFound;
use PhpCoinD\Protocol\Payload\GetBlocks;
use PhpCoinD\Protocol\Payload\GetData;
use PhpCoinD\Protocol\Payload\GetHeaders;

/**
 * A default socket manager for coin networks
 * @package PhpCoinD\Network
 */
class CoinNetworkSocketManager {
    /**
     * @var Network
     */
    protected $_network;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * Binds for the server socket
     * @var string[]
     */
    protected $_binds;

    /**
     * All connected peers
     * @var Peer[]
     */
    protected $_peers;

    /**
     * All server sockets
     * @see $_binds
     * @var SocketStream[]
     */
    protected $_server_sockets = array();

    /**
     * Last time the time actions where run
     * @var int
     */
    protected $_last_timed_action = 0;


    /**
     * A flag telling if the blockchain is syncing
     * @var bool
     */
    protected $_is_syncing = false;


    /**
     * A list of Hashes of Blocks we need to download
     * @var Hash[]
     */
    protected $_waiting_block_list;

    /**
     * The hash of the block being loaded from the network
     * @var Hash[]
     */
    protected $_loading_blocks;


    /**
     * @param Network $network
     * @param Logger $logger
     * @param array $binds
     */
    public function __construct($network, $logger, $binds = null) {
        $this->_network = $network;
        $this->_logger = $logger;
        $this->setBinds($binds);

        // Prevent the system from launching timed actions too early
        $this->_last_timed_action = time();
        // Empty list
        $this->_waiting_block_list = array();
        $this->_loading_blocks = array();
    }


    /**
     * Register a new Peer with this network
     * @param Peer $peer
     */
    public function addPeer($peer) {
        $this->_peers[] = $peer;
    }

    /**
     * Bootstrap network connection
     * @param string[] $peer_urls
     */
    public function bootstrap($peer_urls) {
        foreach($peer_urls as $peer_url) {
            try {
                // Connect to the peer
                $new_peer = new CoinPeer($this, SocketStream::client($peer_url));
                $this->addPeer($new_peer);

                // Send version packet first !
                $new_peer->sendVersion();
            } catch(Exception $e) {
                /* Error inhibitor */
            }
        }
    }


    /**
     * Create an empty packet with the given command
     * @param string $command
     * @return Packet
     */
    public function createPacket($command) {
        $packet = new Packet();
        $packet->header->magic = $this->getNetwork()->getMagicValue();
        $packet->header->command = $command;

        // Init payhload
        $payload_class = $packet->getPayloadClassName();
        $packet->payload = new $payload_class;

        return $packet;
    }


    /**
     * Launch time actions
     */
    public function doTimedActions() {
        // Lanch timed actions every seconds max
        if (time() - $this->_last_timed_action < 1) {
            return;
        }

        $this->getLogger()->addDebug("Doing timed actions");

        // Update timestamp
        $this->_last_timed_action = time();

        /////////////////////////////////////////
        // Blockchain synchronization
        $current_peers_max_height = 0;
        foreach($this->getPeers() as $peer) {
            $current_peers_max_height = max($current_peers_max_height, $peer->getHeight());
        }

        if ($this->getNetwork()->getHeight() < $current_peers_max_height && !$this->_is_syncing) {
            $this->getLogger()->addInfo("Sync with the network");
            $this->_is_syncing = true;

            /////////////////////////////////////
            // Display last received block
            $last_block = $this->getNetwork()->getStore()->getLastBlock();
            $this->getLogger()->addDebug('Last block ('.bin2hex($last_block->block_hash->value).'). timestamp = ' . date('Y-m-d H:i:s', $last_block->block_header->timestamp));

            /////////////////////////////////////
            // Sync using checkpoints
            $hash_check_point = $this->getNetwork()->getNextCheckPoint();

            if ($hash_check_point != null) {
                // Get headers from the genesis block
                $getheaders_packet = $this->createPacket('getheaders');
                if ($getheaders_packet->payload instanceof GetHeaders) {
                    $getheaders_packet->payload->version = $this->getNetwork()->getProtocolVersion();
                    $getheaders_packet->payload->block_locator_hashes = $this->getNetwork()->getStore()->blockLocator($this->getNetwork()->getLastBlockHash());
                    $getheaders_packet->payload->hash_stop = $hash_check_point;

                    // Send the packet
                    $this->sendPacket($getheaders_packet);
                }
            } else {
                // Sync using a slower but safer way after checkpoints

                // Get headers from the genesis block
                $getblocks_packet = $this->createPacket('getblocks');

                if ($getblocks_packet->payload instanceof GetBlocks) {
                    $getblocks_packet->payload->version = $this->getNetwork()->getProtocolVersion();
                    $getblocks_packet->payload->block_locator_hashes = $this->getNetwork()->getStore()->blockLocator($this->getNetwork()->getLastBlockHash());
                    $getblocks_packet->payload->hash_stop = new Hash(hex2bin('0000000000000000000000000000000000000000000000000000000000000000'));

                    // Send the packet
                    $this->sendPacket($getblocks_packet);
                }
            }
        }

        /////////////////////////////////////////
        // Download waiting blocks
        if (count($this->_loading_blocks) == 0 && count($this->_waiting_block_list) > 0) {
            $getdata_packet = $this->createPacket('getdata');
            if ($getdata_packet->payload instanceof GetData) {

                for($i=0; $i<count($this->_waiting_block_list) && $i < 10; $i++) {
                    // Prepare an inv_vect to load the block from the network
                    $inv_vect = new InvVect();
                    $inv_vect->type = InvVect::OBJECT_MSG_BLOCK;
                    $inv_vect->hash = $this->_waiting_block_list[0];

                    // We need this block
                    $getdata_packet->payload->inventory[] = $inv_vect;

                    $this->sendPacket($getdata_packet);

                    // Add hash to currently loading blocks
                    $this->_loading_blocks[] = $this->_waiting_block_list[0];

                    // Remove the block from the waiting queue
                    $this->_waiting_block_list = array_slice($this->_waiting_block_list, 1);
                }
            }
        }


        /////////////////////////////////////////
        // Connect more peers
        // TODO : Add code here
    }

    /**
     * @return \string[]
     */
    public function getBinds() {
        return $this->_binds;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * @return \PhpCoinD\Protocol\Network
     */
    public function getNetwork() {
        return $this->_network;
    }

    /**
     * @return \PhpCoinD\Network\Peer[]
     */
    public function getPeers() {
        return $this->_peers;
    }

    /**
     * @return \Aza\Components\Socket\SocketStream[]
     */
    public function getServerSockets() {
        return $this->_server_sockets;
    }

    /**
     * @return boolean
     */
    public function isSyncing() {
        return $this->_is_syncing;
    }

    /**
     * Callback when a packet is received (after peer level processing !)
     * @param Packet $packet
     */
    public function onPacket($packet) {
        switch($packet->header->command) {
            case 'addr':
                if ($packet->payload instanceof Addr) {
                    foreach($packet->payload->addr_list as $addr) {
                        $this->getLogger()->addInfo("A new client is available : " . $addr->network_address->getParsedIp() . ':' . $addr->network_address->port);
                    }
                }
                break;

            case 'alert':
                // Alert packet : an important message. We log it !
                if ($packet->payload instanceof Alert) {
                    // Get current protocol version
                    $version = $this->getNetwork()->getProtocolVersion();

                    // If needed, display message !
                    if ($version >= $packet->payload->getAlertDetail()->min_ver
                        && $version <= $packet->payload->getAlertDetail()->max_ver
                        && $packet->payload->getAlertDetail()->expiration < time()) {
                        // Message is for us ! Let's log it
                        $this->getLogger()->addAlert($packet->payload->getAlertDetail()->status_bar);
                    }
                }
                break;

            case 'block':
                // Alert packet : an important message. We log it !
                if ($packet->payload instanceof Block) {
                    for($i=0; $i<count($this->_loading_blocks); $i++) {
                        if ($this->_loading_blocks[$i]->value == $packet->payload->block_hash->value) {
                            // We are expecting this block
                            unset($this->_loading_blocks[$i]);
                            // Add the block to the block chain
                            $this->getNetwork()->getStore()->addBlock($packet->payload);

                            $this->getLogger()->addAlert('Block received ! Hash : ' . bin2hex($packet->payload->block_hash->value) );
                            $this->getLogger()->addAlert('Block # transactions : ' . count($packet->payload->tx) );
                        }
                    }

                    // Pack the array
                    $this->_loading_blocks = array_values($this->_loading_blocks);
                }
                break;

            case 'headers':
                if ($packet->payload instanceof Headers && $this->isSyncing()) {
                    // A new list of headers have arrived
                    $this->getLogger()->addInfo("Headers received for " . count($packet->payload->block_header) . ' blocks');

                    // For each block, compute the block hash and ask for full block data
                    foreach($packet->payload->block_header as $block_header) {
                        //$this->getLogger()->addInfo("Block hash : " . bin2hex($block_header->block_hash->value));
                        // Add the new block to the chain
                        $this->getNetwork()->getStore()->addBlock($block_header);
                    }

                    // Send the getdata packet
                    $this->_is_syncing = false;
                }
                break;

            case 'inv':
                if ($packet->payload instanceof Inv) {
                    foreach($packet->payload->inventory as $inv_vect) {
                        /////////////////
                        // Set a getdata message to retrive the block
                        switch($inv_vect->type) {
                                case InvVect::OBJECT_ERROR:
                                    $type = 'Error';
                                    break;
                                case InvVect::OBJECT_MSG_BLOCK:
                                    $type = 'Block';
                                    $this->_waiting_block_list[] = $inv_vect->hash;
                                    break;
                                case InvVect::OBJECT_MSG_TX:
                                    $type = 'Tx';
                                    break;

                                default:
                                    $type = 'Unknown';
                            }

                            $this->getLogger()->addInfo("A new object is present of type : " . $type . '. Hash is ' . bin2hex($inv_vect->hash->value));
                    }
                }
                break;

            case 'notfound':
                if ($packet->payload instanceof NotFound) {
                    foreach($packet->payload->inventory as $vect) {
                        $type = $vect->type == InvVect::OBJECT_MSG_BLOCK ? 'Block' : 'Tx';
                        $this->getLogger()->addInfo($type . ' not found ! Hash : ' . bin2hex($vect->hash->value) );
                    }
                }
                break;

            default:
                $this->getLogger()->addAlert('Packet unknown : ' . $packet->header->command);
        }
    }

    /**
     * Send a packet to one peer (randomly)
     * @param Packet $packet
     * @throws \Exception
     */
    public function sendPacket($packet) {
        $rand = rand(0, count($this->_peers)-1);
        if (!isset($this->_peers[$rand])) {
            throw new PeerNotReadyException();
        }

        /** @var $peer CoinPeer */
        $peer = $this->_peers[$rand];

        // Write packet
        $peer->writePacket($packet);
    }

    /**
     * Change binds of the server sockets.
     * Server sockets are closed and reopened
     *
     * @param \string[] $binds
     */
    public function setBinds($binds = null) {
        // Default binds
        if ($binds === null) {
            $binds = array(
                'tcp://0.0.0.0:2500',
                'tcp://[::1]:2500',
            );
        }
        $this->_binds = $binds;

        /////////////////////////////////////////
        // Close all server sockets
        foreach($this->getServerSockets() as $server_socket) {
            $server_socket->close();
        }
        $this->_server_sockets = array();

        /////////////////////////////////////////
        // Bind new sockets
        foreach($this->getBinds() as $bind_url) {
            $this->_server_sockets[] = new CoinServerSocket($this, SocketStream::server($bind_url));
        }
    }

    /**
     * Stop all activity on the coin network
     */
    public function shutdown() {
        /////////////////////////////////////////
        // Close all server sockets
        foreach($this->getServerSockets() as $server_socket) {
            $server_socket->close();
        }
        $this->_server_sockets = array();


        /////////////////////////////////////////
        // Close connections with all peers
        foreach($this->getPeers() as $peer) {
            $peer->getSocket()->close();
        }
    }
}