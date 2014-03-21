<?php
namespace Vermi0ffh\Coin\Payload;
use Vermi0ffh\Coin\Component\Packet;


/**
 * Class implemented by PayLoad analyzers
 * @package Vermi0ffh\Coin\Component
 */
abstract class Payload {
    protected $_packet;

    /**
     * @param string $payload
     * @return Payload
     */
    public static function fromString($payload) {
        // Create a stream
        $stream = fopen('data://application/octet-stream;base64,' . base64_encode($payload),'r');

        $ret = static::fromStream($stream);

        fclose($stream);

        return $ret;
    }

    //public static abstract function fromStream($stream);

    /**
     * @return Packet
     */
    public function getPacket() {
        return $this->_packet;
    }

    /**
     * @param Packet $packet
     */
    public function setPacket($packet) {
        $this->_packet = $packet;
    }

    /**
     * Write the payload to a stream
     * @param resource $stream
     * @return string
     */
    public abstract function toStream($stream);


    /**
     * Get the payload as raw data
     * @return string
     */
    public function toRawString() {
        $stream = fopen('php://memory', 'r+');
        $this->toStream($stream);

        rewind($stream);
        $ret = stream_get_contents($stream);
        fclose($stream);

        return $ret;
    }
} 