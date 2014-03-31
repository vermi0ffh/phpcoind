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

namespace PhpCoinD\Protocol\Util\Impl;

use PHPUnit_Framework_TestCase;

/**
 * Unit tests for DSha256ChecksumComputer
 * @package PhpCoinD\Protocol\Util\Impl
 */
class DSha256ChecksumComputerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var DSha256ChecksumComputer
     */
    protected $hasher;

    public function setUp() {
        $this->hasher = new DSha256ChecksumComputer();
    }

    /**
     * Test hashing functions
     */
    public function testHash() {
        // Test some random hashes
        $this->assertTrue($this->hasher->hash('0') == hex2bin('67050eeb5f95abf57449d92629dcf69f80c26247e207ad006a862d1e4e6498ff'));
        $this->assertTrue($this->hasher->hash('123456789') == hex2bin('292b0d007566832db94bfae689cd70d1ab772811fd44b9f49d8550ee9ea6a494'));
    }


    /**
     * Test checksum functions
     */
    public function testChecksum() {
        $this->assertTrue($this->hasher->checksum('0') == hex2bin('67050eeb'));
        $this->assertTrue($this->hasher->checksum('123456789') == hex2bin('292b0d00'));
    }
} 