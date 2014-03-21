<?php
namespace Vermi0ffh\Coin\Payload;


use Vermi0ffh\Coin\Annotation\Serializable;
use Vermi0ffh\Coin\Component\Hash;
use Vermi0ffh\Coin\Network\Payload;

class GetBlocks implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $version;

    /**
     * @Vermi0ffh\Coin\Annotation\Set(set_type = "Vermi0ffh\Coin\Component\Hash")
     * @var Hash[]
     */
    public $block_locator_hashes;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "Vermi0ffh\Coin\Component\Hash")
     * @var Hash
     */
    public $hash_stop;
} 