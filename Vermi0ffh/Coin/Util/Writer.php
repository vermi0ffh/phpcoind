<?php
namespace Vermi0ffh\Coin\Util;


interface Writer {
    /**
     * Write a boolean value
     * @param resource $stream
     * @param bool $bool
     */
    function write_bool($stream, $bool);


    /**
     * Write raw data
     * @param resource $stream
     * @param string $data
     */
    function write_raw($stream, $data);

    /**
     * Write a variable-length integer
     * @param resource $stream
     * @param $int
     */
    function write_uint($stream, $int);

    /**
     * Write a 16bit little endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint16($stream, $int);


    /**
     * Write a 16bit big endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint16be($stream, $int);

    /**
     * Write a 32bit little endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint32($stream, $int);

    /**
     * Write a 64bit little endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint64($stream, $int);


    /**
     * Write a set of elements
     * @param resource $stream
     * @param array $set
     * @param string $set_type 'bool', 'uint16', 'uint16be', 'uint32', 'uint64', 'uint', 'string'
     * @internal param string $string
     */
    function write_set($stream, $set, $set_type);

    /**
     * Write a string
     * @param resource $stream
     * @param string $string
     */
    function write_string($stream, $string);
} 