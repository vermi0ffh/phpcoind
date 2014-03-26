<?php
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