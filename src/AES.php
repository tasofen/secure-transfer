<?php

namespace Tasofen\SecureTransfer;

class AES implements Secure
{
    private $key;
    private $cipher = 'aes-256-cbc';
    private $ivLength = 16;
    
    public function __construct(array $data) {
        $this->key = $data['key'] ?? '';
        
        $cipher = $data['cipher'] ?? '';
        
        if ($cipher && in_array($cipher, \openssl_get_cipher_methods())) {
            $this->cipher = $cipher;
        }
        
        if (!$this->key) {
            throw new \Exception('Error input key');
        }
    }

    public function getData(array $data) {
        $str = $data['data'] ?? '';
        $ivLength = openssl_cipher_iv_length($this->cipher);
        $iv = substr($str, 0, $this->ivLength*2);
        $iv = hex2bin($iv);
        $str = substr($str, $ivLength*2);
        $data = openssl_decrypt($str, $this->cipher, $this->key, 0, $iv);
        $data = json_decode($data, true);
        return $data;
    }

    public function secureData(array $data) {
        $str = json_encode($data);
        $iv = random_bytes($this->ivLength);
        $encrypted = openssl_encrypt($str, $this->cipher, $this->key, 0, $iv);
        $encrypted = bin2hex($iv).$encrypted;
        return ['data' => $encrypted];
    }
}