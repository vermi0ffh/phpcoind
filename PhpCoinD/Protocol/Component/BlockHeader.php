<?php
namespace PhpCoinD\Protocol\Component;


class BlockHeader extends BlockHeaderShort {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint")
     * @var int
     */
    public $txn_count;
} 