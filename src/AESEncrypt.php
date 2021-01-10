<?php

namespace Tasofen\SecureTransfer;

class AESEncrypt {
    private $key;
    private $cipher;

    public function __construct(array $data) {
        $this->key = $data['key'] ?? '';
        $cipher = $data['cipher'] ?? 'aes-256-cbc';
        
        $allowCipher = [
            'aes-128-cbc',
            // 'aes-128-cbc-hmac-sha1',
            // 'aes-128-cbc-hmac-sha256',
            'aes-128-cfb',
            'aes-128-cfb1',
            'aes-128-cfb8',
            'aes-128-ctr',
            'aes-128-ofb',
            // 'aes-128-xts',
            'aes-192-cbc',
            'aes-192-cfb',
            'aes-192-cfb1',
            'aes-192-cfb8',
            'aes-192-ctr',
            'aes-192-ofb',
            'aes-256-cbc',
            // 'aes-256-cbc-hmac-sha1',
            // 'aes-256-cbc-hmac-sha256',
            'aes-256-cfb',
            'aes-256-cfb1',
            'aes-256-cfb8',
            'aes-256-ctr',
            'aes-256-ofb',
            // 'aes-256-xts',
        ];

        if (in_array($cipher, $allowCipher)) {
            $this->cipher = $cipher;
        } else {
            throw new \Exception('Cipher not found!');
        }

        if (!$this->key) {
            throw new \Exception('Error input key');
        }
    }

    private function getKeyLength() {
        switch($this->cipher) {
            case 'aes-128-cbc':
            case 'aes-128-cfb':
            case 'aes-128-cfb1':
            case 'aes-128-cfb8':
            case 'aes-128-ctr':
            case 'aes-128-ofb':
                return 16;
            case 'aes-192-cbc':
            case 'aes-192-cfb':
            case 'aes-192-cfb1':
            case 'aes-192-cfb8':
            case 'aes-192-ctr':
            case 'aes-192-ofb':
                return 24;
            case 'aes-256-cbc':
            case 'aes-256-cfb':
            case 'aes-256-cfb1':
            case 'aes-256-cfb8':
            case 'aes-256-ctr':
            case 'aes-256-ofb':
                return 32;
        }
    }

    public function encrypt($str) {
        $salt = random_bytes(8);
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $keyLen = $this->getKeyLength();

        $pbkdf2 = openssl_pbkdf2($this->key, $salt, $ivlen+$keyLen, 10000, "sha256");
        $key = substr($pbkdf2, 0, $keyLen);
        $iv = substr($pbkdf2, $keyLen, $ivlen);

        $encrypted = openssl_encrypt($str, $this->cipher, $key, 0, $iv);
        $encrypted = base64_encode('Salted__'.$salt.base64_decode($encrypted));
        return $encrypted;
    }

    public function decrypt($str) {
        $str = base64_decode($str);
        
        if ($str === false) {
            return null;
        }

        $salt = substr($str, 8, 8);
        $str = substr($str, 16);
        $ivlen = openssl_cipher_iv_length($this->cipher);
        $keyLen = $this->getKeyLength();
        $pbkdf2 = openssl_pbkdf2($this->key, $salt, $keyLen+$ivlen, 10000, "sha256");
        
        $key = substr($pbkdf2, 0, $keyLen);
        $iv = substr($pbkdf2, $keyLen, $ivlen);
        $data = openssl_decrypt($str, $this->cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        return $data;
    }
}