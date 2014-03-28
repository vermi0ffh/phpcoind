<?php
namespace PhpCoinD\Protocol\Payload;

use PhpCoinD\Protocol\Packet\Payload;

class Headers implements Payload {
    /**
     * @PhpCoinD\Annotation\Set(set_type = "PhpCoinD\Protocol\Payload\Block")
     * @var Block[]
     */
    public $block_header;
} 