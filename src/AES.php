<?php

namespace Tasofen\SecureTransfer;

class AES implements Secure
{
    protected $aesEncrypt;

    public function __construct(array $data) {
        $this->aesEncrypt = new AESEncrypt($data);
    }

    public function getData(array $data) {
        $str = $data['data'] ?? '';
        
        if ($str === false) {
            return null;
        }

        $data = $this->aesEncrypt->decrypt($str);
        $data = json_decode($data, true);
        return $data;
    }

    public function secureData(array $data) {
        $str = json_encode($data);
        $encrypted = $this->aesEncrypt->encrypt($str);
        return ['data' => $encrypted];
    }
}