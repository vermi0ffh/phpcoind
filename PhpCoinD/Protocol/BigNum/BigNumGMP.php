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
 * Created 14/04/14 11:22 by Aurélien RICHAUD
 */

namespace PhpCoinD\Protocol\BigNum;


use PhpCoinD\Protocol\BigNum;
use PhpCoinD\Protocol\Util\Impl\AnnotatorObjectSerializer;

class BigNumGMP implements BigNum {
    protected $_bc_math_number;

    /**
     * @param mixed $nBits
     */
    public function __construct($nBits = null) {
        if ($nBits !== null) {
            if (is_int($nBits)) {
                $this->fromCompactInt($nBits);
            } else if (is_string($nBits)) {
                $this->fromUint256($nBits);
            }
        }
    }

    /**
     * Read a packed bits int (from a block header)
     * @param int $int
     * @return BigNum
     */
    public function fromCompactInt($int) {
        $int &= 0xffffffff;

        $nSize = $int >> 24;
        $fNegative = ($int & 0x00800000) != 0;
        $nWord = $int & 0x007fffff;

        // Init long word
        $this->_bc_math_number = gmp_init($nWord);

        if ($nSize <= 3) {
            $decal = gmp_pow(gmp_init(2), 8*(3-$nSize));
            $this->_bc_math_number = gmp_div_q($this->_bc_math_number, $decal);
        } else {
            $decal = gmp_pow(gmp_init(2),  8*($nSize-3));
            $this->_bc_math_number = gmp_mul($this->_bc_math_number, $decal);
        }

        // Negativ number
        if ($fNegative) {
            $this->_bc_math_number = gmp_mul($this->_bc_math_number, -1);
        }
    }

    /**
     * Read an int into a BigInt
     * @param $int int
     */
    public function fromInt($int) {
        $this->_bc_math_number = gmp_init($int);
    }

    /**
     * Read a uint256 hex string
     * @param string $string
     * @return BigNum
     */
    public function fromUint256($string) {
        $this->_bc_math_number = gmp_init($string, 16);
    }

    /**
     * @PhpCoinD\Annotation\Serialize
     * @param AnnotatorObjectSerializer $annotator_object_serializer
     * @param resource $stream
     */
    public function serialize($annotator_object_serializer, $stream) {
        $raw_number = gmp_strval($this->_bc_math_number, 16);
        if (strlen($raw_number) % 2 == 1) {
            $raw_number = '0' . $raw_number;
        }

        $raw_number = hex2bin($raw_number);

        $annotator_object_serializer->write_uint32be($stream, strlen($raw_number));
        $annotator_object_serializer->write_raw($stream, $raw_number);
    }

    /**
     * Return a hex representation of this number
     * @return string
     */
    public function toUint256() {
        return hex2bin(str_pad(gmp_strval($this->_bc_math_number, 16), 64, '0', STR_PAD_LEFT));
    }

    /**
     * Get the compact int representation of the number
     * @return int
     */
    public function toCompactInt() {
        // TODO: Implement toCompactInt() method.
    }

    /**
     * @PhpCoinD\Annotation\Unserialize
     * @param AnnotatorObjectSerializer $annotator_object_serializer
     * @param resource $stream
     */
    public function unserialize($annotator_object_serializer, $stream) {
        $length = $annotator_object_serializer->read_uint32be($stream);
        // Read the number
        $number = $annotator_object_serializer->read_raw($stream, $length);
        $this->_bc_math_number = gmp_init(bin2hex($number), 16);
    }
}