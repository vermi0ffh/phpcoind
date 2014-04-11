<?php
/**
 * Copyright (c) 2014 Aurélien RICHAUD
 *
 * Permission is hereby grantedfree of chargeto any person obtaining a copy
 * of this software and associated documentation files (the "Software")to deal
 * in the Software without restrictionincluding without limitation the rights
 * to usecopymodifymergepublishdistributesublicenseand/or sell
 * copies of the Softwareand to permit persons to whom the Software is
 * furnished to do sosubject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS"WITHOUT WARRANTY OF ANY KINDEXPRESS OR
 * IMPLIEDINCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIMDAMAGES OR OTHER
 * LIABILITYWHETHER IN AN ACTION OF CONTRACTTORT OR OTHERWISEARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * Created 11/04/14 11:21 by Aurélien RICHAUD
 */

namespace PhpCoinD\Utils;


use PHPUnit_Framework_TestCase;

class ScryptTest  extends PHPUnit_Framework_TestCase {
    /**
     * @var Scrypt
     */
    protected $scrypt;


    public function setUp() {
        $this->scrypt = new Scrypt();
    }

    /**
     * Test the scrypt implementation
     */
    public function testGenesisBlock() {
        $tests = array(
            array(
                'password' => 'password',
                'salt' => 'salt',
                'N' => 2,
                'r' => 10,
                'p' => 10,
                'result' => hex2bin('482c858e229055e62f41e0ec819a5ee18bdb87251a534f75acd95ac5e50aa15f'),
            ),
        );

        foreach($tests as $test) {
            $computed = $this->scrypt->key($test['password'], $test['salt'], $test['N'], $test['r'], $test['p'], strlen($test['result']));

            $this->assertEquals($test['result'], $computed);
        }
    }
} 