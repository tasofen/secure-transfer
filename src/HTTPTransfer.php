<?php

namespace Tasofen\SecureTransfer;

class HTTPTransfer extends BaseTransfer
{
    private $url;
    private $timeout;

    public function __construct($url) {
        $this->url = $url;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function send(array $data) {
        $data = $this->secureData($data);
        
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->timeout !== null) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        }

        $res = curl_exec($ch);
        
        if ($res===false || curl_errno($ch) || curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) {
            return null;
        }

        curl_close($ch);
        $response = json_decode($res, true);

        if ($response === null) {
            return null;
        }

        return $this->getData($response);
    }

}