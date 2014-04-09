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

namespace PhpCoinD\Network\Socket\Impl;

use Aza\Components\Socket\SocketStream;
use Exception;
use Monolog\Logger;
use PhpCoinD\Exception\PeerNotReadyException;
use PhpCoinD\Network\CoinPacketHandler;
use PhpCoinD\Network\Socket\Peer;
use PhpCoinD\Protocol\Component\NetworkAddress;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;
use PhpCoinD\Protocol\Payload\Version;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;
use PhpCoinD\Exception\StreamException;
use PhpCoinD\Network\ConnectionEndPoint;

class SocketPeer implements Peer {
    /**
     * 1 Mb recv buffer size
     */
    const MAX_RECV_BUFFER_SIZE = 1048576;

    /**
     * @var CoinPacketHandler
     */
    protected $_coin_packet_handler;

    /**
     * @var ConnectionEndPoint
     */
    protected $_local_end_point;

    /**
     * Receive buffer
     * @var string
     */
    protected $_recv_buffer = '';

    /**
     * @var ConnectionEndPoint
     */
    protected $_remote_end_point;

    /**
     * The packet serializer
     * @var NetworkSerializer
     */
    protected $_serializer;

    /**
     * @var SocketStream
     */
    protected $_socket;

    /**
     * @var bool
     */
    protected $_version_recieved;

    /**
     * @var bool
     */
    protected $_version_sent;

    /**
     * Write buffer
     * @var string
     */
    protected $_write_buffer = '';

    /**
     * The version payload of the remote peer
     * @var Version
     */
    protected $_peer_version;



    /////////////////////////////////////////////
    // Protected methods



    /////////////////////////////////////////////
    // Constructor
    /**
     * @param CoinPacketHandler $coin_packet_handler
     * @param SocketStream $socket
     */
    public function __construct($coin_packet_handler, $socket) {
        $this->_coin_packet_handler = $coin_packet_handler;
        $this->_socket = $socket;
        $this->_serializer = new NetworkSerializer();

        // Instanciate endpoints
        $this->_local_end_point = new ConnectionEndPoint();
        $this->_remote_end_point = new ConnectionEndPoint();

        // No version exchange right now
        $this->_version_recieved = false;
        $this->_version_sent = false;

        // Populate endpoints
        $this->getSocket()->getPeer($this->_remote_end_point->address, $this->_remote_end_point->port);
        $this->getSocket()->getLocal($this->_local_end_point->address, $this->_local_end_point->port);

        // Add peer connection information in log
        $this->getLogger()->addInfo('Connected to a new peer : '.$this->_remote_end_point->address.'('.$this->_remote_end_point->port.')');

        // No buffers on socket (buffer is handler manually)
        $this->getSocket()->setReadBuffer(0);
        $this->getSocket()->setWriteBuffer(0);
    }

    /**
     * Get the packet handler for this socket
     * @return CoinPacketHandler
     */
    public function getCoinNetworkConnector() {
        return $this->_coin_packet_handler;
    }

    /**
     * Return the height of the peer (given in the version message)
     * @return int
     */
    public function getHeight() {
        if ($this->_peer_version != null && $this->_peer_version instanceof Version) {
            return $this->_peer_version->start_height;
        }

        return 0;
    }

    /**
     * Return the peer version message as he sended it
     * @return Version
     */
    public function getPeerVersion() {
        return $this->_peer_version;
    }

    /**
     * Get the low level socket
     * @return resource
     */
    public function getSocketResource() {
        return $this->getSocket()->resource;
    }

    /**
     * @return \PhpCoinD\Network\ConnectionEndPoint
     */
    public function getLocalEndPoint() {
        return $this->_local_end_point;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->getCoinNetworkConnector()->getLogger();
    }

    /**
     * @return \PhpCoinD\Protocol\Util\Impl\NetworkSerializer
     */
    public function getSerializer() {
        return $this->_serializer;
    }

    /**
     * @return \Aza\Components\Socket\SocketStream
     */
    public function getSocket() {
        return $this->_socket;
    }

    /**
     * @return \PhpCoinD\Network\ConnectionEndPoint
     */
    public function getRemoteEndPoint() {
        return $this->_remote_end_point;
    }

    /**
     * Return true if a version packet was recieved
     * @return int
     */
    public function isVersionRecieved() {
        return $this->_version_recieved;
    }

    /**
     * Return true if a version packet was sent
     * @return bool
     */
    public function isVersionSent() {
        return $this->_version_sent;
    }

