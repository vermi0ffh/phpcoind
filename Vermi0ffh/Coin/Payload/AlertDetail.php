<?php
namespace Vermi0ffh\Coin\Payload;

use Vermi0ffh\Coin\Annotation\Serializable,
    Vermi0ffh\Coin\Annotation\Set;


class AlertDetail {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $version;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint64")
     */
    public $relay_until;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint64")
     */
    public $expiration;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $id;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $cancel;

    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "uint32")
     */
    public $set_cancel;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $min_ver;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $max_ver;

    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "string")
     */
    public $set_sub_ver;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     */
    public $priority;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
     */
    public $comment;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
     */
    public $status_bar;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
     */
    public $reserved;
} 