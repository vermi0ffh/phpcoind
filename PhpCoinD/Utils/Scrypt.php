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
 * Created 11/04/14 10:08 by Aurélien RICHAUD
 */

namespace PhpCoinD\Utils;
use Exception;

require_once __DIR__ . '/pbkdf2.php';

/**
 * Scrypt implementation in full-php
 * Inspired by the GO implementation here : https://code.google.com/p/go/source/browse/?repo=crypto#hg%2Fscrypt
 * @package PhpCoinD\Utils
 */
class Scrypt {
    /**
     * blockXOR XORs numbers from dst with n numbers from src.
     * @param int[] $dst
     * @param int[]$src
     * @param int $n
     */
    function blockXOR(&$dst, $src, $n) {
        $n = min($n, count($dst), count($src));
        for($i=0; $i<$n; $i++) {
            $dst[$i] ^= $src[$i];
        }
    }


    /**
     * salsaXOR applies Salsa20/8 to the XOR of 16 numbers from tmp and in,
     * and puts the result into both both tmp and out.
     * @param int[] $tmp
     * @param int[] $in
     * @param int[] $out
     */
    function salsaXOR(&$tmp, $in, &$out) {
        $out = array();

        $w = array();
        for($i=0; $i<16; $i++) {
            $w[$i] = $tmp[$i] ^ $in[$i];
        }

        // array copy
        $x = $w;

        for($i=0; $i<8; $i+=2) {

            $u = $x[0] + $x[12];
            $x[4] ^= $u<<7 | $u>>(32-7);
            $u = $x[4] + $x[0];
            $x[8] ^= $u<<9 | $u>>(32-9);
            $u = $x[8] + $x[4];
            $x[12] ^= $u<<13 | $u>>(32-13);
            $u = $x[12] + $x[8];
            $x[0] ^= $u<<18 | $u>>(32-18);

            $u = $x[5] + $x[1];
            $x[9] ^= $u<<7 | $u>>(32-7);
            $u = $x[9] + $x[5];
            $x[13] ^= $u<<9 | $u>>(32-9);
            $u = $x[13] + $x[9];
            $x[1] ^= $u<<13 | $u>>(32-13);
            $u = $x[1] + $x[13];
            $x[5] ^= $u<<18 | $u>>(32-18);

            $u = $x[10] + $x[6];
            $x[14] ^= $u<<7 | $u>>(32-7);
            $u = $x[14] + $x[10];
            $x[2] ^= $u<<9 | $u>>(32-9);
            $u = $x[2] + $x[14];
            $x[6] ^= $u<<13 | $u>>(32-13);
            $u = $x[6] + $x[2];
            $x[10] ^= $u<<18 | $u>>(32-18);

            $u = $x[15] + $x[11];
            $x[3] ^= $u<<7 | $u>>(32-7);
            $u = $x[3] + $x[15];
            $x[7] ^= $u<<9 | $u>>(32-9);
            $u = $x[7] + $x[3];
            $x[11] ^= $u<<13 | $u>>(32-13);
            $u = $x[11] + $x[7];
            $x[15] ^= $u<<18 | $u>>(32-18);

            $u = $x[0] + $x[3];
            $x[1] ^= $u<<7 | $u>>(32-7);
            $u = $x[1] + $x[0];
            $x[2] ^= $u<<9 | $u>>(32-9);
            $u = $x[2] + $x[1];
            $x[3] ^= $u<<13 | $u>>(32-13);
            $u = $x[3] + $x[2];
            $x[0] ^= $u<<18 | $u>>(32-18);

            $u = $x[5] + $x[4];
            $x[6] ^= $u<<7 | $u>>(32-7);
            $u = $x[6] + $x[5];
            $x[7] ^= $u<<9 | $u>>(32-9);
            $u = $x[7] + $x[6];
            $x[4] ^= $u<<13 | $u>>(32-13);
            $u = $x[4] + $x[7];
            $x[5] ^= $u<<18 | $u>>(32-18);

            $u = $x[10] + $x[9];
            $x[11] ^= $u<<7 | $u>>(32-7);
            $u = $x[11] + $x[10];
            $x[8] ^= $u<<9 | $u>>(32-9);
            $u = $x[8] + $x[11];
            $x[9] ^= $u<<13 | $u>>(32-13);
            $u = $x[9] + $x[8];
            $x[10] ^= $u<<18 | $u>>(32-18);

            $u = $x[15] + $x[14];
            $x[12] ^= $u<<7 | $u>>(32-7);
            $u = $x[12] + $x[15];
            $x[13] ^= $u<<9 | $u>>(32-9);
            $u = $x[13] + $x[12];
            $x[14] ^= $u<<13 | $u>>(32-13);
            $u = $x[14] + $x[13];
            $x[15] ^= $u<<18 | $u>>(32-18);
        }

        for($i=0; $i<16;$i++) {
            $x[$i] += $w[$i];
            $out[$i] = $tmp[$i] = $x[$i];
        }
    }