    /**
     * Check if the socket has write pending
     * @return bool
     */
    public function hasWritePending() {
        return (strlen($this->_write_buffer) > 0);
    }

    /**
     * Send a version packet to the peer
     */
    public function sendVersionPacket() {
        $network = $this->getCoinNetworkConnector()->getNetwork();

        $version_packet = $this->getCoinNetworkConnector()->createPacket('version');
        $version_packet->payload->version = $network->getProtocolVersion();
        $version_packet->payload->services = 0x1;
        $version_packet->payload->timestamp = time();
        $version_packet->payload->addr_recv = NetworkAddress::fromString($this->getLocalEndPoint()->address, $this->getLocalEndPoint()->port);
        $version_packet->payload->addr_from = NetworkAddress::fromString($this->getRemoteEndPoint()->address, $this->getRemoteEndPoint()->port);
        $version_packet->payload->nonce = $network->getNonce();
        $version_packet->payload->user_agent = "CoinPHPd";
        $version_packet->payload->start_height = $network->getCurrentHeight();

        // Write the version packet
        $this->writePacket($version_packet);

        // Set the flag telling we sent the version packet
        $this->setVersionSent();
    }

    /**
     * Set the peer version information
     * @param Version $version
     */
    public function setPeerVersion($version) {
        $this->_peer_version = $version;
    }

    /**
     * Set the flag telling version packet was recived
     */
    public function setVersionRecieved() {
        $this->_version_recieved = true;
    }

    /**
     * Set the flag telling version packet was recived
     */
    public function setVersionSent() {
        $this->_version_sent = true;
    }

    /**
     * Callback called when socket is closed
     */
    public function onClose() {
        // Peer is closed
    }

    /**
     * Callback called when socket can be read
     */
    public function onRead() {
        // Read data from the socket
        $data = $this->getSocket()->read(self::MAX_RECV_BUFFER_SIZE - strlen($this->_recv_buffer));

        // Nothing read ? We have nothing to do then...
        if (is_string($data) && strlen($data) == 0) {
            return;
        }

        // Append data to the buffer
        $this->_recv_buffer .= $data;


        /////////////////////////////////////////
        // Convert the recv buffer into a stream
        $buffered_stream = fopen('php://memory', 'rb+');
        // Write buffer into the stream
        fwrite($buffered_stream, $this->_recv_buffer);
        // Rewind
        fseek($buffered_stream, 0);


        /////////////////////////////////////////
        // Read all packets in the recv buffer
        try {
            while(strlen($this->_recv_buffer) > 0) {
                /**
                 * Read a packet
                 * @var $packet Packet
                 */
                $packet = $this->getSerializer()->read_object($buffered_stream, 'PhpCoinD\Protocol\Packet');

                $this->getLogger()->addNotice('Packet recieved : ' . $packet->header->command);

                // Skip the packet in the buffer
                $this->_recv_buffer = substr($this->_recv_buffer, ftell($buffered_stream));

                // Handle the new packet
                $this->getCoinNetworkConnector()->onPacketReceived($this, $packet);
            }
        } catch (StreamException $e) {
            // No more packet !
        } catch (Exception $e) {
            // Something went wrong, add a log
            $this->getLogger()->addWarning($e);
        }

        // Close the temporary stream
        fclose($buffered_stream);
    }

    /**
     * Callback called when socket can write data
     */
    public function onWrite() {
        // Write data from the write buffer, skip data successfully written
        $this->_write_buffer = substr($this->_write_buffer, $this->getSocket()->write($this->_write_buffer));
    }

    /**
     * Write a packet to the peer
     * The packet is nos written directly, it is added to the write buffer instead
     * and send when the socket is ready
     * @param Packet $packet
     * @throws \PhpCoinD\Exception\PeerNotReadyException
     */
    public function writePacket($packet) {
        // Until version exchange is done, we can't then anything else than version and verack
        if ( (!$this->isVersionRecieved() || !$this->isVersionSent()) && !in_array($packet->header->command, array('version', 'verack'))) {
            throw new PeerNotReadyException();
        }

        $this->getLogger()->addNotice('Packet written : ' . $packet->header->command);

        // Create a memory buffer
        $temp_stream = fopen('php://memory', 'r+');

        // Write the packet to the temp buffer
        $this->getSerializer()->write_object($temp_stream, $packet);

        $this->getLogger()->addDebug('Bytes written : ' . ftell($temp_stream));

        // Append temp stream content to the write_buffer
        fseek($temp_stream, 0);

        $this->_write_buffer .= stream_get_contents($temp_stream);

        // Close the temporary stream
        fclose($temp_stream);
    }
}