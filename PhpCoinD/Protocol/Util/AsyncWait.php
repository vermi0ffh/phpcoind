<?php

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
