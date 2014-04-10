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

use Monolog\Logger;
use PhpCoinD\Network\ConnectionEndPoint;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;
use PhpCoinD\Protocol\Payload\Version;

/**
 * A connection with a peer on a coin network
 * @package PhpCoinD\Network
 */
interface Peer {
    /**
     * Return the height of the peer (given in the version message)
     * @return int
     */
    public function getHeight();

    /**
     * @return ConnectionEndPoint
     */
    public function getLocalEndPoint();

    /**
     * @return Logger
     */
    public function getLogger();

    /**
     * Return the peer version message as he sended it
     * @return Version
     */
    public function getPeerVersion();

    /**
     * @return ConnectionEndPoint
     */
    public function getRemoteEndPoint();

    /**
     * Return true if a version packet was recieved
     * @return int
     */
    public function isVersionRecieved();

    /**
     * Return true if a version packet was sent
     * @return bool
     */
    public function isVersionSent();


    /**
     * Set the peer version information
     * @param Version $version
     */
    public function setPeerVersion($version);

    /**
     * Send a version packet to the peer
     */
    public function sendVersionPacket();

    /**
     * Set the flag telling version packet was recived
     */
    public function setVersionRecieved();

    /**
     * Set the flag telling version packet was recived
     */
    public function setVersionSent();

    /**
     * Write a packet to the peer
     * @param Packet $packet
     */
    public function writePacket($packet);
}

