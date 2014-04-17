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

namespace PhpCoinD\Protocol\Util;

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
     * Read a 32bit big endian int
     * @param resource $stream
     * @return int
     */
    function read_uint32be($stream);

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