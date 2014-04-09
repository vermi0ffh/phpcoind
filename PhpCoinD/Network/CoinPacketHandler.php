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
 * Created 08/04/14 15:03 by Aurélien RICHAUD
 */

namespace PhpCoinD\Network;
use Monolog\Logger;
use PhpCoinD\Network\Socket\Peer;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;


/**
 * How to handle packets for a coin network
 * @package PhpCoinD\Network
 */
interface CoinPacketHandler {
    /**
     * Create an empty packet
     * @param string $command
     * @return Packet
     */
    public function createPacket($command);

    /**
     * @return Logger
     */
    public function getLogger();

    /**
     * Get the network we are currently handling packets for
     * @return Network
     */
    public function getNetwork();

    /**
     * Callback : When a peer connect to ourself
     * @param Peer $peer
     */
    public function onPeerAccept($peer);

    /**
     * Callback : When we connect to a new peer
     * @param Peer $peer
     */
    public function onPeerConnect($peer);

    /**
     * Callback : When a message is received
     * @param Peer $peer The peer that sent us the packet
     * @param Packet $packet The packet
     */
    public function onPacketReceived($peer, $packet);
}