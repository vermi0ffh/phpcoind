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

use PhpCoinD\Protocol\Component\NetworkAddress;
use PhpCoinD\Protocol\Packet\Payload;

class Version implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $version;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $services;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $timestamp;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\NetworkAddress")
     * @var $addr_recv NetworkAddress;
     */
    public $addr_recv;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "PhpCoinD\Protocol\Component\NetworkAddress")
     * @var $addr_recv NetworkAddress;
     */
    public $addr_from;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $nonce;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $user_agent;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $start_height;

} 