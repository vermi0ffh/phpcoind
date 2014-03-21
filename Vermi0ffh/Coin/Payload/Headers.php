<?php
namespace Vermi0ffh\Coin\Payload;


use Vermi0ffh\Coin\Annotation\Set;
use Vermi0ffh\Coin\Component\BlockHeader;
use Vermi0ffh\Coin\Network\Payload;

class Headers implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Component\BlockHeader")
     * @var BlockHeader[]
     */
    public $block_header;
} 