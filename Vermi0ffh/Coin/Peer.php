<?php

namespace Vermi0ffh\Coin;

use Aza\Components\Socket\SocketStream;
use Exception;
use Monolog\Logger;
use Vermi0ffh\Coin\Component\Hash;
use Vermi0ffh\Coin\Component\InvVect;
use Vermi0ffh\Coin\Component\NetworkAddress;
use Vermi0ffh\Coin\Network\Packet;
use Vermi0ffh\Coin\Payload\Addr;
use Vermi0ffh\Coin\Payload\Alert;
use Vermi0ffh\Coin\Payload\Block;
use Vermi0ffh\Coin\Payload\GetBlocks;
use Vermi0ffh\Coin\Payload\GetData;
use Vermi0ffh\Coin\Payload\GetHeaders;
use Vermi0ffh\Coin\Payload\Headers;
use Vermi0ffh\Coin\Payload\Inv;
use Vermi0ffh\Coin\Payload\Version;
use Vermi0ffh\Coin\Payload\Void;
use Vermi0ffh\Coin\Storage\Impl\PdoStore;
use Vermi0ffh\Coin\Util\Impl\NetworkSerializer;
use Vermi0ffh\Exception\StreamException;

class Peer {
    protected $_pid;
    protected $_socket;
    protected $_ipc_socket;
    protected $_logger;
    protected $_local_addr;
    protected $_local_port;
    protected $_remote_addr;
    protected $_remote_port;

    protected $_read_buffer;
    protected $_write_packets = array();

    protected $_store;

    protected function createPacket($command) {
        $packet = new Packet();
        $packet->header->magic = $GLOBALS['magic_values'][ $GLOBALS['coind']['coin_network'] ];
        $packet->header->command = $command;

        return $packet;
    }

    /**
     * Create a version packet
     * @return Packet
     */
    protected function getVersionPacket() {
        $version_packet = $this->createPacket('version');

        // Create the version payload
        $version_packet->payload = new Version();
        $version_packet->payload->version = 70002;
        $version_packet->payload->services = 0x1;
        $version_packet->payload->timestamp = time();
        $version_packet->payload->addr_recv = NetworkAddress::fromString($this->getLocalAddr(), $this->getLocalPort());
        $version_packet->payload->addr_from = NetworkAddress::fromString($this->getRemoteAddr(), $this->getRemotePort());
        $version_packet->payload->nonce = $GLOBALS['nonce'];
        $version_packet->payload->user_agent = "CoinPHPd";
        $version_packet->payload->start_height = 0;

        // Return the new Version packet
        return $version_packet;
    }


    /**
     * Packet handler : Do actions with received packets
     * @param Packet $packet
     */
    protected function packetCallBack(Packet $packet) {

        switch( $packet->header->command ) {
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
                    $this->getLogger()->addAlert($packet->payload->getAlertDetail()->status_bar);
                }
                break;

            case 'block':
                // Alert packet : an important message. We log it !
                if ($packet->payload instanceof Block) {
                    $this->getLogger()->addAlert('Block received : ' . bin2hex($packet->payload->block_hash) );
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
                // Reply to "version" with a "verack" packet
                $verack_packet = $this->createPacket('verack');
                $verack_packet->payload = new Void();
                $this->writePacket($verack_packet);
                break;

            case 'verack':
                /*$getaddr_packet = $this->createPacket('getaddr');

                // Create the version payload
                $getaddr_packet->payload = new Void();
                $this->writePacket($getaddr_packet);*/

                /////////////////////////////////
                // Check if we have genesis block
                $genesis_block = $this->getStore()->readBlock($GLOBALS['genesis_block'][ $GLOBALS['coind']['coin_network'] ]);
                if ($genesis_block == null) {
                    // We need to download the genesis block !
                    $getblock_packet = $this->createPacket('getheaders');
                    $getblock_packet->payload = new GetHeaders();
                    $getblock_packet->payload->version = $GLOBALS['protocol_version'][ $GLOBALS['coind']['coin_network' ] ];
                    $getblock_packet->payload->block_locator_hashes = array();
                    $hash = new Hash();
                    $hash->value = $GLOBALS['genesis_block'][ $GLOBALS['coind']['coin_network'] ];
                    $getblock_packet->payload->block_locator_hashes[] = $hash;
                    $hashnull = new Hash();
                    $hashnull->value = hex2bin('00000000000000000000000000000000');
                    $getblock_packet->payload->hash_stop = $hashnull;
                    $this->writePacket($getblock_packet);
                }

                break;

            default:
                $this->getLogger()->addAlert('Packet unknown : ' . $packet->header->command);
        }
    }

    /**
     * Peer constructor
     * @param $socket SocketStream
     * @param Logger $logger
     */
    public function __construct($socket, $logger=null) {
        $this->_socket = $socket;
        if ($logger == null) {
            $logger = $GLOBALS['logger'];
        }
        $this->_logger = $logger;
        $this->_store = new PdoStore();
    }


