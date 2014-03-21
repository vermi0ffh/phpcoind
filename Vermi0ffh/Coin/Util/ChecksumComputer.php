<?php
namespace Vermi0ffh\Coin\Util;


interface ChecksumComputer {
    /**
     * @param string $raw_data
     * @return string
     */
    public function checksum($raw_data);
} 