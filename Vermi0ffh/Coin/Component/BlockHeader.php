<?php
namespace Vermi0ffh\Coin\Component;


class BlockHeader extends BlockHeaderShort {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint")
     * @var int
     */
    public $txn_count;
} 