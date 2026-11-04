<?php
$start = microtime(true);
$file = 'C:/Users/ASUS/Downloads/Data Induk Satuan Pendidikan - DAFTAR Nasional 360 - kemendikdasmen,kemenag - dikmen - formal - ASC - 30 Oktober 2026.xlsx';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$db = new mysqli('127.0.0.1', 'root', '', 'eventweb');
if ($db->connect_error) {
    die("DB connection failed: " . $db->connect_error);
}
$db->set_charset('utf8mb4');

echo "Opening Excel file...\n";
$zip = new ZipArchive();
if ($zip->open($file) !== true) {
    die("Failed to open zip archive.\n");
}

echo "Reading sharedStrings.xml...\n";
$reader = new XMLReader();
$reader->open('zip://' . realpath($file) . '#xl/sharedStrings.xml');

$sharedStrings = [];
$currText = '';
while ($reader->read()) {
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 't') {
        $currText .= $reader->readString();
    } elseif ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'si') {
        $currText = '';
    } elseif ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'si') {
        $sharedStrings[] = $currText;
    }
}
$reader->close();
echo "Loaded " . count($sharedStrings) . " shared strings (" . round(microtime(true) - $start, 2) . "s)\n";

// Truncate table first to avoid duplicate imports
$db->query("TRUNCATE TABLE `national_schools`");

echo "Streaming sheet1.xml and batch inserting into national_schools...\n";
$reader = new XMLReader();
$reader->open('zip://' . realpath($file) . '#xl/worksheets/sheet1.xml');

$batchSize = 1000;
$batch = [];
$totalInserted = 0;
$rowNum = 0;
$now = date('Y-m-d H:i:s');

$db->begin_transaction();

while ($reader->read()) {
    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'row') {
        $rowNum++;
        if ($rowNum <= 5) {
            continue; // Header rows
        }

        $rowXml = $reader->readOuterXml();
        $rowObj = simplexml_load_string($rowXml);
        $cells = [];

        foreach ($rowObj->c as $c) {
            $r = (string)$c['r'];
            $col = preg_replace('/[0-9]/', '', $r);
            $t = (string)$c['t'];
            $v = (string)$c->v;
            $val = $v;
            if ($t === 's' && isset($sharedStrings[(int)$v])) {
                $val = $sharedStrings[(int)$v];
            }
            $cells[$col] = trim($val);
        }

        $npsn = $cells['A'] ?? '';
        $name = $cells['B'] ?? '';
        if ($npsn === '' && $name === '') {
            continue;
        }

        $schoolType = $cells['C'] ?? 'SMA';
        $educationType = $cells['D'] ?? '';
        $status = $cells['E'] ?? 'NEGERI';
        $stage = $cells['F'] ?? '';
        $city = $cells['G'] ?? '';
        $district = $cells['H'] ?? '';
        $subdistrict = $cells['I'] ?? '';
        $address = $cells['J'] ?? '';
        $track = $cells['K'] ?? '';
        $ministry = $cells['L'] ?? '';

        $batch[] = sprintf(
            "(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, '%s', '%s')",
            "'" . $db->real_escape_string($npsn) . "'",
            "'" . $db->real_escape_string($name) . "'",
            "'" . $db->real_escape_string($schoolType) . "'",
            "'" . $db->real_escape_string($educationType) . "'",
            "'" . $db->real_escape_string($status) . "'",
            "'" . $db->real_escape_string($stage) . "'",
            "'" . $db->real_escape_string($city) . "'",
            "'" . $db->real_escape_string($district) . "'",
            "'" . $db->real_escape_string($subdistrict) . "'",
            "'" . $db->real_escape_string($address) . "'",
            "'" . $db->real_escape_string($track) . "'",
            "'" . $db->real_escape_string($ministry) . "'",
            $now,
            $now
        );

        if (count($batch) >= $batchSize) {
            $sql = "INSERT INTO `national_schools` (`npsn`, `name`, `school_type`, `education_type`, `school_status`, `stage`, `city`, `district`, `subdistrict`, `address`, `track`, `supervising_ministry`, `created_at`, `updated_at`) VALUES " . implode(",\n", $batch);
            if (!$db->query($sql)) {
                echo "Error inserting batch: " . $db->error . "\n";
            }
            $totalInserted += count($batch);
            $batch = [];
            echo "Inserted $totalInserted rows...\n";
        }
    }
}

if (!empty($batch)) {
    $sql = "INSERT INTO `national_schools` (`npsn`, `name`, `school_type`, `education_type`, `school_status`, `stage`, `city`, `district`, `subdistrict`, `address`, `track`, `supervising_ministry`, `created_at`, `updated_at`) VALUES " . implode(",\n", $batch);
    $db->query($sql);
    $totalInserted += count($batch);
}

$db->commit();
$reader->close();
$zip->close();

$time = round(microtime(true) - $start, 2);
echo "DONE! Successfully imported $totalInserted national schools in $time seconds.\n";

