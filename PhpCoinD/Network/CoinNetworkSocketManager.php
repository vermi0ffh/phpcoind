<?php
namespace PhpCoinD\Network;
use Aza\Components\Socket\SocketStream;
use Exception;
use Monolog\Logger;
use PhpCoinD\Exception\PeerNotReadyException;
use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Component\InvVect;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Network\Peer\CoinPeer;
use PhpCoinD\Protocol\Packet;
use PhpCoinD\Protocol\Payload\GetData;


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
            $getblock_packet = $this->createPacket('getdata');
            $getblock_packet->payload = new GetData();
            $getblock_packet->payload->inventory = array();
            $inv_vect = new InvVect();
            $inv_vect->hash = new Hash($this->getNetwork()->getGenesisBlock());
            $inv_vect->type = InvVect::OBJECT_MSG_BLOCK;
            $getblock_packet->payload->inventory[] = $inv_vect;
/*            $getblock_packet->payload->version = $this->getNetwork()->getProtocolVersion();
            $getblock_packet->payload->block_locator_hashes = array(
                new Hash($this->getNetwork()->getGenesisBlock()),
                //new Hash(hex2bin('98bbc4d809b42375a2627b174be7a8b360f8e172e5c5d1d7c395c30b38db7e33')),
            );
            $getblock_packet->payload->hash_stop = new Hash(hex2bin('00000000000000000000000000000000'));*/

            // Send the packet
            $this->sendPacket($getblock_packet);
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