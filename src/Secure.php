<?php

namespace Tasofen\SecureTransfer;

interface Secure
{
    public function __construct(array $data);
    public function getData(array $data);
    public function secureData(array $data);
}