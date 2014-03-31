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
use PhpCoinD\Network\Peer\CoinPeer;

class CoinServerSocket implements AsyncSocket {
    /**
     * @var CoinNetworkSocketManager
     */
    protected $_coin_network;

    /**
     * @var SocketStream
     */
    protected $_socket;

    /**
     * @param CoinNetworkSocketManager $coin_network
     * @param SocketStream $socket
     */
    public function __construct($coin_network, $socket) {
        $this->_coin_network = $coin_network;
        $this->_socket = $socket;
    }

    /**
     * Callback called when socket is closed
     */
    public function onClose() {
        // Nothing
    }

    /**
     * Get the low level socket
     * @return resource
     */
    public function getSocketResource() {
        return $this->getSocket()->resource;
    }

    /**
     * Callback called when socket can be read
     */
    public function onRead() {
        // Add a new peer to the coin network
        $this->getCoinNetwork()->addPeer( new CoinPeer($this->getCoinNetwork(), $this->getSocket()->accept()) );
    }

    /**
     * Callback called when socket can write data
     */
    public function onWrite() {
        // Nothing to write, ever
    }

    /**
     * @return \PhpCoinD\Network\CoinNetworkSocketManager
     */
    public function getCoinNetwork() {
        return $this->_coin_network;
    }

    /**
     * @return \Aza\Components\Socket\SocketStream
     */
    public function getSocket() {
        return $this->_socket;
    }

    /**
     * Check if the socket has write pending
     * @return bool
     */
    public function hasWritePending() {
        // We never write to this socket !
        return false;
    }
}