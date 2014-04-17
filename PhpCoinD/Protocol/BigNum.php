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
 * Created 14/04/14 11:20 by Aurélien RICHAUD
 */

namespace PhpCoinD\Protocol;


use PhpCoinD\Protocol\Util\Impl\AnnotatorObjectSerializer;

interface BigNum {
    /**
     * Read a packed bits int (from a block header)
     * @param int $int
     * @return BigNum
     */
    public function fromCompactInt($int);

    /**
     * Read an int into a BigInt
     * @param $int int
     */
    public function fromInt($int);

    /**
     * Read a uint256 hex string
     * @param string $string
     * @return BigNum
     */
    public function fromUint256($string);


    /**
     * @PhpCoinD\Annotation\Serialize
     * @param AnnotatorObjectSerializer $annotator_object_serializer
     * @param resource $stream
     */
    public function serialize($annotator_object_serializer, $stream);

    /**
     * Get the compact int representation of the number
     * @return int
     */
    public function toCompactInt();

    /**
     * Return a hex representation of this number
     * @return string
     */
    public function toUint256();

    /**
     * @PhpCoinD\Annotation\Unserialize
     * @param AnnotatorObjectSerializer $annotator_object_serializer
     * @param resource $stream
     */
    public function unserialize($annotator_object_serializer, $stream);
}