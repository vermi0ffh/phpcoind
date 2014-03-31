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

namespace PhpCoinD\Protocol\Component;

class AlertDetail {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $version;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $relay_until;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     */
    public $expiration;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $id;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $cancel;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "uint32")
     */
    public $set_cancel;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $min_ver;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $max_ver;

    /**
     * @PhpCoinD\Annotation\Set(set_type = "string")
     */
    public $set_sub_ver;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     */
    public $priority;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $comment;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $status_bar;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $reserved;
} 