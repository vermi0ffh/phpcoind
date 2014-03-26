<?php
namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Component\BlockHeader;
use PhpCoinD\Protocol\Packet\Payload;

class Headers implements Payload {
    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Component\BlockHeader")
     * @var BlockHeader[]
     */
    public $block_header;
} 