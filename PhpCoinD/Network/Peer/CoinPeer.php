<?php
namespace PhpCoinD\Network\Peer;


use Aza\Components\Socket\SocketStream;
use Exception;
use Monolog\Logger;
use PhpCoinD\Exception\PeerNotReadyException;
use PhpCoinD\Protocol\Component\InvVect;
use PhpCoinD\Protocol\Component\NetworkAddress;
use PhpCoinD\Protocol\Network;
use PhpCoinD\Protocol\Packet;
use PhpCoinD\Protocol\Payload\Addr;
use PhpCoinD\Protocol\Payload\Alert;
use PhpCoinD\Protocol\Payload\Block;
use PhpCoinD\Protocol\Payload\Headers;
use PhpCoinD\Protocol\Payload\Inv;
use PhpCoinD\Protocol\Payload\Version;
use PhpCoinD\Protocol\Payload\Void;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;
use PhpCoinD\Exception\StreamException;
use PhpCoinD\Network\CoinNetworkSocketManager,
    PhpCoinD\Network\ConnectionEndPoint,
    PhpCoinD\Network\Peer;

class CoinPeer implements Peer {
    /**
     * @var CoinNetworkSocketManager
     */
    protected $_coin_network_socket_manager;

    /**
     * @var SocketStream
     */
    protected $_socket;


    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * The packet serializer
     * @var NetworkSerializer
     */
    protected $_serializer;

    /**
     * Receive buffer
     * @var string
     */
    protected $_recv_buffer = '';


    /**
     * Write buffer
     * @var string
     */
    protected $_write_buffer = '';

    /**
     * @var ConnectionEndPoint
     */
    protected $_local_end_point;

    /**
     * @var ConnectionEndPoint
     */
    protected $_remote_end_point;

    /**
     * Flag : did we received a version ?
     *  0 : no
     *  1 : yes
     *  2 : verack send
     * @var int
     */
    protected $_version_received = 0;

    /**
     * Flag : did we sent our version ?
     *  0 : no
     *  1 : yes
     *  2 : verack received
     * @var int
     */
    protected $_version_sent = 0;



    /////////////////////////////////////////////
    // Protected methods
    /**
     * Create an empty packet with the given command
     * @see CoinNetworkSocketManager::createPacket
     * @param string $command
     * @return Packet
     */
    protected function createPacket($command) {
        return $this->getCoinNetworkSocketmanager()->createPacket($command);
    }



    /////////////////////////////////////////////
    // Constructor
    /**
     * @param CoinNetworkSocketManager $coin_network_socket_manager
     * @param SocketStream $socket
     */
    public function __construct($coin_network_socket_manager, $socket) {
        $this->_coin_network_socket_manager = $coin_network_socket_manager;
        $this->_socket = $socket;
        $this->_serializer = new NetworkSerializer();
        $this->_logger = $coin_network_socket_manager->getLogger();

        // Instanciate endpoints
        $this->_local_end_point = new ConnectionEndPoint();
        $this->_remote_end_point = new ConnectionEndPoint();

        // Populate endpoints
        $this->getSocket()->getPeer($this->_remote_end_point->address, $this->_remote_end_point->port);
        $this->getSocket()->getLocal($this->_local_end_point->address, $this->_local_end_point->port);

        // Add peer connection information in log
        $this->getLogger()->addInfo('Connected to a new peer : '.$this->_remote_end_point->address.'('.$this->_remote_end_point->port.')');

        // No buffers on socket (buffer is handler manually)
        $this->getSocket()->setReadBuffer(0);
        $this->getSocket()->setWriteBuffer(0);
    }

    /**
     * Callback called when socket is closed
     */
    public function onClose() {
        // Peer is closed
    }

    /**
     * Get the low level socket
     * @return resource
     */
    public function getSocketResource() {
        return $this->getSocket()->resource;
    }

    /**
     * Get the coin network associated with the peer
     * @return CoinNetworkSocketManager
     */
    public function getCoinNetworkSocketmanager() {
        return $this->_coin_network_socket_manager;
    }

