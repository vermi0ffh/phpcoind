<?php
namespace Vermi0ffh\Coin\Util\Impl;


use Vermi0ffh\Exception\StreamException;
use Vermi0ffh\Exception\UnsupportedTypeException;

class NetworkSerializer extends AnnotatorObjectSerializer {
    /**
     * Read a boolean value
     * @param resource $stream
     * @throws StreamException
     * @return bool
     */
    function read_bool($stream) {
        $ret = fread($stream, 1);

        if (strlen($ret) != 1) {
            throw new StreamException();
        }

        return (bool)$ret;
    }

    /**
     * Read raw data of fixed size
     * @param resource $stream
     * @param int $length
     * @throws StreamException
     * @return string
     */
    function read_raw($stream, $length) {
        $ret = fread($stream, $length);

        if (strlen($ret) != $length) {
            throw new StreamException();
        }

        return $ret;
    }

    /**
     * Read a variable-length integer
     * @param resource $stream
     * @return int
     */
    function read_uint($stream) {
        $ret = ord(fread($stream, 1));

        if ($ret < 0xfd) {
            // 1-byte int, already read
        } else if ($ret == 0xfd) {
            $ret = $this->read_uint16($stream);
        } else if ($ret == 0xfe) {
            $ret = $this->read_uint32($stream);
        } else if ($ret == 0xff) {
            $ret = $this->read_uint64($stream);
        }

        // Return the int
        return $ret;
    }

    /**
     * Read a 16bit little endian int
     * @param resource $stream
     * @throws StreamException
     * @return int
     */
    function read_uint16($stream) {
        $ret = fread($stream, 2);

        if (strlen($ret) != 2) {
            throw new StreamException();
        }

        $ret = unpack('v', $ret); // 16 bits unsigned integer little endian
        return $ret[1];
    }

    /**
     * Read a 16bit big endian int
     * @param resource $stream
     * @throws StreamException
     * @return int
     */
    function read_uint16be($stream) {
        $ret = fread($stream, 2);

        if (strlen($ret) != 2) {
            throw new StreamException();
        }

        $ret = unpack('n', $ret); // 16 bits unsigned integer little endian
        return $ret[1];
    }

    /**
     * Read a 32bit little endian int
     * @param resource $stream
     * @throws StreamException
     * @return int
     */
    function read_uint32($stream) {
        $ret = fread($stream, 4);

        if (strlen($ret) != 4) {
            throw new StreamException();
        }

        $ret = unpack('V', $ret); // 32 bits unsigned integer little endian
        return $ret[1];
    }

    /**
     * Read a 64bit little endian int
     * @param resource $stream
     * @throws StreamException
     * @return int
     */
    function read_uint64($stream) {
        $ret = fread($stream, 8);

        if (strlen($ret) != 8) {
            throw new StreamException();
        }

        list($lower, $higher) = array_values(unpack('V2', $ret)); // 64 bits unsigned integer little endian
        return ($higher << 32) | $lower;
    }

    /**
     * Read a set of values
     * @param resource $stream
     * @param string $set_type 'bool', 'uint16', 'uint16be', 'uint32', 'uint64', 'uint' or 'string'
     * @throws UnsupportedTypeException
     * @return mixed
     */
    function read_set($stream, $set_type) {
        // Check if the asked type is readable
        if ( !method_exists($this, 'read_'.$set_type) ) {
            if (class_exists($set_type, true)) {
                // If the type is a class, we use the object reader
                // Callable read function
                $read_func = array(
                    $this,
                    'read_object',
                );
                $params = array(
                    $stream,
                    $set_type
                );
            } else {
                // Type not defined
                throw new UnsupportedTypeException($set_type);
            }
        } else {
            // Callable read function
            $read_func = array(
                $this,
                'read_'.$set_type,
            );
            $params = array(
                $stream
            );
        }

        // Read the set length
        $set_length = $this->read_uint($stream);
        $ret = array();

        // Read each elements of the set
        while($set_length > 0) {
            $ret[] = call_user_func_array($read_func, $params);
            $set_length --;
        }

        return $ret;
    }

    /**
     * Read a string
     * @param resource $stream
     * @throws \Vermi0ffh\Exception\StreamException
     * @return string
     */
    function read_string($stream) {
        // Read string length
        $string_length = $this->read_uint($stream);
        if ($string_length === false) {
            return false;
        }

        // Check empty string
        if ($string_length === 0) {
            return '';
        }

        // Read the string
        $ret = fread($stream, $string_length);

        // Check string length
        if (strlen($ret) != $string_length) {
            throw new StreamException();
        }

        return $ret;
    }

    /**
     * Write a boolean value
     * @param resource $stream
     * @param bool $bool
     */
    function write_bool($stream, $bool) {
        fwrite($stream, pack('c', $bool));
    }

    /**
     * Write raw data
     * @param resource $stream
     * @param string $data
     */
    function write_raw($stream, $data) {
        fwrite($stream, $data);
    }

    /**
     * Write a variable-length integer
     * @param resource $stream
     * @param int $int
     */
    function write_uint($stream, $int) {
        if ($int < 0xfd) {
            fwrite($stream, pack('c', $int));
        } else if ($int <= 0xffff) {
            fwrite($stream, pack('c', 0xfd));
            $this->write_uint16($stream, $int);
        } else if ($int <= 0xffffffff) {
            fwrite($stream, pack('c', 0xfe));
            $this->write_uint32($stream, $int);
        } else {
            fwrite($stream, pack('c', 0xff));
            $this->write_uint64($stream, $int);
        }
    }

    /**
     * Write a 16bit little endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint16($stream, $int) {
        fwrite($stream, pack('v', $int));
    }

    /**
     * Write a 16bit big endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint16be($stream, $int) {
        fwrite($stream, pack('n', $int));
    }

    /**
     * Write a 32bit little endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint32($stream, $int) {
        fwrite($stream, pack('V', $int));
    }

    /**
     * Write a 64bit little endian int
     * @param resource $stream
     * @param int $int
     */
    function write_uint64($stream, $int) {
        fwrite($stream, pack('V', $int & 0x00000000ffffffff) . pack('V', ($int & 0xffffffff00000000) >> 32));
    }

    /**
     * Write a set of elements
     * @param resource $stream
     * @param array $set
     * @param string $set_type 'bool', 'uint16', 'uint16be', 'uint32', 'uint64', 'uint', 'string'
     * @throws UnsupportedTypeException
     */
    function write_set($stream, $set, $set_type) {
        // Check if the asked type is readable
        if ( !method_exists($this, 'write_'.$set_type) ) {
            if (!class_exists($set_type)) {
                throw new UnsupportedTypeException($set_type);
            }

            // We know how to write objects
            $set_type = 'object';
        }

        // Callable read function
        $write_func = array(
            $this,
            'write_'.$set_type,
        );

        // Write set length
        $this->write_uint($stream, count($set));

        // Write each element of the set
        foreach($set as $element) {
            call_user_func($write_func, $stream, $element);
        }
    }

    /**
     * Write a string
     * @param resource $stream
     * @param string $string
     */
    function write_string($stream, $string) {
        // Write string length
        $this->write_uint($stream, strlen($string));
        // Write string content
        $this->write_raw($stream, $string);
    }
}