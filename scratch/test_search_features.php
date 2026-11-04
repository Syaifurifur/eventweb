<?php

function testSearchFeature($baseUrl, $engineName) {
    echo "=== Testing $engineName ($baseUrl) ===\n";
    $cookie = __DIR__ . "/cookie_{$engineName}_search.txt";
    if (file_exists($cookie)) unlink($cookie);

    // 1. Login
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $html = curl_exec($ch);
    curl_close($ch);

    $csrf = '';
    if (preg_match('/name="csrf_test_name" value="([^"]+)"/', $html, $m)) $csrf = $m[1];
    elseif (preg_match('/name="_token" value="([^"]+)"/', $html, $m)) $csrf = $m[1];

    $post = ['identity' => 'admin@eventhub.test', 'password' => 'password'];
    if ($csrf) { $post['csrf_test_name'] = $csrf; $post['_token'] = $csrf; }

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);

    // 2. Test /documents/accreditation
    $ch = curl_init("$baseUrl/documents/accreditation");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $accredResp = curl_exec($ch);
    $accredCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "1. /documents/accreditation status: $accredCode\n";
    $hasSearchTitle = strpos($accredResp, 'Cari dari Dokumen Event untuk Akreditasi') !== false;
    $hasSearchInput = strpos($accredResp, 'candSearchInput') !== false;
    $hasResultsContainer = strpos($accredResp, 'candResultsContainer') !== false;
    $hasChooseBtn = strpos($accredResp, '⭐ Jadikan Data Akreditasi') !== false;
    echo "  - hasSearchTitle: " . ($hasSearchTitle ? 'PASS' : 'FAIL') . "\n";
    echo "  - hasSearchInput: " . ($hasSearchInput ? 'PASS' : 'FAIL') . "\n";
    echo "  - hasResultsContainer: " . ($hasResultsContainer ? 'PASS' : 'FAIL') . "\n";
    echo "  - hasChooseBtn in script: " . ($hasChooseBtn ? 'PASS' : 'FAIL') . "\n";

    // 3. Test /documents
    $ch = curl_init("$baseUrl/documents");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    $docResp = curl_exec($ch);
    $docCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "2. /documents status: $docCode\n";
    $hasAccredTh = strpos($docResp, '<th>Akreditasi</th>') !== false || strpos($docResp, '>Akreditasi<') !== false;
    $hasAccredBtn = strpos($docResp, 'Jadikan Akreditasi') !== false;
    $hasQuickDialog = strpos($docResp, 'id="dialog-quick-accredit"') !== false;
    echo "  - hasAccredTh: " . ($hasAccredTh ? 'PASS' : 'FAIL') . "\n";
    echo "  - hasAccredBtn: " . ($hasAccredBtn ? 'PASS' : 'FAIL') . "\n";
    echo "  - hasQuickDialog: " . ($hasQuickDialog ? 'PASS' : 'FAIL') . "\n";
}

testSearchFeature('http://127.0.0.1:8080', 'CodeIgniter4');
echo "\n";
testSearchFeature('http://127.0.0.1:8081', 'Laravel');

