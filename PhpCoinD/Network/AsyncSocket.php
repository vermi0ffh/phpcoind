<?php
namespace PhpCoinD\Network;
use Aza\Components\Socket\SocketStream;


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