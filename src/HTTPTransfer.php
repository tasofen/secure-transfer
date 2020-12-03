<?php

namespace Tasofen\SecureTransfer;

class HTTPTransfer extends BaseTransfer
{
    private $url;
    
    public function __construct($url) {
        $this->url = $url;
    }

    public function send(array $data) {
        $data = $this->secureData($data);
        
        $ch = curl_init($this->url);
        
        
        return $data;
    }

}