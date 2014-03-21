<?php

namespace Vermi0ffh\Coin\Payload;


use Exception;
use Vermi0ffh\Coin\Network\Payload;
use Vermi0ffh\Coin\Annotation\Serializable;
use Vermi0ffh\Coin\Util\Impl\NetworkSerializer;

class Alert implements Payload {
    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
     */
    public $payload;

    /**
     * @Vermi0ffh\Coin\Annotation\Serializable(type = "string")
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
            $this->_alert_detail = $serializer->read_object($stream_payload, 'Vermi0ffh\Coin\Payload\AlertDetail');
        } catch (Exception $e) {
            fclose($stream_payload);

            // Forward exception
            throw $e;
        }

        fclose($stream_payload);
    }
} 