<?php
namespace PhpCoinD\Protocol\Component;

use Exception;
/**
 * Representation of a Network Address
 * @package PhpCoinD\Protocol\Component
 */
class NetworkAddress {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint64")
     * @var int
     */
    public $services;

    /**
     * @PhpCoinD\Annotation\ConstantString(length = 16)
     */
    public $ip;

    /**
     * The parsed version of the IP
     * @var string
     */
    protected $_parsed_ip;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "uint16be")
     * @var int
     */
    public $port;

    /**
     * Set raw IP
     * @param string $ip
     */
    public function setIp($ip) {
        $this->ip = $ip;

        // Set parsed IP
        $this->_parsed_ip = $this->ipFromRawToString($ip);
    }

    /**
     * @param string $parsed_ip
     */
    public function setParsedIp($parsed_ip) {
        $this->_parsed_ip = $parsed_ip;

        // Set RAW IP
        $this->ip = $this->ipFromStringToRaw($parsed_ip);
    }

    /**
     * @return string
     */
    public function getParsedIp() {
        return $this->_parsed_ip;
    }


    /**
     * Get IP version of a string representation of an IP
     * @param $ip
     * @return bool|int
     */
    protected function getIpVersion($ip) {
        // IPv4 regex, took it here : http://www.sroze.io/2008/10/09/regex-ipv4-et-ipv6/
        if (preg_match('#^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$#', $ip) == 1) {
            return 4;
        }

        // IPv6 regex, took it here : http://www.sroze.io/2008/10/09/regex-ipv4-et-ipv6/
        if (preg_match('#^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b).){3}(b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b))|(([0-9A-Fa-f]{1,4}:){0,5}:((b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b).){3}(b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b))|(::([0-9A-Fa-f]{1,4}:){0,5}((b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b).){3}(b((25[0-5])|(1d{2})|(2[0-4]d)|(d{1,2}))b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$#', $ip) == 1) {
            return 6;
        }

        // Unknown IP type
        return false;
    }


    /**
     * Convert a raw representation of an IP to a string version
     * @param string $raw
     * @return string|false
     */
    protected function ipFromRawToString($raw) {
        if (strlen($raw) != 16) {
            return false;
        }

        // Check IP version
        if (strpos($raw, "\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\xff\xff") === 0) {
            // IPv4
            $raw = substr($raw, 12);
            // 4 bytes
            $ret = ord($raw{0}).'.'.ord($raw{1}).'.'.ord($raw{2}).'.'.ord($raw{3});
        } else {
            // IPv6
            $ret = array();

            // IPv6 default format : 8 groups of 2 bytes, hexadecimal
            while(strlen($raw) > 0) {
                $ret[] = pack('H*', substr($raw, 0, 2));
                $raw = substr($raw, 2);
            }

            // Glue parts with ":"
            $ret = implode(':', $ret);
        }

        return $ret;
    }

    /**
     * @param string $ip_str
     * @throws \Exception
     * @return string
     */
    protected function ipFromStringToRaw($ip_str) {
        $ip_version = $this->getIPVersion($ip_str);

        if ($ip_version == false) {
            throw new Exception("Invalid IP : " . $ip_str);
        }

        if ($ip_version == 4) {
            if (preg_match('#(^10\.)|(^172\.1[6-9]\.)|(^172\.2[0-9]\.)|(^172\.3[0-1]\.)|(^192\.168\.)|(^127\.0\.0\.1)#', $ip_str) == 1) {
                // Non routable : set Ip to 0.0.0.0
                $parts = array('0', '0', '0', '0');
            } else {
                $parts = explode('.', $ip_str);
            }

            $ip_raw = "\x0\x0\x0\x0\x0\x0\x0\x0\x0\x0\xff\xff";
            foreach($parts as $part) {
                $ip_raw .= chr(intval($part));
            }
        } else {
            // IPv6
            // TODO : Support IPv6
            throw new Exception("Unsupported (IPv6) : " . $ip_str);
        }

        return $ip_raw;
    }


    /**
     * Create an object with ip and port represented as string
     * @param string $ip
     * @param int $port
     * @return \PhpCoinD\Protocol\Component\NetworkAddress
     */
    public static function fromString($ip, $port) {
        $ret = new self();

        $ret->setParsedIp($ip);
        $ret->port = $port;

        return $ret;
    }
} 