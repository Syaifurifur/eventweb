# EventHub Laravel

Implementasi Laravel 12 + Tailwind CSS 4 untuk seluruh fitur EventHub. Aplikasi ini memakai database MySQL `eventweb` yang sama dengan aplikasi CodeIgniter sebelumnya, sehingga data existing tidak perlu dipindah atau di-seed ulang.

## Menjalankan aplikasi

Pastikan MySQL XAMPP aktif, lalu dari root repository jalankan:

```powershell
.\serve-laravel.ps1
```

Aplikasi tersedia di `http://127.0.0.1:8081/login`.

Untuk membangun ulang Tailwind/Vite:

```powershell
cd laravel
npm install
npm run build
```

## Struktur migrasi

- `app/Controllers`: seluruh controller autentikasi, EventHub, CRM, dan Google Calendar.
- `app/Models`: model untuk tabel existing tanpa perubahan struktur data.
- `app/Libraries`: integrasi Gemini, Google Calendar, CRM bridge, dan ekspor kalender.
- `app/Http/Middleware`: autentikasi, role, permission, dan autentikasi CRM Laravel.
- `app/Support`: adapter query/request/response untuk menjaga perilaku logika bisnis saat runtime dipindah ke Laravel.
- `resources/views`: seluruh halaman dijalankan sebagai Blade.
- `resources/css/app.css`: entry point Tailwind CSS 4.
- `database/schema/eventweb-schema.sql`: snapshot schema MySQL tanpa data.

Laravel menggunakan folder `../writable` lama untuk template, upload, cache dokumen, dan dokumen hasil generate agar file existing tetap tersedia. Jangan menjalankan `migrate:fresh` terhadap database produksi.

## Pemeriksaan

```powershell
php artisan route:list --except-vendor
php artisan test
npm run build
```
