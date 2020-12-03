<?php

namespace Tasofen\SecureTransfer;

class Sign implements Secure
{
    private $signKey;
    private $checkKey;
    
    private $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    
    public function __construct(array $data) {
        $this->signKey = $data['signKey'] ?? '';
        $this->checkKey = $data['checkKey'] ?? '';
        
        if (!$this->signKey || !$this->checkKey) {
            throw new \Exception('Error. signKey or checkKey is empty!');
        }
    }

    public function getData(array $data) {
        $inputData = $data['data'] ?? '';
        $inputHash = $data['hash'] ?? '';
        $inputSign = $data['sign'] ?? 'fail';
        $sign = hash('sha512', $inputData . $this->checkKey . $inputHash);
        
        if ($sign === $inputSign) {
            $inputData = json_decode($inputData, true);
            return $inputData;
        }
        
        return null;
    }

    public function secureData(array $data) {
        $data = [
            'data' => json_encode($data),
            'hash' => $this->getRand(),
        ];
        
        $data['sign'] = hash('sha512', $data['data'] . $this->signKey . $data['hash']);
        return $data;
    }
    
    private function getRand() {
        $this->chars = str_shuffle($this->chars);
        $rand = $this->chars . microtime();
        $rand .= bin2hex(random_bytes( rand(64, 100) ));
        $rand = hash('sha512', $rand);
        return $rand;
    }
}