<?php
namespace PhpCoinD\Protocol\Util\Impl;


use Exception;
use PhpCoinD\Protocol\Util\ChecksumComputer;


/**
 * Compute checksum using 4 first bytes of sha256(sha256(data))
 * @package PhpCoinD\Protocol\Util\Impl
 */
class DSha256ChecksumComputer implements ChecksumComputer {
    /**
     * Generic sha256 hasher
     * @param $string
     * @return string
     * @throws Exception
     */
    public function hash_sha256($string) {
        if (function_exists('hash')) return hash('sha256', $string, true);
        if (function_exists('mhash')) return mhash(MHASH_SHA256, $string);
        // insert native php implementation of sha256 here
        throw new Exception('Too lazy to fallback when the guy who configured php was lazy too');
    }


    /**
     * Double hash with SHA256
     * @param string $string
     * @return string string
     */
    public function hash($string) {
        return $this->hash_sha256($this->hash_sha256($string));
    }

    /**
     * @param string $raw_data
     * @return string
     */
    public function checksum($raw_data) {
        return substr($this->hash($raw_data), 0, 4);
    }
}