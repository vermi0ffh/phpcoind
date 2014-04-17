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
 * Created 14/04/14 11:35 by Aurélien RICHAUD
 */

namespace PhpCoinD\Protocol\BigNum;


use PHPUnit_Framework_TestCase;

class BigNumBCMathTest extends PHPUnit_Framework_TestCase {
    public function testConstructor() {
        // Test with 0
        $test = new BigNumBCMath(0);
        $this->assertEquals($test->toUint256(), hex2bin('0000000000000000000000000000000000000000000000000000000000000000'));

        // Test with $nSize < 3
        $test = new BigNumBCMath(0x020fedc0);
        $this->assertEquals($test->toUint256(), hex2bin('0000000000000000000000000000000000000000000000000000000000000fed'));

        // Test with $nSize = 3
        $test = new BigNumBCMath(0x030fedc0);
        $this->assertEquals($test->toUint256(), hex2bin('00000000000000000000000000000000000000000000000000000000000fedc0'));

        // Test with $nSize > 3
        $test = new BigNumBCMath(0x040fedc0);
        $this->assertEquals($test->toUint256(), hex2bin('000000000000000000000000000000000000000000000000000000000fedc000'));

        // Test with DogeCoin genesis block
        $test = new BigNumBCMath(0x1e0ffff0);
        $this->assertTrue(strcmp($test->toUint256(), hex2bin('9156352c1818b32e90c9e792efd6a11a82fe7956a630f03bbee236cedae3911a')) < 0);
    }
} 