<?php
namespace PhpCoinD\Protocol\Util\Impl;
use PHPUnit_Framework_TestCase;


/**
 * Unit tests for DSha256ChecksumComputer
 * @package PhpCoinD\Protocol\Util\Impl
 */
class DSha256ChecksumComputerTest extends PHPUnit_Framework_TestCase {
    /**
     * @var DSha256ChecksumComputer
     */
    protected $hasher;

    public function setUp() {
        $this->hasher = new DSha256ChecksumComputer();
    }

    /**
     * Test hashing functions
     */
    public function testHash() {
        // Test some random hashes
        $this->assertTrue($this->hasher->hash('0') == hex2bin('67050eeb5f95abf57449d92629dcf69f80c26247e207ad006a862d1e4e6498ff'));
        $this->assertTrue($this->hasher->hash('123456789') == hex2bin('292b0d007566832db94bfae689cd70d1ab772811fd44b9f49d8550ee9ea6a494'));
    }


    /**
     * Test checksum functions
     */
    public function testChecksum() {
        $this->assertTrue($this->hasher->checksum('0') == hex2bin('67050eeb'));
        $this->assertTrue($this->hasher->checksum('123456789') == hex2bin('292b0d00'));
    }
} 