<?php
$cookie = __DIR__ . '/test_cookie.txt';

$ch = curl_init('http://127.0.0.1:8080/documents/accreditation');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
$page = curl_exec($ch);

// Search for flash message or table content
if (preg_match('/class="[^"]*(bg-emerald|bg-rose)[^"]*"[^>]*>.*?<\/div>/s', $page, $m)) {
    echo "FLASH: " . strip_tags($m[0]) . "\n";
} else {
    echo "No flash found.\n";
}

if (preg_match('/Menampilkan <strong>(\d+)<\/strong>/', $page, $m2)) {
    echo "Items count shown in page: " . $m2[1] . "\n";
}

