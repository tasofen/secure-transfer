<?php

namespace Tasofen\SecureTransfer;

abstract class BaseTransfer
{
    private $layers = [];
    
    public function addSecureLayer(Secure $layer) {
        $this->layers[] = $layer;
    }
    
    protected function secureData(array $data) {
        foreach($this->layers as $layer) {
            $data = $layer->secureData($data);
        }
        return $data;
    }
    
    public function getData($data) {
        $layers = $this->layers;
        $layers = array_reverse($layers);
        
        foreach($layers as $key => $layer) {
            $data = $layer->getData($data);
            if (!$data) {
                return null;
            }
        }
        return $data;
    }

    abstract public function send(array $data);
}