    public function run() {
        /////////////////////////////////////////
        // Fork the peer
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new Exception("Whould not fork");
        } else if ($pid != 0) {
            $this->_pid = $pid;
            // Close the socket in the parent process, only child process have access
            $this->getSocket()->close();
            // Parent process
            return;
        }

        $this->getSocket()->getPeer($this->_remote_addr, $this->_remote_port);
        $this->getSocket()->getLocal($this->_local_addr, $this->_local_port);

        $this->getLogger()->addInfo('Connected to a new peer : '.$this->getRemoteAddr().'('.$this->getRemotePort().')');
        $this->getSocket()->setReadBuffer(0);
        $this->getSocket()->setWriteBuffer(0);

        // Write the Version Packet
        $this->writePacket($this->getVersionPacket());

        /////////////////////////////////////////
        // Child process
        while(!$GLOBALS['shutdown']) {
            try {
                $read = array($this->getSocket()->resource);
                $write = (count($this->_write_packets) > 0 ? $read : array());
                $except = $read;

                // Check if we are ready to read
                if ($this->getSocket()->select($read, $write, $except, 1, 0) > 0) {
                    // Disconnected !
                    if (count($except) > 0) {
                        $GLOBALS['shutdown'] = true;
                    }

                    // Write waiting packets
                    if (count($write) > 0) {
                        /** @var $packet Packet */
                        $serializer = new NetworkSerializer();

                        foreach($this->_write_packets as $packet) {
                            $this->getLogger()->addInfo('Packet sent : ' . $packet->header->command);
                            $serializer->write_object($this->getSocket()->resource, $packet);
                        }
                        $this->_write_packets = array();
                    }

                    // We have a packet waiting
                    if (count($read) > 0) {
                        // Read data
                        $data = $this->getSocket()->read();
                        if (is_string($data) && strlen($data) == 0) {
                            // Shutdown the system
                            $GLOBALS['shutdown'] = true;
                            continue;
                        }

                        // Append data to the buffer
                        $this->_read_buffer .= $data;

                        // Try to read a packet
                        $buffered_stream = fopen('php://memory', 'rb+');
                        // Write buffer into the stream
                        fwrite($buffered_stream, $this->_read_buffer);
                        // Rewind
                        fseek($buffered_stream, 0);

                        // Read all packets
                        try {
                            while(strlen($this->_read_buffer) > 0) {
                                $serializer = new NetworkSerializer();

                                /** @var $packet Packet */
                                $packet = $serializer->read_object($buffered_stream, 'Vermi0ffh\Coin\Network\Packet');

                                $this->getLogger()->addInfo('Packet arrived : ' . $packet->header->command);

                                // Skip the packet in the buffer
                                $this->_read_buffer = substr($this->_read_buffer, ftell($buffered_stream));

                                $this->packetCallBack($packet);
                            }
                        } catch (StreamException $e) {
                            /* Can't read from the stream ? We are waiting more data ! */
                            continue;
                        } catch (Exception $e) {
                            $this->getLogger()->addWarning($e);
                        }

                        // Close the temporary stream
                        fclose($buffered_stream);
                    }
                }
            } catch (Exception $e) {
                $this->getLogger()->addWarning($e);

                if ($e instanceof StreamException) {
                    $GLOBALS['shutdown'] = true;
                }
            }
        }

        $this->getLogger()->addInfo('Client connection closed for ' . $this->getRemoteAddr().'('.$this->getRemotePort().')');

        exit();
    }

    /**
     * @return \Aza\Components\Socket\SocketStream
     */
    public function getSocket() {
        return $this->_socket;
    }


    /**
     * Connect to a new client
     * @param string $url An url like : tcp://10.10.10.10:2500
     * @return Peer
     */
    public static function connect($url) {
        $ret = new self(SocketStream::client($url));

        return $ret;
    }

    /**
     * Get the process id for this peer
     * @return int
     */
    public function getPid() {
        return $this->_pid;
    }

    /**
     * @return Logger
     */
    public function getLogger() {
        return $this->_logger;
    }

    /**
     * @return string
     */
    public function getRemoteAddr() {
        return $this->_remote_addr;
    }

    /**
     * @return int
     */
    public function getRemotePort() {
        return $this->_remote_port;
    }

    /**
     * @return string
     */
    public function getLocalAddr() {
        return $this->_local_addr;
    }

    /**
     * @return int
     */
    public function getLocalPort() {
        return $this->_local_port;
    }

    public function writePacket(Packet $packet) {
        $this->_write_packets[] = $packet;
    }

    /**
     * @return \Vermi0ffh\Coin\Storage\Impl\PdoStore
     */
    public function getStore() {
        return $this->_store;
    }
}