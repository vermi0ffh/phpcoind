<?php

namespace Vermi0ffh\Coin\Util;


interface Reader {
    /**
     * Read a boolean value
     * @param resource $stream
     * @return bool
     */
    function read_bool($stream);


    /**
     * Read raw data of fixed size
     * @param resource $stream
     * @param int $length
     * @return string
     */
    function read_raw($stream, $length);


    /**
     * Read a variable-length integer
     * @param resource $stream
     * @return int
     */
    function read_uint($stream);


    /**
     * Read a 16bit little endian int
     * @param resource $stream
     * @return int
     */
    function read_uint16($stream);


    /**
     * Read a 16bit big endian int
     * @param resource $stream
     * @return int
     */
    function read_uint16be($stream);


    /**
     * Read a 32bit little endian int
     * @param resource $stream
     * @return int
     */
    function read_uint32($stream);

    /**
     * Read a 64bit little endian int
     * @param resource $stream
     * @return int
     */
    function read_uint64($stream);


    /**
     * Read a set of values
     * @param resource $stream
     * @param string $set_type 'bool', 'uint16', 'uint16be', 'uint32', 'uint64', 'uint', 'string'
     * @return mixed
     */
    function read_set($stream, $set_type);


    /**
     * Read a string
     * @param resource $stream
     * @return string
     */
    function read_string($stream);
} 