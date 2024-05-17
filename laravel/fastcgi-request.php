<?php
declare(strict_types=1);

function buildPacket($type, $requestId, $content) {
    $length = strlen($content);
    return pack('CCnnCx', 1, $type, $requestId, $length, 0) . $content;
}

function buildNameValuePair($name, $value) {
    $nameLength = strlen($name);
    $valueLength = strlen($value);
    return chr($nameLength) . chr($valueLength) . $name . $value;
}

// 接続情報
$host = 'localhost';
$port = 9000;

// ソケット作成と接続
$sock = fsockopen($host, $port, $errno, $errstr, 30);
if (!$sock) {
    die("Connection failed: $errstr ($errno)\n");
}

// FCGI_BEGIN_REQUEST パケット作成と送信
$beginRequest = buildPacket(1, 1, pack('nCx5', 1, 0));
fwrite($sock, $beginRequest);

// 環境変数を設定
$params = [
    'SCRIPT_FILENAME' => '/var/www/html/public/index.php',
    'REQUEST_METHOD' => 'GET',
    'CONTENT_TYPE' => '',
    'CONTENT_LENGTH' => '0',
    'QUERY_STRING' => '',
    'GATEWAY_INTERFACE' => 'CGI/1.1',
    'SERVER_SOFTWARE' => 'php/fcgiclient',
    'REMOTE_ADDR' => '127.0.0.1',
    'REMOTE_PORT' => '12345',
    'SERVER_ADDR' => '127.0.0.1',
    'SERVER_PORT' => '80',
    'SERVER_NAME' => 'localhost',
    'SERVER_PROTOCOL' => 'HTTP/1.1',
];

// 環境変数を送信
foreach ($params as $name => $value) {
    $paramData = buildNameValuePair($name, $value);
    fwrite($sock, buildPacket(4, 1, $paramData));
}

// FCGI_PARAMS の終わり
fwrite($sock, buildPacket(4, 1, ''));

// FCGI_STDIN の終わり（GETリクエストのため空）
fwrite($sock, buildPacket(5, 1, ''));
fwrite($sock, buildPacket(5, 1, ''));

// レスポンスの受信
$response = '';
while (!feof($sock)) {
    $response .= fgets($sock, 128);
}

// ソケットを閉じる
fclose($sock);

// レスポンスの表示
echo $response;
