<?php

namespace App\Libraries;

use App\Models\SystemSettingModel;

class GoogleDriveUploadService
{
    public static function isConfigured(): bool
    {
        $url = trim((string) SystemSettingModel::getSetting('gdrive_webhook_url', ''));
        return $url !== '' && filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function isAutoUploadEnabled(): bool
    {
        return SystemSettingModel::getSetting('gdrive_auto_upload_enabled', '1') === '1';
    }

    public static function getSettings(): array
    {
        $folderUrl = SystemSettingModel::getSetting('gdrive_mou_folder_url', 'https://drive.google.com/drive/folders/1MOU-BJS-SEKOLAH-BSI-GROUP');
        $folderId = self::extractFolderId($folderUrl);
        return [
            'admin_email' => SystemSettingModel::getSetting('gdrive_admin_email', 'admin.markom@bsi.ac.id'),
            'folder_url' => $folderUrl,
            'folder_name' => SystemSettingModel::getSetting('gdrive_mou_folder_name', 'MOU BJS Sekolah'),
            'webhook_url' => SystemSettingModel::getSetting('gdrive_webhook_url', ''),
            'auto_upload_enabled' => self::isAutoUploadEnabled(),
            'is_configured' => self::isConfigured(),
            'folder_id' => $folderId,
            'apps_script_template' => self::getAppsScriptTemplate($folderId),
        ];
    }

    public static function extractFolderId(?string $urlOrId): string
    {
        $raw = trim((string) $urlOrId);
        if ($raw === '') {
            return '';
        }

        // Patterns like:
        // https://drive.google.com/drive/folders/1abcXYZ_123?usp=sharing
        // https://drive.google.com/drive/u/0/folders/1abcXYZ_123
        if (preg_match('#/folders/([a-zA-Z0-9_-]+)#', $raw, $matches)) {
            return $matches[1];
        }

        // Direct ID
        if (preg_match('/^[a-zA-Z0-9_-]{15,}$/', $raw)) {
            return $raw;
        }

        return $raw;
    }

    /**
     * Uploads local file to Google Drive via Apps Script Webhook.
     * 
     * @return array [ 'success' => bool, 'drive_url' => ?string, 'file_id' => ?string, 'message' => string ]
     */
    public static function uploadFile(string $fullPath, string $targetFileName, ?string $targetFolderId = null): array
    {
        if (! is_file($fullPath)) {
            return [
                'success' => false,
                'drive_url' => null,
                'file_id' => null,
                'message' => 'File lokal tidak ditemukan pada path: ' . $fullPath,
            ];
        }

        $webhookUrl = trim((string) SystemSettingModel::getSetting('gdrive_webhook_url', ''));
        if ($webhookUrl === '' || filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
            return [
                'success' => false,
                'drive_url' => null,
                'file_id' => null,
                'message' => 'URL Webhook Google Apps Script belum dikonfigurasi.',
            ];
        }

        $configuredFolderUrl = SystemSettingModel::getSetting('gdrive_mou_folder_url', '');
        $folderId = self::extractFolderId($targetFolderId ?: $configuredFolderUrl);

        $fileContent = file_get_contents($fullPath);
        if ($fileContent === false) {
            return [
                'success' => false,
                'drive_url' => null,
                'file_id' => null,
                'message' => 'Gagal membaca konten file lokal.',
            ];
        }

        $payload = [
            'file_name' => $targetFileName,
            'mime_type' => 'application/pdf',
            'base64_data' => base64_encode($fileContent),
            'folder_id' => $folderId,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $webhookUrl,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true, // Essential for Google Apps Script 302 redirects!
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 40,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            return [
                'success' => false,
                'drive_url' => null,
                'file_id' => null,
                'message' => 'Koneksi ke Google Drive Webhook gagal: ' . $curlError,
            ];
        }

        $decoded = json_decode((string) $response, true);
        if (! is_array($decoded)) {
            return [
                'success' => false,
                'drive_url' => null,
                'file_id' => null,
                'message' => 'Respon Google Drive tidak valid (HTTP ' . $httpCode . '): ' . substr(strip_tags((string) $response), 0, 150),
            ];
        }

        if (($decoded['status'] ?? '') === 'success' || ! empty($decoded['file_url'])) {
            return [
                'success' => true,
                'drive_url' => $decoded['file_url'] ?? ($decoded['webViewLink'] ?? null),
                'file_id' => $decoded['file_id'] ?? ($decoded['id'] ?? null),
                'message' => 'Dokumen berhasil diunggah langsung ke Google Drive Admin.',
            ];
        }

        $errMsg = $decoded['message'] ?? ($decoded['error'] ?? 'Terjadi kesalahan saat memproses file di Google Drive.');
        return [
            'success' => false,
            'drive_url' => null,
            'file_id' => null,
            'message' => $errMsg,
        ];
    }

    /**
     * Tests the webhook connectivity.
     */
    public static function testConnection(): array
    {
        $webhookUrl = trim((string) SystemSettingModel::getSetting('gdrive_webhook_url', ''));
        if ($webhookUrl === '' || filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
            return [
                'success' => false,
                'message' => 'URL Webhook Google Apps Script belum diisi atau tidak valid.',
            ];
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $webhookUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Webhook: ' . $curlError,
            ];
        }

        $decoded = json_decode((string) $response, true);
        if (is_array($decoded) && (($decoded['status'] ?? '') === 'ok' || ($decoded['status'] ?? '') === 'success')) {
            return [
                'success' => true,
                'message' => 'Koneksi ke Webhook Google Drive BERHASIL aktif! (HTTP ' . $httpCode . ')',
            ];
        }

        return [
            'success' => true,
            'message' => 'Webhook merespons (HTTP ' . $httpCode . '). Siap menerima unggahan dokumen MOU.',
        ];
    }

