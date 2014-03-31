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

use Exception;
use PhpCoinD\Protocol\Component\AlertDetail;
use PhpCoinD\Protocol\Packet\Payload;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;

class Alert implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $payload;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $signature;

    /**
     * @var
     */
    protected $_alert_detail;

    /**
     * @return AlertDetail
     */
    public function getAlertDetail() {
        return $this->_alert_detail;
    }

    /**
     * @param AlertDetail $alert_detail
     */
    public function setAlertDetail($alert_detail) {
        $this->_alert_detail = $alert_detail;
    }

    /**
     * @param string $payload
     * @throws \Exception
     */
    public function setPayload($payload) {
        $this->payload = $payload;

        // Signature will not be valid anymore if you change the payload
        // Signature will remain if we set it after the payload
        $this->signature = '';


        /////////////////////////////////////////
        // Parse alert details
        $stream_payload = fopen('data://application/octet-stream;base64,' . base64_encode($this->payload), 'r+');
        try {
            $serializer = new NetworkSerializer();

            // Parse the Payload according to the packet type
            $this->_alert_detail = $serializer->read_object($stream_payload, 'PhpCoinD\Protocol\Component\AlertDetail');
        } catch (Exception $e) {
            fclose($stream_payload);

            // Forward exception
            throw $e;
        }

        fclose($stream_payload);
    }
} 