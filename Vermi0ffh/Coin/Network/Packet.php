<?php
namespace Vermi0ffh\Coin\Network;

use Exception;
use Vermi0ffh\Coin\Network\PacketHeader;
use Vermi0ffh\Coin\Payload\Addr,
    Vermi0ffh\Coin\Payload\Alert,
    Vermi0ffh\Coin\Payload\GetBlocks,
    Vermi0ffh\Coin\Payload\GetData,
    Vermi0ffh\Coin\Payload\GetHeaders,
    Vermi0ffh\Coin\Payload\Headers,
    Vermi0ffh\Coin\Payload\Inv,
    Vermi0ffh\Coin\Payload\NotFound,
    Vermi0ffh\Coin\Payload\Tx,
    Vermi0ffh\Coin\Payload\Version,
    Vermi0ffh\Coin\Payload\Void;
use Vermi0ffh\Coin\Util\Impl\DSha256ChecksumComputer;
use Vermi0ffh\Coin\Util\Serializer;
use Vermi0ffh\Exception\PayloadParseException;
use Vermi0ffh\Exception\StreamException;

class Packet {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Network\PacketHeader")
     * @var PacketHeader
     */
    public $header;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Network\Payload")
     * @var Payload
     */
    public $payload;


    /**
     * Default constructor
     */
    public function __construct() {
        $this->header = new PacketHeader();
        $this->payload = null;
    }

    /**
     * Get class name of Payload based on command
     * @throws \Exception
     * @return string
     */
    public function getPayloadClassName() {
        $classname = null;

        switch($this->header->command) {
            case 'addr':
                $classname = 'Vermi0ffh\Coin\Payload\Addr';
                break;

            case 'alert':
                $classname = 'Vermi0ffh\Coin\Payload\Alert';
                break;

            case 'getblocks':
                $classname = 'Vermi0ffh\Coin\Payload\GetBlocks';
                break;

            case 'getdata':
                $classname = 'Vermi0ffh\Coin\Payload\GetData';
                break;

            case 'getheaders':
                $classname = 'Vermi0ffh\Coin\Payload\GetHeaders';
                break;

            case 'headers':
                $classname = 'Vermi0ffh\Coin\Payload\Headers';
                break;

            case 'inv':
                $classname = 'Vermi0ffh\Coin\Payload\Inv';
                break;

            case 'notfound':
                $classname = 'Vermi0ffh\Coin\Payload\NotFound';
                break;

            case 'tx':
                $classname = 'Vermi0ffh\Coin\Payload\Tx';
                break;

            case 'version':
                $classname = 'Vermi0ffh\Coin\Payload\Version';
                break;

            case 'verack':
                $classname = 'Vermi0ffh\Coin\Payload\Void';
                break;

            default:
                throw new Exception("No Payload class found for message type : " . $this->header->command);
        }

        return $classname;
    }

    /**
     * @Vermi0ffh\Coin\Annotation\Serialize
     * @param $serializer Serializer
     * @param $stream resource
     * @return bool
     * @throws \Vermi0ffh\Exception\StreamException
     * @throws \Exception
     */
    public function serialize($serializer, $stream) {
        /////////////////////////////////////////
        // Create a temporary stream to write the payload
        $payload_stream = fopen('php://memory', 'wb+');
        // Serialize the payload
        $serializer->write_object($payload_stream, $this->payload);
        // The the length of the serialized payload
        $this->header->length = ftell($payload_stream);

        /////////////////////////////////////////
        // Get the serialized payload
        $payload = stream_get_contents($payload_stream, $this->header->length, 0);
        fclose($payload_stream);

        /////////////////////////////////////////
        // Compute checksum
        $checksummer = new DSha256ChecksumComputer();
        $this->header->checksum = $checksummer->checksum($payload);


        /////////////////////////////////////////
        // Now we can write the header
        $serializer->write_object($stream, $this->header);


        /////////////////////////////////////////
        // Write the payload
        $serializer->write_raw($stream, $payload);

        // We managed to serialize it all :)
        return true;
    }

    /**
     * @Vermi0ffh\Coin\Annotation\Unserialize
     * @param $serializer Serializer
     * @param $stream resource
     * @throws \Vermi0ffh\Exception\StreamException
     * @throws \Exception
     */
    public function unserialize($serializer, $stream) {
        // Read the header
        $this->header = $serializer->read_object($stream, 'Vermi0ffh\Coin\Network\PacketHeader');
        if (!($this->header instanceof PacketHeader)) {
            // Wow, what did append here ?
            throw new StreamException();
        }

        // Read the raw payload
        $this->payload = $serializer->read_raw($stream, $this->header->length);

        // Compute checksum
        $checksummer = new DSha256ChecksumComputer();
        $checksum = $checksummer->checksum($this->payload);
        if ($checksum != $this->header->checksum) {
            throw new StreamException();
        }

        // Create a stream from the raw payload
        $stream_payload = fopen('data://application/octet-stream;base64,' . base64_encode($this->payload), 'r+');
        try {
            // Parse the Payload according to the packet type
            $this->payload = $serializer->read_object($stream_payload, $this->getPayloadClassName());
        } catch (Exception $e) {
            fclose($stream_payload);

            // Forward exception
            throw new PayloadParseException($e);
        }

        fclose($stream_payload);
    }
}