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

namespace PhpCoinD\Protocol;

use Exception;
use PhpCoinD\Protocol\Packet\Header,
    PhpCoinD\Protocol\Packet\Payload;
use PhpCoinD\Protocol\Util\Impl\DSha256ChecksumComputer,
    PhpCoinD\Protocol\Util\Serializer;
use PhpCoinD\Exception\PayloadParseException;
use PhpCoinD\Exception\StreamException;

class Packet {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Packet\Header")
     * @var Header
     */
    public $header;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Packet\Payload")
     * @var Payload
     */
    public $payload;


    /**
     * Default constructor
     */
    public function __construct() {
        $this->header = new Header();
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
                $classname = 'PhpCoinD\Protocol\Payload\Addr';
                break;

            case 'alert':
                $classname = 'PhpCoinD\Protocol\Payload\Alert';
                break;

            case 'block':
                $classname = 'PhpCoinD\Protocol\Payload\Block';
                break;

            case 'getblocks':
                $classname = 'PhpCoinD\Protocol\Payload\GetBlocks';
                break;

            case 'getdata':
                $classname = 'PhpCoinD\Protocol\Payload\GetData';
                break;

            case 'getheaders':
                $classname = 'PhpCoinD\Protocol\Payload\GetHeaders';
                break;

            case 'headers':
                $classname = 'PhpCoinD\Protocol\Payload\Headers';
                break;

            case 'inv':
                $classname = 'PhpCoinD\Protocol\Payload\Inv';
                break;

            case 'notfound':
                $classname = 'PhpCoinD\Protocol\Payload\NotFound';
                break;

            case 'tx':
                $classname = 'PhpCoinD\Protocol\Payload\Tx';
                break;

            case 'version':
                $classname = 'PhpCoinD\Protocol\Payload\Version';
                break;

            case 'verack':
                $classname = 'PhpCoinD\Protocol\Payload\Void';
                break;

            default:
                throw new Exception("No Payload class found for message type : " . $this->header->command);
        }

        return $classname;
    }

    /**
     * @PhpCoinD\Annotation\Serialize
     * @param $serializer Serializer
     * @param $stream resource
     * @return bool
     * @throws \PhpCoinD\Exception\StreamException
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
     * @PhpCoinD\Annotation\Unserialize
     * @param $serializer Serializer
     * @param $stream resource
     * @return bool
     * @throws \PhpCoinD\Exception\StreamException
     * @throws \Exception
     */
    public function unserialize($serializer, $stream) {
        // Read the header
        $this->header = $serializer->read_object($stream, 'PhpCoinD\Protocol\Packet\Header');
        if (!($this->header instanceof Header)) {
            // Wow, what did append here ?
            throw new StreamException();
        }

        // Read the raw payload
        if ($this->header->length > 0) {
            $this->payload = $serializer->read_raw($stream, $this->header->length);
        } else {
            // Empty payload !
            $this->payload = '';
        }

        // Compute checksum
        $checksummer = new DSha256ChecksumComputer();
        $checksum = $checksummer->checksum($this->payload);
        if ($checksum != $this->header->checksum) {
            throw new StreamException();
        }

        // Create a stream from the raw payload
        $stream_payload = fopen('data://application/octet-stream;base64,' . base64_encode($this->payload), 'r');
        try {
            // Parse the Payload according to the packet type
            $this->payload = $serializer->read_object($stream_payload, $this->getPayloadClassName());
        } catch (Exception $e) {
            fclose($stream_payload);

            // Forward exception
            throw new PayloadParseException($e);
        }

        fclose($stream_payload);

        // We did the job
        return true;
    }
}