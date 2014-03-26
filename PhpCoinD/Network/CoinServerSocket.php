<?php
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