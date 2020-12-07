### Generate RSA keys
```bash
openssl genrsa -out request-private.key -rand /dev/urandom 2048
openssl rsa -in request-private.key -pubout -out request-public.key

openssl genrsa -out server-private.key -rand /dev/urandom 2048
openssl rsa -in server-private.key -pubout -out server-public.key
```

### request.php
```php
<?php
$url = 'http://example.com';
$request = new Tasofen\SecureTransfer\HTTPTransfer($url);

// Add Sign layer
$signKey = '432ec5842379093e7e9e9e3fb050f0b119cb55358f5376296a238743cd126f7d';
$checkKey = 'c7e43587372b9067146b73e16752e69d52287d3c99856fbb5dce25c5a4dd83d8';
$config = [
    'signKey' => $signKey,
    'checkKey' => $checkKey,
];
$signLayer = new Tasofen\SecureTransfer\Sign($config);
$request->addSecureLayer($signLayer);

// Add AES layer
$encryptionKey = '55a32b50003753a5b79c0304c9ad654329275c3fdf0ba30c8e1e0245da26bedd';
$AESLayer = new \Tasofen\SecureTransfer\AES([
    'key' => $encryptionKey,
]);
$request->addSecureLayer($AESLayer);

// Add RSA layer
$publicKey = file_get_contents(__DIR__.'/server-public.key'); //encode
$privateKey = file_get_contents(__DIR__.'/request-private.key'); // decode
$RSALayer = new \Tasofen\SecureTransfer\RSA([
    'publicKey' => $publicKey,
    'privateKey' => $privateKey,
]);
$request->addSecureLayer($RSALayer);

$response = $request->send(['msg' => 'request message']);
var_dump($response);
/*
Array
(
    [msg] => response message
)
*/
```

### server.php
```php
<?php
$url = 'example.com';
$server = new Tasofen\SecureTransfer\HTTPTransfer($url);

// Add Sign layer
$signKey = 'c7e43587372b9067146b73e16752e69d52287d3c99856fbb5dce25c5a4dd83d8';
$checkKey = '432ec5842379093e7e9e9e3fb050f0b119cb55358f5376296a238743cd126f7d';
$config = [
    'signKey' => $signKey,
    'checkKey' => $checkKey,
];
$signLayer = new Tasofen\SecureTransfer\Sign($config);
$server->addSecureLayer($signLayer);

// Add AES layer
$encryptionKey = '55a32b50003753a5b79c0304c9ad654329275c3fdf0ba30c8e1e0245da26bedd';
$AESLayer = new \Tasofen\SecureTransfer\AES([
    'key' => $encryptionKey,
]);
$server->addSecureLayer($AESLayer);

// Add RSA layer
$publicKey = file_get_contents(__DIR__.'/request-public.key'); //encode
$privateKey = file_get_contents(__DIR__.'/server-private.key'); // decode
$RSALayer = new \Tasofen\SecureTransfer\RSA([
    'publicKey' => $publicKey,
    'privateKey' => $privateKey,
]);
$server->addSecureLayer($RSALayer);

// Get request data
$data = $server->getData($_POST);
/*
print_r($data);
Array
(
    [msg] => request message
)
*/

$responseData = $server->secureData(['msg' => 'response message']);
echo json_encode($responseData);
```