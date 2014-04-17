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
 * Created 17/04/14 14:56 by Aurélien RICHAUD
 */

namespace PhpCoinD\Protocol\Component;


use PhpCoinD\Protocol\BigNum;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;

class CScript {
    const OP_0 = 0x00;
    const OP_1 = 0x51;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $raw_data;

    public function addElement($elem) {
        if (is_int($elem)) {
            if ($elem == -1 || ($elem >= 1 && $elem <= 16) ) {
                // OP Code
                $this->raw_data .= chr($elem + self::OP_1 - 1);
            } else {
                // BigNum
                $tmp = new BigNum\BigNumBCMath();
                $tmp->fromInt($elem);
                $elem = $tmp;
            }
        }

        // Serialize the BigNumber
        if ($elem instanceof BigNum) {
            $network_serializer = new NetworkSerializer();
            $stream = fopen('php://memory', 'r+');

            // Serialize the BigNum
            $network_serializer->write_object($stream, $elem);
            fseek($stream, 0);
            $this->raw_data .= stream_get_contents($stream);

            fclose($stream);
        }
    }
} 