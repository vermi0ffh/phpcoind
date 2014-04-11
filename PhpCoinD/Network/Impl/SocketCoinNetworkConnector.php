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
 * Created 09/04/14 09:44 by Aurélien RICHAUD
 */

namespace PhpCoinD\Network\Impl;


use Aza\Components\Socket\SocketStream;
use Monolog\Logger;
use PhpCoinD\Network\CoinNetworkConnector;
use PhpCoinD\Network\CoinPacketHandler;
use PhpCoinD\Network\Socket\AsyncSocket;
use PhpCoinD\Network\Socket\Peer;
use PhpCoinD\Network\Socket\Impl\SocketPeer;
use PhpCoinD\Network\Socket\Impl\SocketServer;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;

class SocketCoinNetworkConnector implements CoinNetworkConnector {
    /**
     * Binds for the server socket
     * @var string[]
     */
    protected $_binds;

    /**
     * @var CoinPacketHandler
     */
    protected $_coin_packet_handler;

    /**
     * All connected peers
     * @var Peer[]
     */
    protected $_peers;

    /**
     * All server sockets
     * @see $_binds
     * @var SocketServer[]
     */
    protected $_server_sockets = array();


    /**
     * Connect to network peers
     */
    protected function bootstrap() {
        if (count($this->_peers) == 0) {
            // TODO : Remove this for production
            $defaults_peers = array(
                'tcp://127.0.0.1:22556',
            );

            // Connect to peers
            foreach($defaults_peers as $peer_url) {
                $this->_peers[] = SocketPeer::connect($this, $peer_url);
            }
        }
    }


    /**
     * @param CoinPacketHandler $coin_packet_handler
     * @param string[] $binds
     */
    public function __construct($coin_packet_handler, $binds = null) {
        $this->_coin_packet_handler = $coin_packet_handler;
        $this->setBinds($binds);
        $this->_peers = array();
    }

    public function __destruct() {
        foreach($this->_server_sockets as $socket) {
            $socket->getSocket()->close();
        }

        /** @var $peer SocketPeer */
        foreach($this->_peers as $peer) {
            $peer->getSocket()->close();
        }
    }


    /**
     * @return \string[]
     */
    public function getBinds() {
        return $this->_binds;
    }

    /**
     * Return the PacketHandler
     * @return CoinPacketHandler
     */
    public function getCoinPacketHandler() {
        return $this->_coin_packet_handler;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_coin_packet_handler->getLogger();
    }

    /**
     * Return the network concerned by this connector
     * @return Network
     */
    public function getNetwork() {
        return $this->_coin_packet_handler->getNetwork();
    }

    /**
     * Get all connected peers
     * @return Peer[]
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
     * Method used to do stuff needed for the network.
     * This method should return "quickly" to prevent blocking of the other networks
     */
    public function run() {
        // Connect to peers
        $this->bootstrap();


        /** @var $network_sockets AsyncSocket[] */
        $sockets_objects = array();
        /** @var $all_sockets resource[] */
        $all_sockets = array();
        /** @var $all_sockets resource[] */
        $all_write_sockets = array();

        /** @var $all_sockets AsyncSocket[] */
        $network_sockets = array_merge($this->getServerSockets(), $this->getPeers());

        // Translate sockets structure for stream_select
        /** @var $async_socket AsyncSocket */
        foreach($network_sockets as $async_socket) {
            $sockets_objects[] = $async_socket;
            $all_sockets[] = $async_socket->getSocketResource();

            // We check for write only socket with writes pending ! Else stream_select will go nuts !
            if ($async_socket->hasWritePending()) {
                $all_write_sockets[] = $async_socket->getSocketResource();
            }
        }

        // Duplicates array (because stream_select change arrays)
        $sockets = array(
            'read' => $all_sockets,
            'write' => $all_write_sockets,
            'close' => $all_sockets,
        );

        // Check all sockets for read, write or error (wait .5 seconds max)
        $nb_socks = stream_select($sockets['read'], $sockets['write'], $sockets['close'], 0, 1);

        // Sockets have moved !
        if ($nb_socks > 0) {
            /////////////////////////////////////////
            // Read events
            foreach($sockets['read'] as $socket) {
                $socket_id = array_search($socket, $all_sockets);

                if ($socket_id === false) {
                    $this->getLogger()->addWarning("Can't find a socket after stream_select : " . $socket);
                } else {
                    /** @var $sockets_object AsyncSocket */
                    $sockets_object = $sockets_objects[$socket_id];
                    $sockets_object->onRead();
                }
            }

            /////////////////////////////////////////
            // Write events
            foreach($sockets['write'] as $socket) {
                $socket_id = array_search($socket, $all_sockets);

                if ($socket_id === false) {
                    $this->getLogger()->addWarning("Can't find a socket after stream_select : " . $socket);
                } else {
                    /** @var $sockets_object AsyncSocket */
                    $sockets_object = $sockets_objects[$socket_id];
                    $sockets_object->onWrite();
                }
            }


            /////////////////////////////////////////
            // Close events
            foreach($sockets['close'] as $socket) {
                $socket_id = array_search($socket, $all_sockets);

                if ($socket_id === false) {
                    $this->getLogger()->addWarning("Can't find a socket after stream_select : " . $socket);
                } else {
                    /** @var $sockets_object AsyncSocket */
                    $sockets_object = $sockets_objects[$socket_id];
                    $sockets_object->onClose();
                }
            }
        }
    }

    /**
     * A new peer just connected
     * @param $peer
     */
    public function onPeerAccept($peer) {
        $this->_peers[] = $peer;

        $this->getCoinPacketHandler()->onPeerAccept($peer);
    }

    /**
     * @param string[] $binds
     */
    public function setBinds($binds) {
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
            $this->_server_sockets[] = new SocketServer($this, SocketStream::server($bind_url));
        }
    }

    /**
     * Callback when a peer closed connection
     * @param SocketPeer $peer
     */
    public function onPeerClose($peer) {
        $peer->getSocket()->close();

        $peer_pos = array_search($peer, $this->_peers);

        // Remove the peer from the inner array
        if ($peer_pos !== false) {
            $this->_peers = array_merge(array_slice($this->_peers, 0, $peer_pos), array_slice($this->_peers, $peer_pos+1));
        }
    }

    /**
     * @param Packet $packet
     * @throws \Exception
     */
    public function writePacket($packet) {
        if (count($this->_peers) == 0) {
            throw new \Exception("No peer ready");
        }
        $this->_peers[0]->writePacket($packet);
    }
}