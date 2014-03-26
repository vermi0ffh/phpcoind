<?php

namespace PhpCoinD\Protocol\Payload;


use Exception;
use PhpCoinD\Protocol\Component\AlertDetail;
use PhpCoinD\Protocol\Packet\Payload;
use PhpCoinD\Protocol\Util\Impl\NetworkSerializer;

class Alert implements Payload {
    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $payload;

    /**
     * @PhpCoinD\Annotation\Serializable(type = "string")
     */
    public $signature;

    /**
     * @var
     */
    protected $_alert_detail;

    /**
     * @return AlertDetail
     */
    public function getAlertDetail() {
        return $this->_alert_detail;
    }

    /**
     * @param AlertDetail $alert_detail
     */
    public function setAlertDetail($alert_detail) {
        $this->_alert_detail = $alert_detail;
    }

    /**
     * @param string $payload
     * @throws \Exception
     */
    public function setPayload($payload) {
        $this->payload = $payload;

        // Signature will not be valid anymore if you change the payload
        // Signature will remain if we set it after the payload
        $this->signature = '';


        /////////////////////////////////////////
        // Parse alert details
        $stream_payload = fopen('data://application/octet-stream;base64,' . base64_encode($this->payload), 'r+');
        try {
            $serializer = new NetworkSerializer();

            // Parse the Payload according to the packet type
            $this->_alert_detail = $serializer->read_object($stream_payload, 'PhpCoinD\Protocol\Component\AlertDetail');
        } catch (Exception $e) {
            fclose($stream_payload);

            // Forward exception
            throw $e;
        }

        fclose($stream_payload);
    }
} 