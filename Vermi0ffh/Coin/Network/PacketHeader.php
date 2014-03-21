<?php

namespace Vermi0ffh\Coin\Network;

use Vermi0ffh\Coin\Annotation\Serializable;
use Vermi0ffh\Coin\Annotation\ConstantString;

/**
 * Header of a packet
 * @package Vermi0ffh\Coin\Network
 */
class PacketHeader {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $magic;

    /**
     * @Vermi0ffh\Coin\Annotation\ConstantString(length = 12)
     * @var string
     */
    public $command;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $length;

    /**
     * @Vermi0ffh\Coin\Annotation\ConstantString(length = 4)
     * @var string
     */
    public $checksum;

    /**
     * @param string $command
     */
    public function setCommand($command) {
        $this->command = trim($command);
    }
} 