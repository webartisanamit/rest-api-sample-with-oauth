<?php

require_once '../vendor/autoload.php';

if(empty(session_id())) session_start();

$lib = new \Library\lib(array(
    'client_id' => 'XX',
    'client_secret' => 'XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX',
    'redirectUri' => 'http://example.com',
));

$randNo = rand('10', '99');
$attributes = [
    "name" => "TestDemo ".$randNo,
    "email" => "test_demo".$randNo."@example.com",
];

$fileAttr = [ 'id' => 1, 'file' => 'https://d36fgo9tveb5fn.cloudfront.net/wp-content/uploads/2016/02/ff_kontakt-2-1024x302.jpg'];

$accessToken = isset($_SESSION['token']) ? unserialize($_SESSION['token'])->getAccessToken() : '';

$result = [];

if (!empty($accessToken)) {
    $lib->setToken(unserialize($_SESSION['token']));
} elseif (!$lib->getToken()) {
    $lib->requestAccessToken();
}

if ($lib->getToken()) {
    try {
        $result = $lib->customers()->all();
    } catch (\Library\TokenExpiredException $e) {
        $lib->refreshAccessToken();
        $result = $lib->customers()->all();
    }
    $_SESSION['token'] = serialize($lib->getToken());
}

echo "<pre>"; print_r($result); die;
