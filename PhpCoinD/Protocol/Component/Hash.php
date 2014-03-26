<?php
namespace PhpCoinD\Protocol\Component;

class Hash {
    /**
     * @PhpCoinD\Annotation\ConstantString(length = 32)
     * @var string
     */
    public $value;

    public function __construct($value = null) {
        $this->value = $value;
    }
} 