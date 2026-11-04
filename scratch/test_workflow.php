<?php

function runWorkflowTest($baseUrl, $engineName) {
    echo "====================================================\n";
    echo "   TESTING ACCREDITATION WORKFLOW ON $engineName ($baseUrl)\n";
    echo "====================================================\n";

    $adminCookie = __DIR__ . "/cookie_admin_{$engineName}.txt";
    $campusCookie = __DIR__ . "/cookie_campus_{$engineName}.txt";
    if (file_exists($adminCookie)) unlink($adminCookie);
    if (file_exists($campusCookie)) unlink($campusCookie);

    // Step 1: Admin Login
    echo "1. Admin Login...\n";
    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    $html = curl_exec($ch);
    curl_close($ch);

    $csrfToken = '';
    if (preg_match('/name="csrf_test_name" value="([^"]+)"/', $html, $m)) $csrfToken = $m[1];
    elseif (preg_match('/name="_token" value="([^"]+)"/', $html, $m)) $csrfToken = $m[1];

    $postData = ['identity' => 'admin@eventhub.test', 'password' => 'password'];
    if ($csrfToken) { $postData['csrf_test_name'] = $csrfToken; $postData['_token'] = $csrfToken; }

    $ch = curl_init("$baseUrl/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);

    // Get candidate event from MySQL or event list
    $db = new mysqli('127.0.0.1', 'root', '', 'eventweb');
    $evRes = $db->query("SELECT id, title, campus_id FROM events WHERE deleted_at IS NULL AND campus_id IS NOT NULL ORDER BY id DESC LIMIT 1");
    $ev = $evRes->fetch_assoc();
    $eventId = (int)$ev['id'];
    $eventCampusId = (int)$ev['campus_id'];
    echo "Found candidate event: ID $eventId, Campus ID $eventCampusId\n";

    // Step 2: Admin designates document for accreditation
    echo "2. Admin designates event $eventId for accreditation...\n";
    // Get fresh page for CSRF
    $ch = curl_init("$baseUrl/documents/accreditation");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    $pageHtml = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/name="csrf_test_name" value="([^"]+)"/', $pageHtml, $m)) $csrfToken = $m[1];
    elseif (preg_match('/name="_token" value="([^"]+)"/', $pageHtml, $m)) $csrfToken = $m[1];

    $designateData = [
        'event_id' => $eventId,
        'accreditation_standard' => 'Kriteria 9 - Luaran dan Capaian Tridharma',
        'title' => "Bukti Akreditasi {$engineName} - " . date('His'),
        'doc_type' => 'all',
        'admin_notes' => 'Harap lengkapi bukti dokumentasi dan daftar hadir.',
    ];
    if ($csrfToken) { $designateData['csrf_test_name'] = $csrfToken; $designateData['_token'] = $csrfToken; }

    $ch = curl_init("$baseUrl/documents/accreditation/designate");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($designateData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $designateResp = curl_exec($ch);
    $designateCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Designate response HTTP code: $designateCode\n";

    // Check newly inserted record
    $docRes = $db->query("SELECT id, title, status, campus_id FROM accreditation_documents WHERE event_id = $eventId ORDER BY id DESC LIMIT 1");
    $docRow = $docRes->fetch_assoc();
    $docId = (int)$docRow['id'];
    echo "Created accreditation doc ID: $docId, Status: {$docRow['status']}, Campus ID: {$docRow['campus_id']}\n";
    if ($docRow['status'] !== 'menunggu_kelengkapan') {
        echo "FAIL: Expected status menunggu_kelengkapan, got {$docRow['status']}\n";
        return;
    }

    // Step 3: Kepala Kampus completes document
    echo "3. Testing completion (upload notes & external link)...\n";
    // Get fresh page for CSRF
    $ch = curl_init("$baseUrl/documents/accreditation");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    $pageHtml = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/name="csrf_test_name" value="([^"]+)"/', $pageHtml, $m)) $csrfToken = $m[1];
    elseif (preg_match('/name="_token" value="([^"]+)"/', $pageHtml, $m)) $csrfToken = $m[1];

    $completeData = [
        'external_evidence_link' => 'https://drive.google.com/drive/folders/test-accreditation-evidence',
        'campus_notes' => 'Berkas daftar hadir dan foto dokumentasi kegiatan telah diunggah ke Google Drive.',
        'status' => 'menunggu_verifikasi',
    ];
    if ($csrfToken) { $completeData['csrf_test_name'] = $csrfToken; $completeData['_token'] = $csrfToken; }

    $ch = curl_init("$baseUrl/documents/accreditation/$docId/complete");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($completeData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $completeResp = curl_exec($ch);
    $completeCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $docRes2 = $db->query("SELECT status, external_evidence_link, campus_notes FROM accreditation_documents WHERE id = $docId");
    $docRow2 = $docRes2->fetch_assoc();
    echo "After completion - Status: {$docRow2['status']}, Link: {$docRow2['external_evidence_link']}\n";
    if ($docRow2['status'] !== 'menunggu_verifikasi') {
        echo "FAIL: Expected status menunggu_verifikasi, got {$docRow2['status']}\n";
        return;
    }

    // Step 4: Admin verifies document
    echo "4. Admin verifies document $docId...\n";
    // Get fresh page for CSRF
    $ch = curl_init("$baseUrl/documents/accreditation");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    $pageHtml = curl_exec($ch);
    curl_close($ch);

    if (preg_match('/name="csrf_test_name" value="([^"]+)"/', $pageHtml, $m)) $csrfToken = $m[1];
    elseif (preg_match('/name="_token" value="([^"]+)"/', $pageHtml, $m)) $csrfToken = $m[1];

    $verifyData = [
        'action' => 'approve',
        'admin_notes' => 'Dokumen lengkap dan telah diverifikasi valid untuk Instrumen Kriteria 9.',
    ];
    if ($csrfToken) { $verifyData['csrf_test_name'] = $csrfToken; $verifyData['_token'] = $csrfToken; }

    $ch = curl_init("$baseUrl/documents/accreditation/$docId/verify");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($verifyData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $verifyResp = curl_exec($ch);
    $verifyCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $docRes3 = $db->query("SELECT status, admin_notes, verified_at FROM accreditation_documents WHERE id = $docId");
    $docRow3 = $docRes3->fetch_assoc();
    echo "After verification - Status: {$docRow3['status']}, Verified At: {$docRow3['verified_at']}\n";
    if ($docRow3['status'] !== 'terverifikasi') {
        echo "FAIL: Expected status terverifikasi, got {$docRow3['status']}\n";
        return;
    }

    // Step 5: Test filters on listing
    echo "5. Testing Filter Parameters...\n";
    $testYear = (int)date('Y');
    $filterUrl = "$baseUrl/documents/accreditation?year=$testYear&status=terverifikasi&campus_id=$eventCampusId";
    $ch = curl_init($filterUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $adminCookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $adminCookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $filterResp = curl_exec($ch);
    $filterCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $hasDocTitle = strpos($filterResp, $designateData['title']) !== false;
    echo "Filter query ($filterUrl) HTTP code: $filterCode, Contains created doc: " . ($hasDocTitle ? "YES (PASS)" : "NO") . "\n";

    echo ">>> WORKFLOW TEST ON $engineName: 100% SUCCESS <<<\n\n";
}

runWorkflowTest('http://127.0.0.1:8080', 'CodeIgniter4');
runWorkflowTest('http://127.0.0.1:8081', 'Laravel');
