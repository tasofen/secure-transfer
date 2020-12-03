<?php

namespace Tasofen\SecureTransfer;

class RSA implements Secure
{
    private $privateKey;
    private $publicKey;
    private $cipher = 'aes-256-cbc';
    private $ivLength = 16;
    
    public function __construct(array $data) {
        $publicKey = $data['publicKey'] ?? '';
        $privateKey = $data['privateKey'] ?? '';
        
        
        if ($privateKey) {
            $privateKey = openssl_pkey_get_private($privateKey);
            
            if (!$privateKey) {
                throw new \Exception('Error private key');
            }
            
            $this->privateKey = $privateKey;
        }
        
        if ($publicKey) {
            $publicKey = openssl_pkey_get_public($publicKey);
            
            if (!$publicKey) {
                throw new \Exception('Error public key');
            }
            
            $this->publicKey = $publicKey;
        }
    }

    public function getData(array $data) {
        $key = $data['key'] ?? '';
        $data = $data['data'] ?? '';
        if (!$data || !$key) {
            return null;
        }
        
        $key = base64_decode($key);
        
        if ($key===false) {
            return null;
        }
        
        if (openssl_private_decrypt($key, $aesPass, $this->privateKey)) {
            $data = $this->decodeAES256($data, $aesPass);
            $data = json_decode($data, true);
            return $data;
        }
        
        return null;
    }

    public function secureData(array $data) {
        $infoKey = openssl_pkey_get_details($this->publicKey);
        $key = random_bytes($infoKey['bits']/8-11);
        $data = $this->encryptAES256(json_encode($data), $key);
        
        if ($data===false) {
            return null;
        }
        
        if (openssl_public_encrypt($key, $encrypted, $this->publicKey)) {
            $encrypted = base64_encode($encrypted);
            return ['key' => $encrypted, 'data' => $data];
        }
        
        return null;
    }
    
    private function decodeAES256($str, $key) {
        $iv = substr($str, 0, $this->ivLength*2);
        $iv = hex2bin($iv);
        $str = substr($str, $this->ivLength*2);
        $data = openssl_decrypt($str, $this->cipher, $key, 0, $iv);
        return $data;
    }
    
    private function encryptAES256($str, $key) {
        $iv = random_bytes($this->ivLength);
        $encrypted = openssl_encrypt($str, $this->cipher, $key, 0, $iv);
        $encrypted = bin2hex($iv).$encrypted;
        return $encrypted;
    }
}