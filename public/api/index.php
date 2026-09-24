<?php
// API Reverse Proxy to FastAPI Backend on port 8028
$backend_host = 'http://127.0.0.1:8028';
$request_uri = $_SERVER['REQUEST_URI'];
$url = $backend_host . $request_uri;

$method = $_SERVER['REQUEST_METHOD'];
$headers = getallheaders();

$curl_headers = [];
foreach ($headers as $k => $v) {
    if (strtolower($k) !== 'host' && strtolower($k) !== 'content-length') {
        $curl_headers[] = "$k: $v";
    }
}

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
curl_setopt($ch, CURLOPT_HTTPHEADER, $curl_headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

if ($method !== 'GET' && $method !== 'HEAD') {
    $input = file_get_contents('php://input');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
}

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Backend FastAPI service unavailable',
        'detail' => curl_error($ch)
    ]);
    curl_close($ch);
    exit;
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
curl_close($ch);

$header_text = substr($response, 0, $header_size);
$body = substr($response, $header_size);

http_response_code($http_code);
foreach (explode("\r\n", $header_text) as $h) {
    if (!empty($h) && !preg_match('/^Transfer-Encoding:/i', $h) && !preg_match('/^HTTP\//i', $h)) {
        header($h, false);
    }
}

echo $body;
