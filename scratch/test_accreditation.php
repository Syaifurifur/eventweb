<?php
// Scratch test script to test /documents/accreditation on CI4 (8080) and Laravel (8081)

function testEngine($baseUrl, $engineName) {
    echo "=== Testing $engineName ($baseUrl) ===\n";
    $cookieFile = __DIR__ . "/cookie_{$engineName}.txt";
    if (file_exists($cookieFile)) unlink($cookieFile);

    // 1. Get login page to extract CSRF if needed
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    $html = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Extract CSRF token
    $csrfToken = '';
    if (preg_match('/name="csrf_test_name" value="([^"]+)"/', $html, $m)) {
        $csrfToken = $m[1];
    } elseif (preg_match('/name="_token" value="([^"]+)"/', $html, $m)) {
        $csrfToken = $m[1];
    }

    // 2. Post login
    $postFields = [
        'identity' => 'admin@eventhub.test',
        'password' => 'password',
    ];
    if ($csrfToken) {
        $postFields['csrf_test_name'] = $csrfToken;
        $postFields['_token'] = $csrfToken;
    }

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $loginResp = curl_exec($ch);
    $loginCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Login response HTTP code: $loginCode\n";

    // 3. Request /documents/accreditation
    $ch = curl_init("$baseUrl/documents/accreditation");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $docResp = curl_exec($ch);
    $docCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    echo "Accreditation docs HTTP code: $docCode, Final URL: $finalUrl\n";

    // Assertions
    $hasHeader = strpos($docResp, 'Dokumen Akreditasi Kampus') !== false;
    $hasButton = strpos($docResp, '+ Pilih Dokumen Event untuk Akreditasi') !== false;
    $hasTable = strpos($docResp, 'Daftar Dokumen Akreditasi') !== false;
    $hasFilter = strpos($docResp, 'Kampus Pelaksana:') !== false;

    echo "Check hasHeader: " . ($hasHeader ? "PASS" : "FAIL") . "\n";
    echo "Check hasButton: " . ($hasButton ? "PASS" : "FAIL") . "\n";
    echo "Check hasTable: " . ($hasTable ? "PASS" : "FAIL") . "\n";
    echo "Check hasFilter: " . ($hasFilter ? "PASS" : "FAIL") . "\n";

    if (!$hasHeader || !$hasButton || !$hasTable || !$hasFilter) {
        echo "Response snippet:\n" . substr($docResp, 0, 800) . "\n";
    }
}

testEngine('http://127.0.0.1:8080', 'ci4');
echo "\n";
testEngine('http://127.0.0.1:8081', 'laravel');

