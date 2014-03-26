<?php
namespace PhpCoinD\Protocol\Util;


interface ChecksumComputer {
    /**
     * @param string $raw_data
     * @return string
     */
    public function checksum($raw_data);
} 