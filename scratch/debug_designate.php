<?php
$cookie = __DIR__ . '/test_cookie.txt';
if (file_exists($cookie)) unlink($cookie);

// 1. Login to 8080
$ch = curl_init('http://127.0.0.1:8080/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
$html = curl_exec($ch);
preg_match('/name="csrf_test_name" value="([^"]+)"/', $html, $m);
$token = $m[1] ?? '';

curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8080/login');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'identity' => 'admin@eventhub.test',
    'password' => 'password',
    'csrf_test_name' => $token
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);

// 2. Fetch /documents/accreditation
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8080/documents/accreditation');
curl_setopt($ch, CURLOPT_POST, false);
$page = curl_exec($ch);
preg_match('/name="csrf_test_name" value="([^"]+)"/', $page, $m2);
$token2 = $m2[1] ?? '';

echo "CSRF token on page: $token2\n";

// 3. Post designate
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8080/documents/accreditation/designate');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'csrf_test_name' => $token2,
    'event_id' => 1,
    'accreditation_standard' => 'Kriteria 9',
    'title' => 'Test Doc',
    'doc_type' => 'all',
    'admin_notes' => 'Note 123'
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
$postResp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$loc = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
echo "HTTP Code: $code, Redirect: $loc\n";
if ($code != 302) {
    echo "Body:\n" . $postResp . "\n";
} else {
    // Follow redirect to see flash messages
    curl_setopt($ch, CURLOPT_URL, $loc);
    curl_setopt($ch, CURLOPT_POST, false);
    $redirPage = curl_exec($ch);
    if (preg_match('/class="[^"]*(bg-emerald|bg-rose)[^"]*"[^>]*>.*?<\/div>/s', $redirPage, $flash)) {
        echo "Flash: " . strip_tags($flash[0]) . "\n";
    }
}

