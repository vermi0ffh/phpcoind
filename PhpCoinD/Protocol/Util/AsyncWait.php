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

namespace PhpCoinD\Protocol\Util;

use Aza\Components\Socket\SocketStream;

class AsyncWait {
    protected $_sockets;

    public function __construct() {
        $this->_sockets = array(
            'read' => array(),
            'write' => array(),
            'except' => array(),
        );
    }

    /**
     * @param $operation string 'read', 'write' or 'except'
     * @param $socket SocketStream
     * @param $callback callable
     */
    public function checkSocketFor($operation, $socket, $callback) {
        $this->_sockets[$operation][] = array(
            'socket' => $socket,
            'callback' => $callback,
        );
    }

    /**
     * @param int $timeout Timeout in seconds
     * @param int $utimeout Timeout in miliseconds
     */
    public function run($timeout = 0, $utimeout = 0) {
        // Creating a new array containing sockets
        $wait_sockets = array(
            'read' => array(),
            'write' => array(),
            'except' => array(),
        );

        foreach($this->_sockets as $operation => $sockets) {
            /** @var $socket SocketStream */
            foreach($sockets as $socket) {
                $wait_sockets[$operation][] = $socket->resource;
            }
        }

        $nb_socks = SocketStream::select($wait_sockets['read'], $wait_sockets['write'], $wait_sockets['except'], $timeout, $utimeout);

        // Sockets have moved !
        if ($nb_socks > 0) {

        }
    }
}