    /**
     * Ready-to-use Google Apps Script source code template for admin.
     */
    public static function getAppsScriptTemplate(?string $defaultFolderId = null): string
    {
        $folderId = $defaultFolderId ?: 'GANTI_DENGAN_ID_FOLDER_GOOGLE_DRIVE_ANDA';
        return <<<JAVASCRIPT
/**
 * Webhook Google Apps Script untuk Upload Dokumen MOU EventHub ke Google Drive Admin
 * 
 * LANGKAH DEPLOYMENT WAJIB (MENCEGAH "Akses ditolak: DriveApp"):
 * 1. Buka https://script.google.com/ lalu klik "New project"
 * 2. Hapus semua kode bawaan dan tempel SELURUH kode ini.
 * 3. PENTING: Pada menu dropdown fungsi di bagian atas, pilih "setupOtorisasi", 
 *    lalu klik tombol "Run" (Jalankan).
 *    -> Klik "Review permissions" / "Tinjau izin"
 *    -> Pilih Akun Google Anda
 *    -> Klik "Advanced" / "Lanjutan" -> Klik "Go to ... (unsafe)"
 *    -> Klik "Allow" / "Izinkan".
 * 4. Setelah sukses di-Run, klik tombol "Deploy" (kanan atas) -> "New deployment"
 *    (Jika sudah pernah deploy, pilih "Manage deployments" -> ikon Pensil -> Version: "New version").
 * 5. Pilih tipe: "Web app"
 * 6. Set "Execute as": "Me (<email-admin>)" / "Saya" (JANGAN pilih User accessing)
 * 7. Set "Who has access": "Anyone" / "Siapa saja"
 * 8. Klik "Deploy" dan salin "Web app URL" ke pengaturan EventHub.
 */

// Jalankan fungsi ini sekali di editor Apps Script untuk memicu izin akses Google Drive
function setupOtorisasi() {
  Logger.log("Mengecek izin akses DriveApp...");
  var root = DriveApp.getRootFolder();
  Logger.log("Izin DriveApp SUKSES diberikan untuk: " + root.getName());
  
  var targetFolderId = "{$folderId}";
  if (targetFolderId && targetFolderId !== "GANTI_DENGAN_ID_FOLDER_GOOGLE_DRIVE_ANDA") {
    try {
      var folder = DriveApp.getFolderById(targetFolderId);
      Logger.log("Folder target valid: " + folder.getName());
    } catch (e) {
      Logger.log("Catatan: ID Folder tidak ditemukan, akan fallback ke folder utama / auto-create.");
    }
  }
  return "Izin DriveApp siap dan aktif!";
}

function doPost(e) {
  try {
    if (!e || !e.postData || !e.postData.contents) {
      return ContentService.createTextOutput(JSON.stringify({
        status: "error",
        message: "Tidak ada payload data yang diterima."
      })).setMimeType(ContentService.MimeType.JSON);
    }

    var data = JSON.parse(e.postData.contents);
    var targetFolderId = data.folder_id || "{$folderId}";
    var folder;

    // Cari folder target atau buat folder fallback otomatis jika ID tidak ditemukan
    try {
      if (targetFolderId && targetFolderId !== "GANTI_DENGAN_ID_FOLDER_GOOGLE_DRIVE_ANDA") {
        folder = DriveApp.getFolderById(targetFolderId);
      } else {
        folder = getOrCreateFolder("MOU BJS Sekolah");
      }
    } catch (folderErr) {
      folder = getOrCreateFolder("MOU BJS Sekolah");
    }
    
    var decoded = Utilities.base64Decode(data.base64_data);
    var blob = Utilities.newBlob(decoded, data.mime_type || "application/pdf", data.file_name);
    var file = folder.createFile(blob);
    
    // Set izin lihat dengan proteksi error (jika kebijakan organisasi Workspace membatasi link share)
    try {
      file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    } catch (shareErr) {
      // Kebijakan domain workspace membatasi ANYONE_WITH_LINK, fallback ke DOMAIN_WITH_LINK
      try {
        file.setSharing(DriveApp.Access.DOMAIN_WITH_LINK, DriveApp.Permission.VIEW);
      } catch (domainErr) {
        // Abaikan jika tidak diizinkan oleh admin organisasi, file tetap berhasil tersimpan
      }
    }
    
    var result = {
      status: "success",
      file_id: file.getId(),
      file_url: file.getUrl(),
      download_url: file.getDownloadUrl(),
      name: file.getName(),
      size: file.getSize()
    };
    
    return ContentService.createTextOutput(JSON.stringify(result))
      .setMimeType(ContentService.MimeType.JSON);
  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      status: "error",
      message: error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet(e) {
  return ContentService.createTextOutput(JSON.stringify({
    status: "ok",
    message: "Google Drive Upload Webhook EventHub aktif dan siap menerima data!"
  })).setMimeType(ContentService.MimeType.JSON);
}

function getOrCreateFolder(folderName) {
  var folders = DriveApp.getFoldersByName(folderName);
  if (folders.hasNext()) {
    return folders.next();
  }
  return DriveApp.createFolder(folderName);
}
JAVASCRIPT;
    }
}
