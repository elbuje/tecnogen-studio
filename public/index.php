<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 1. API Reverse Proxy to FastAPI backend on port 8028
if (strpos($uri, '/api') === 0) {
    $backend_host = 'http://127.0.0.1:8028';
    $url = $backend_host . $_SERVER['REQUEST_URI'];
    
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
        echo json_encode(['error' => 'FastAPI backend is starting up...', 'detail' => curl_error($ch)]);
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
    exit;
}

// 2. Static files check
$file = __DIR__ . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    $mime = mime_content_type($file);
    if (preg_match('/\.css$/i', $file)) $mime = 'text/css';
    if (preg_match('/\.js$/i', $file)) $mime = 'application/javascript';
    if (preg_match('/\.svg$/i', $file)) $mime = 'image/svg+xml';
    header('Content-Type: ' . $mime);
    readfile($file);
    exit;
}

// 3. SPA Fallback: Serve React index.html
$spa_index = __DIR__ . '/index.html';
if (file_exists($spa_index)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($spa_index);
    exit;
}

echo "TecnoGen Studio is compiling assets. Please refresh in a moment.";
