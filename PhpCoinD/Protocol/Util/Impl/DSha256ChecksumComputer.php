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

use Exception;
use PhpCoinD\Protocol\Util\ChecksumComputer;

/**
 * Compute checksum using 4 first bytes of sha256(sha256(data))
 * @package PhpCoinD\Protocol\Util\Impl
 */
class DSha256ChecksumComputer implements ChecksumComputer {
    /**
     * Generic sha256 hasher
     * @param $string
     * @return string
     * @throws Exception
     */
    public function hash_sha256($string) {
        if (function_exists('hash')) return hash('sha256', $string, true);
        if (function_exists('mhash')) return mhash(MHASH_SHA256, $string);
        // insert native php implementation of sha256 here
        throw new Exception('Too lazy to fallback when the guy who configured php was lazy too');
    }


    /**
     * Double hash with SHA256
     * @param string $string
     * @return string string
     */
    public function hash($string) {
        return $this->hash_sha256($this->hash_sha256($string));
    }

    /**
     * @param string $raw_data
     * @return string
     */
    public function checksum($raw_data) {
        return substr($this->hash($raw_data), 0, 4);
    }
}