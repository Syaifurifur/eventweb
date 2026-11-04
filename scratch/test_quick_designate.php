<?php

$db = new mysqli('127.0.0.1', 'root', '', 'eventweb');
// Find an active non-accredited event
$res = $db->query("SELECT id, code, title FROM events WHERE deleted_at IS NULL AND id NOT IN (SELECT event_id FROM accreditation_documents) LIMIT 1");
$targetEvent = $res->fetch_assoc();
if (!$targetEvent) {
    echo "No unaccredited event found.\n";
    exit;
}

$evId = (int)$targetEvent['id'];
echo "Testing quick accreditation designate on Event ID $evId: {$targetEvent['code']} - {$targetEvent['title']}\n";

$cookie = __DIR__ . '/cookie_quick_test.txt';
if (file_exists($cookie)) unlink($cookie);

// Login to CI4
$ch = curl_init("http://127.0.0.1:8080/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
$html = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_test_name" value="([^"]+)"/', $html, $m);
$csrf = $m[1] ?? '';

$ch = curl_init("http://127.0.0.1:8080/login");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['identity' => 'admin@eventhub.test', 'password' => 'password', 'csrf_test_name' => $csrf]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
curl_close($ch);

// Fetch /documents to get fresh CSRF
$ch = curl_init("http://127.0.0.1:8080/documents");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
$docPage = curl_exec($ch);
curl_close($ch);

preg_match('/name="csrf_test_name" value="([^"]+)"/', $docPage, $m2);
$csrf2 = $m2[1] ?? '';

// Post quick designate
$postFields = [
    'csrf_test_name' => $csrf2,
    'event_id' => $evId,
    'accreditation_standard' => 'Kriteria 8 - Pengabdian kepada Masyarakat (PkM)',
    'doc_type' => 'all',
    'title' => 'Bukti PkM Event ' . $targetEvent['code'],
    'admin_notes' => 'Tolong dilengkapi bukti presensi dan dokumentasi PkM.',
];

$ch = curl_init("http://127.0.0.1:8080/documents/accreditation/designate");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Designate HTTP Code: $httpCode\n";

$check = $db->query("SELECT id, event_id, title, status FROM accreditation_documents WHERE event_id = $evId");
$row = $check->fetch_assoc();
if ($row) {
    echo "SUCCESS: Accreditation document created with ID {$row['id']}, Title: {$row['title']}, Status: {$row['status']}\n";
} else {
    echo "FAILED: Record not found in database.\n";
}

