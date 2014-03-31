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

/**
 * Base58 encoder, thanks to MagicalTux (https://bitcointalk.org/index.php?topic=1026.0);
 * @param $string
 * @return string
 */
function base58_encode($string) {
    $table = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';

    $long_value = gmp_init(bin2hex($string), 16);

    $result = '';
    while(gmp_cmp($long_value, 58) > 0) {
        list($long_value, $mod) = gmp_div_qr($long_value, 58);
        $result .= $table[gmp_intval($mod)];
    }
    $result .= $table[gmp_intval($long_value)];

    for($nPad = 0; $string[$nPad] == "\0"; ++$nPad);

    return str_repeat($table[0], $nPad).strrev($result);
}

/**
 * Base58 decoder, thanks to MagicalTux (https://bitcointalk.org/index.php?topic=1026.0);
 * @param $string
 * @return string
 */
function base58_decode($string) {
    $table = '123456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz';
    static $table_rev = null;
    if (is_null($table_rev)) {
        $table_rev = array();
        for($i=0;$i<58;++$i) $table_rev[$table[$i]]=$i;
    }

    $l = strlen($string);
    $long_value = gmp_init('0');
    for($i=0;$i<$l;++$i) {
        $c=$string[$l-$i-1];
        $long_value = gmp_add($long_value, gmp_mul($table_rev[$c], gmp_pow(58, $i)));
    }

    // php is lacking binary output for gmp
    $res = pack('H*', gmp_strval($long_value, 16));

    for($nPad = 0; $string[$nPad] == $table[0]; ++$nPad);
    return str_repeat("\0", $nPad).$res;
}