    /**
     * @param int[] $tmp
     * @param int[]$in
     * @param int[]$out
     * @param int $r
     */
    function blockMix(&$tmp, $in, &$out, $r) {
        array_splice($tmp, 0, 16, array_slice($in, (2*$r-1)*16));
        for($i = 0; $i<2*$r; $i+=2) {
            $res = array();
            $this->salsaXOR($tmp, array_slice($in, $i*16), $res);
            array_splice($out, $i*8, 16, $res);
            $this->salsaXOR($tmp, array_slice($in, $i*16+16), $res);
            array_splice($out, $i*8+$r*16, 16, $res);
        }
    }

    /**
     * @param int[] $b
     * @param int $r
     * @return int
     */
    function integer($b, $r) {
        $j = (2*$r - 1) * 16;
        return $b[$j] | (($b[$j+1])<<32);
    }


    /**
     * @param string $b
     * @param int $r
     * @param int $N
     * @param int[] $v
     * @param int[] $xy
     */
    function smix(&$b , $r, $N, $v, $xy) {
        $r32 = 32*$r;
        $tmp = array_fill(0, 16, 0);
        $x = $xy;
        $y = array_slice($xy, 32*$r);

        $j = 0;
        for($i=0; $i<32*$r; $i++) {
            $unpacked = unpack('V', $b);
            $x[$i] = $unpacked[1];
            $j += 4;
        }

        for($i=0; $i<$N; $i+=2) {
            array_splice($v, $i*$r32, $r32, $x);
            $this->blockMix($tmp, $x, $y, $r);

            array_splice($v, ($i+1)*$r32, $r32, $y);
            $this->blockMix($tmp, $y, $x, $r);
        }

        for($i=0; $i<$N; $i+=2) {
            $j = $this->integer($x, $r) & ($N-1);
            $this->blockXOR($x, array_slice($v, $j*$r32), $r32);
            $this->blockMix($tmp, $x, $y, $r);

            $j = $this->integer($y, $r) & ($N -1);
            $this->blockXOR($y, array_slice($v, $j*$r32), $r32);
            $this->blockMix($tmp, $y, $x, $r);
        }

        foreach(array_slice($x, 0, $r32) as $v) {
            // $v as little endian 32 bits int
            $v_packed = pack('V', $v);
            for($c=0; $c<4; $c++) {
                $b{$j+$c} = $v_packed{$c};
            }
            $j += 4;
        }
    }

    /**
     * @param string $password
     * @param string $salt
     * @param int $N
     * @param int $r
     * @param int $p
     * @param int $keyLen
     * @throws Exception
     * @return string
     */
    function key($password, $salt, $N, $r, $p, $keyLen) {
        if ($N <=1 || $N&($N-1) != 0) {
            throw new Exception("scrypt: N must be > 1 and a power of 2");
        }

        if ($r*$p >= 1<<30 || $r > PHP_INT_MAX/(128*$p) || $r > PHP_INT_MAX/256 || $N > PHP_INT_MAX/(128*$r)) {
            throw new Exception("scrypt: parameters are too large");
        }

        $xy = array_fill(0, 64*$r, 0);
        $v = array_fill(0, 32*$N*$r, 0);
        $b = hash_pbkdf2('sha256', $password, $salt, 1, $p*128*$r, true);

        for($i=0; $i<$p; $i++) {
            $tmp = substr($b, $i*128*$r);
            $this->smix($tmp, $r, $N, $v, $xy);
            $b = substr($b, 0, $i*128*$r) . $tmp;
        }

        return hash_pbkdf2('sha256', $password, $b, 1, $keyLen, true);
    }
} 