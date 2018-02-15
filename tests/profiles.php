<?php

require_once '../vendor/autoload.php';

if(empty(session_id())) session_start();

$lib = new \Library\lib(array(
    'client_id' => 'XX',
    'client_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
    'redirectUri' => 'http://example.com',
));

$attributes = [
    'first_name' => 'super',
    'last_name' => 'admin',
    'email' => 'superadmin@gmail.com',
    'address' => 'st logan',
    'city' => 'chd',
    'telephone_number' => '1564654211',
];

$fileAttr = ['id' => 1, 'file' => 'https://d36fgo9tveb5fn.cloudfront.net/wp-content/uploads/2016/02/ff_kontakt-2-1024x302.jpg'];

$accessToken = isset($_SESSION['token']) ? unserialize($_SESSION['token'])->getAccessToken() : '';

$result = [];

if (!empty($accessToken)) {
    $lib->setToken(unserialize($_SESSION['token']));
} elseif (!$lib->getToken()) {
    $lib->requestAccessToken();
}

if ($lib->getToken()) {
    try {
        $result = $lib->profiles()->find(1);
    } catch (\Library\TokenExpiredException $e) {
        $lib->refreshAccessToken();
        $result = $lib->profiles()->find(1);
    }
    $_SESSION['token'] = serialize($lib->getToken());
}

echo "<pre>"; print_r($result); die;
