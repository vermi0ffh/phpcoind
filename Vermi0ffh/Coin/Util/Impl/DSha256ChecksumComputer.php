<?php
namespace Vermi0ffh\Coin\Util\Impl;


use Exception;
use Vermi0ffh\Coin\Util\ChecksumComputer;


/**
 * Compute checksum using 4 first bytes of sha256(sha256(data))
 * @package Vermi0ffh\Coin\Util\Impl
 */
class DSha256ChecksumComputer implements ChecksumComputer {
    /**
     * Generic sha256 hasher
     * @param $string
     * @return string
     * @throws Exception
     */
    function hash_sha256($string) {
        if (function_exists('hash')) return hash('sha256', $string, true);
        if (function_exists('mhash')) return mhash(MHASH_SHA256, $string);
        // insert native php implementation of sha256 here
        throw new Exception('Too lazy to fallback when the guy who configured php was lazy too');
    }

    /**
     * @param string $raw_data
     * @return string
     */
    public function checksum($raw_data) {
        return substr($this->hash_sha256($this->hash_sha256($raw_data)), 0, 4);
    }
}