    /**
     * @return \PhpCoinD\Network\ConnectionEndPoint
     */
    public function getLocalEndPoint() {
        return $this->_local_end_point;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * @return \PhpCoinD\Protocol\Util\Impl\NetworkSerializer
     */
    public function getSerializer() {
        return $this->_serializer;
    }

    /**
     * @return \Aza\Components\Socket\SocketStream
     */
    public function getSocket() {
        return $this->_socket;
    }

    /**
     * @return \PhpCoinD\Network\ConnectionEndPoint
     */
    public function getRemoteEndPoint() {
        return $this->_remote_end_point;
    }

    /**
     * Check if the socket has write pending
     * @return bool
     */
    public function hasWritePending() {
        return (strlen($this->_write_buffer) > 0);
    }

    /**
     * Callback when a packet is received
     * @param Packet $packet
     */
    public function onPacket($packet) {
        switch($packet->header->command) {
            case 'addr':
                if ($packet->payload instanceof Addr) {
                    foreach($packet->payload->addr_list as $addr) {
                        $this->getLogger()->addInfo("A new client is available : " . $addr->network_address->getParsedIp() . ':' . $addr->network_address->port);
                    }
                }
                break;

            case 'alert':
                // Alert packet : an important message. We log it !
                if ($packet->payload instanceof Alert) {
                    // Get current protocol version
                    $version = $this->getCoinNetworkSocketmanager()->getNetwork()->getProtocolVersion();

                    // If needed, display message !
                    if ($version >= $packet->payload->getAlertDetail()->min_ver
                        && $version <= $packet->payload->getAlertDetail()->max_ver
                        && $packet->payload->getAlertDetail()->expiration < time()) {
                        // Message is for us ! Let's log it
                        $this->getLogger()->addAlert($packet->payload->getAlertDetail()->status_bar);
                    }
                }
                break;

            case 'block':
                // Alert packet : an important message. We log it !
                if ($packet->payload instanceof Block) {
                    $this->getLogger()->addAlert('Block received ! Merkle Root : ' . bin2hex($packet->payload->block_header->merkle_root->value) );
                    $this->getLogger()->addAlert('Block # transactions : ' . count($packet->payload->tx) );
                }
                break;

            case 'headers':
                if ($packet->payload instanceof Headers) {
                    $this->getLogger()->addInfo("Headers received for " . count($packet->payload->block_header) . ' blocks');
                    foreach($packet->payload->block_header as $block_header) {
                        $this->getLogger()->addInfo("Received header for block : " . bin2hex($block_header->merkle_root->value));
                    }
                }
                break;

            case 'inv':
                if ($packet->payload instanceof Inv) {
                    foreach($packet->payload->inventory as $inv_vect) {
                        switch($inv_vect->type) {
                            case InvVect::OBJECT_ERROR:
                                $type = 'Error';
                                break;
                            case InvVect::OBJECT_MSG_BLOCK:
                                $type = 'Block';
                                break;
                            case InvVect::OBJECT_MSG_TX:
                                $type = 'Tx';
                                break;

                            default:
                                $type = 'Unknown';
                        }
                        $this->getLogger()->addInfo("A new object is present of type : " . $type . '. Hash is ' . bin2hex($inv_vect->hash->value));
                    }
                }
                break;

            case 'version':
                if ($this->_version_received > 0) {
                    $this->getLogger()->addWarning("version packet already received once !");
                }
                // We got the packet
                $this->_version_received = 1;

                // Reply to "version" with a "verack" packet
                $verack_packet = $this->createPacket('verack');
                $verack_packet->payload = new Void();
                $this->writePacket($verack_packet);

                // We just send a verack
                $this->_version_received = 2;

                // Send version back if needed (if version already sent, only verack is sent !)
                if ($this->_version_sent == 0) {
                    $this->sendVersion();
                }
                break;

            case 'verack':
                if ($this->_version_sent == 0) {
                    $this->getLogger()->addWarning("verack received before version packet !");
                }
                // We juste received a verack for our version packet
                $this->_version_sent = 2;
                break;

            default:
                $this->getLogger()->addAlert('Packet unknown : ' . $packet->header->command);
        }
    }

    /**
     * Callback called when socket can be read
     */
    public function onRead() {
        // Read data from the socket
        $data = $this->getSocket()->read();

        // Nothing read ? We have nothing to do then...
        if (is_string($data) && strlen($data) == 0) {
            return;
        }

        // Append data to the buffer
        $this->_recv_buffer .= $data;


        /////////////////////////////////////////
        // Convert the recv buffer into a stream
        $buffered_stream = fopen('php://memory', 'rb+');
        // Write buffer into the stream
        fwrite($buffered_stream, $this->_recv_buffer);
        // Rewind
        fseek($buffered_stream, 0);


        /////////////////////////////////////////
        // Read all packets in the recv buffer
        try {
            while(strlen($this->_recv_buffer) > 0) {
                /**
                 * Read a packet
                 * @var $packet Packet
                 */
                $packet = $this->getSerializer()->read_object($buffered_stream, 'PhpCoinD\Protocol\Packet');

                $this->getLogger()->addNotice('Packet recieved : ' . $packet->header->command);

                // Skip the packet in the buffer
                $this->_recv_buffer = substr($this->_recv_buffer, ftell($buffered_stream));

                // Handle the new packet
                $this->onPacket($packet);
            }
        } catch (StreamException $e) {
            // No more packet !
        } catch (Exception $e) {
            // Something went wrong, add a log
            $this->getLogger()->addWarning($e);
        }

        // Close the temporary stream
        fclose($buffered_stream);
    }

    /**
     * Callback called when socket can write data
     */
    public function onWrite() {
        // Write data from the write buffer, skip data successfully written
        $this->_write_buffer = substr($this->_write_buffer, $this->getSocket()->write($this->_write_buffer));
    }

    /**
     * Send a version packet
     */
    public function sendVersion() {
        try {
            $version_packet = $this->createPacket('version');

            // Create the version payload
            $version_packet->payload = new Version();
            $version_packet->payload->version = $this->getCoinNetworkSocketmanager()->getNetwork()->getProtocolVersion();
            $version_packet->payload->services = 0x1;
            $version_packet->payload->timestamp = time();
            $version_packet->payload->addr_recv = NetworkAddress::fromString($this->getLocalEndPoint()->address, $this->getLocalEndPoint()->port);
            $version_packet->payload->addr_from = NetworkAddress::fromString($this->getRemoteEndPoint()->address, $this->getRemoteEndPoint()->port);
            $version_packet->payload->nonce = $this->getCoinNetworkSocketmanager()->getNetwork()->getNonce();
            $version_packet->payload->user_agent = "CoinPHPd";
            $version_packet->payload->start_height = 1;

            // Write the version packet to the socket
            $this->writePacket($version_packet);
            // Set the flag : version packet has been send
            $this->_version_sent = 1;
        } catch (Exception $e) {
            $this->getLogger()->addAlert($e);
            $this->onClose();
        }
    }


    /**
     * Write a packet to the peer
     * The packet is nos written directly, it is added to the write buffer instead
     * and send when the socket is ready
     * @param Packet $packet
     * @throws \PhpCoinD\Exception\PeerNotReadyException
     */
    public function writePacket($packet) {
        // Until version exchange is done, we can't then anything else than version and verack
        if ( ($this->_version_sent != 2 || $this->_version_received != 2) && !in_array($packet->header->command, array('version', 'verack'))) {
            throw new PeerNotReadyException();
        }

        $this->getLogger()->addNotice('Packet written : ' . $packet->header->command);

        // Create a memory buffer
        $temp_stream = fopen('php://memory', 'r+');

        // Write the packet to the temp buffer
        $this->getSerializer()->write_object($temp_stream, $packet);

        $this->getLogger()->addDebug('Bytes written : ' . ftell($temp_stream));

        // Append temp stream content to the write_buffer
        fseek($temp_stream, 0);

        $this->_write_buffer .= stream_get_contents($temp_stream);

        // Close the temporary stream
        fclose($temp_stream);
    }
}