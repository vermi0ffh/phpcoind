<?php

namespace PhpCoinD\Protocol\Packet;

/**
 * Header of a packet
 * @package PhpCoinD\Protocol\Network
 */
class Header {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $magic;

    /**
     * @PhpCoinD\Annotation\ConstantString(length = 12)
     * @var string
     */
    public $command;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint32")
     * @var int
     */
    public $length;

    /**
     * @PhpCoinD\Annotation\ConstantString(length = 4)
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