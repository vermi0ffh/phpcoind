<?php
namespace PhpCoinD\Network;
use Aza\Components\Socket\SocketStream;
use Exception;
use Monolog\Logger;
use PhpCoinD\Exception\PeerNotReadyException;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Network\Peer\CoinPeer;
use PhpCoinD\Protocol\Packet;
use PhpCoinD\Protocol\Payload\GetHeaders;


/**
 * A default socket manager for coin networks
 * @package PhpCoinD\Network
 */
class CoinNetworkSocketManager {
    /**
     * @var Network
     */
    protected $_network;

    /**
     * @var \Monolog\Logger
     */
    protected $_logger;

    /**
     * Binds for the server socket
     * @var string[]
     */
    protected $_binds;

    /**
     * All connected peers
     * @var Peer[]
     */
    protected $_peers;

    /**
     * All server sockets
     * @see $_binds
     * @var SocketStream[]
     */
    protected $_server_sockets = array();

    /**
     * Last time the time actions where run
     * @var int
     */
    protected $_last_timed_action = 0;


    /**
     * @param Network $network
     * @param Logger $logger
     * @param array $binds
     */
    public function __construct($network, $logger, $binds = null) {
        $this->_network = $network;
        $this->_logger = $logger;
        $this->setBinds($binds);

        // Prevent the system from launching timed actions too early
        $this->_last_timed_action = time();
    }


    /**
     * Register a new Peer with this network
     * @param Peer $peer
     */
    public function addPeer($peer) {
        $this->_peers[] = $peer;
    }

    /**
     * Bootstrap network connection
     * @param string[] $peer_urls
     */
    public function bootstrap($peer_urls) {
        foreach($peer_urls as $peer_url) {
            try {
                // Connect to the peer
                $new_peer = new CoinPeer($this, SocketStream::client($peer_url));
                $this->addPeer($new_peer);

                // Send version packet first !
                $new_peer->sendVersion();
            } catch(Exception $e) {
                /* Error inhibitor */
            }
        }
    }


    /**
     * Create an empty packet with the given command
     * @param string $command
     * @return Packet
     */
    public function createPacket($command) {
        $packet = new Packet();
        $packet->header->magic = $this->getNetwork()->getMagicValue();
        $packet->header->command = $command;

        return $packet;
    }


    /**
     * Launch time actions
     */
    public function doTimedActions() {
        // Lanch timed actions every 5 seconds max
        if (time() - $this->_last_timed_action < 5) {
            return;
        }

        $this->getLogger()->addDebug("Doing timed actions");

        // Update timestamp
        $this->_last_timed_action = time();

        /////////////////////////////////////////
        // First init
        if ($this->getNetwork()->getHeight() == 0) {
            $this->getLogger()->addInfo("First sync with the network");

            // Get headers from the genesis block
            $getheaders_packet = $this->createPacket('getheaders');
            $getheaders_packet->payload = new GetHeaders();
            $getheaders_packet->payload->version = $this->getNetwork()->getProtocolVersion();
            $getheaders_packet->payload->block_locator_hashes = $this->getNetwork()->getStore()->blockLocator($this->getNetwork()->getGenesisBlockHash());
            $getheaders_packet->payload->hash_stop = new Hash(hex2bin('3a4d13c36ea8b9e4e8518bbd781540efc9d26a95ef8475e82262a439efc34484'));


            // Send the packet
            $this->sendPacket($getheaders_packet);
        }
    }

    /**
     * @return \string[]
     */
    public function getBinds() {
        return $this->_binds;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * @return \PhpCoinD\Protocol\Network
     */
    public function getNetwork() {
        return $this->_network;
    }

    /**
     * @return \PhpCoinD\Network\Peer[]
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
     * Send a packet to one peer (randomly)
     * @param Packet $packet
     * @throws \Exception
     */
    public function sendPacket($packet) {
        $rand = rand(0, count($this->_peers)-1);
        if (!isset($this->_peers[$rand])) {
            throw new PeerNotReadyException();
        }

        /** @var $peer CoinPeer */
        $peer = $this->_peers[$rand];

        // Write packet
        $peer->writePacket($packet);
    }

    /**
     * Change binds of the server sockets.
     * Server sockets are closed and reopened
     *
     * @param \string[] $binds
     */
    public function setBinds($binds = null) {
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
            $this->_server_sockets[] = new CoinServerSocket($this, SocketStream::server($bind_url));
        }
    }

    /**
     * Stop all activity on the coin network
     */
    public function shutdown() {
        /////////////////////////////////////////
        // Close all server sockets
        foreach($this->getServerSockets() as $server_socket) {
            $server_socket->close();
        }
        $this->_server_sockets = array();


        /////////////////////////////////////////
        // Close connections with all peers
        foreach($this->getPeers() as $peer) {
            $peer->getSocket()->close();
        }
    }
} 