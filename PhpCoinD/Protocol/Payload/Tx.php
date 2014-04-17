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

namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Component\Hash;
use PhpCoinD\Protocol\Component\TxIn;
use PhpCoinD\Protocol\Component\TxOut;
use PhpCoinD\Protocol\Packet\Payload;
use PhpCoinD\Protocol\Util\Impl\DSha256ChecksumComputer;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;

class Tx implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $version;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Component\TxIn")
     * @var TxIn[]
     */
    public $tx_in;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Component\TxOut")
     * @var TxOut[]
     */
    public $tx_out;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $lock_time;


    /**
     * Hash of the transaction
     * @var Hash
     */
    protected $hash;


    /**
     * Add a new input transaction
     * @param TxIn $tx_in
     */
    public function addTxIn($tx_in) {
        // Init array if needed
        if (!is_array($this->tx_in)) {
            $this->tx_in = array();
        }

        // Add the new input transaction
        $this->tx_in[] = $tx_in;
    }


    /**
     * Add a new input transaction
     * @param TxOut $tx_out
     */
    public function addTxOut($tx_out) {
        // Init array if needed
        if (!is_array($this->tx_out)) {
            $this->tx_out = array();
        }

        // Add the new input transaction
        $this->tx_out[] = $tx_out;
    }

    /**
     * @return \PhpCoinD\Protocol\Component\Hash
     */
    public function getHash() {
        // Compute the hash if needed
        if ($this->hash == null) {
            // A stream for object serialization
            $stream = fopen('php://memory', 'r+');

            $network_serializer = new NetworkSerializer();
            $network_serializer->write_object($stream, $this);
            $checksummer = new DSha256ChecksumComputer();

            fseek($stream, 0);

            // Compute transaction hash
            $tx_hash = $checksummer->hash(stream_get_contents($stream));

            $this->hash = new Hash($tx_hash);

            fclose($stream);
        }

        return $this->hash;
    }
} 