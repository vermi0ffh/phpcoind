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

namespace PhpCoinD\Network\Socket;

use Aza\Components\Socket\SocketStream;
use PhpCoinD\Network\CoinPacketHandler;

/**
 * Wrapper for asynchronous sockets
 * @package PhpCoinD\Network
 */
interface AsyncSocket {
    /**
     * Callback called when socket is closed
     */
    public function onClose();

    /**
     * Get the packet handler for this socket
     * @return CoinPacketHandler
     */
    public function getCoinNetworkConnector();

    /**
     * @return SocketStream
     */
    public function getSocket();

    /**
     * Get the low level socket
     * @return resource
     */
    public function getSocketResource();


    /**
     * Check if the socket has write pending
     * @return bool
     */
    public function hasWritePending();


    /**
     * Callback called when socket can be read
     */
    public function onRead();


    /**
     * Callback called when socket can write data
     */
    public function onWrite();
} 