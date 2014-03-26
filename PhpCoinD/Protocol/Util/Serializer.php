<?php

namespace PhpCoinD\Protocol\Util;

/**
 * Defining method for a Converter (Read / Write Serializable objects)
 * @package PhpCoinD\Protocol\Util
 */
interface Serializer extends Reader, Writer {
    /**
     * Read an object with the Reader Functions
     * @param resource $stream
     * @param string $class_name The name of the class we want to read
     * @return mixed An instance of the asked class
     */
    public function read_object($stream, $class_name);


    /**
     * Write an object with the Writer Functions
     * @param resource $stream
     * @param object $object An instance of a class we want to serialize
     */
    public function write_object($stream, $object);
} 