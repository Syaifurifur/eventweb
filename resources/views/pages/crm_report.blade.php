<?php
$canViewAllCrmCampuses = (bool) ($canViewAllCrmCampuses ?? false);
$userCampusFullName = strtoupper(trim((string) ($userCampusFullName ?? 'UBSI KAMPUS YOGYAKARTA')));
$requestedCampusParam = strtoupper(trim((string) ($requestedCampusParam ?? '')));

$allCrmCampusesJson = <<<'JSON_CAMPUSES'
[{"no": 1, "kampus": "UBSI KAMPUS YOGYAKARTA", "group": "UBSI", "total": 2056, "assigned": 2056, "unassigned": 0, "fresh": 513, "freshAssigned": 513, "freshUnassigned": 0, "noResp": 1384, "minat": 0, "ragu": 0, "menolak": 139, "closing": 15, "t2Unlabeled": 5, "anomUnassigned": 0, "t1": 1533, "t2": 480, "t3": 43, "avgScore": 14.66, "fuCount": 1543, "logCount": 2056, "batchCount": 13, "schoolCount": 324, "missingSchool": 0, "events": [{"code": "EVT-2855", "name": "Klaten Edu Expo & Job Fair 2025", "category": "Edufair", "date": "16 Dec 2025", "location": "GRAHA BUNG KARNO KLATEN", "total": 1189, "unassigned": 0, "fresh": 513, "noResp": 630, "minat": 0, "ragu": 0, "menolak": 45, "closing": 0, "t2Unlabeled": 1}, {"code": "EVT-2273", "name": "Edufair SMKN 1 Panjatan", "category": "Edufair", "date": "22 Apr 2026", "location": "SMKN 1 Panjatan", "total": 150, "unassigned": 0, "fresh": 0, "noResp": 142, "minat": 0, "ragu": 0, "menolak": 8, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2435", "name": "Presentasi SMAN 1 Imogiri", "category": "Akademik", "date": "10 Oct 2025", "location": "Auditorium Utama", "total": 124, "unassigned": 0, "fresh": 0, "noResp": 108, "minat": 0, "ragu": 0, "menolak": 16, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2283", "name": "Edufair SMAN 1 Jetis", "category": "Edufair", "date": "10 Feb 2026", "location": "SMAN 1 Jetis", "total": 85, "unassigned": 0, "fresh": 0, "noResp": 78, "minat": 0, "ragu": 0, "menolak": 6, "closing": 0, "t2Unlabeled": 1}, {"code": "EVT-2274", "name": "Edufair SMKN 1 Sewon", "category": "Edufair", "date": "22 Apr 2026", "location": "SMKN 1 Sewon", "total": 82, "unassigned": 0, "fresh": 0, "noResp": 61, "minat": 0, "ragu": 0, "menolak": 18, "closing": 1, "t2Unlabeled": 2}, {"code": "EVT-2430", "name": "Edufair SMKN 1 Nanggulan", "category": "Edufair", "date": "21 May 2026 - 21 Jun 2026", "location": "SMKN 1 Nanggulan", "total": 74, "unassigned": 0, "fresh": 0, "noResp": 72, "minat": 0, "ragu": 0, "menolak": 2, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2428", "name": "DATA APLIKASI PMB 2026-2027", "category": "Aplikasi PMB", "date": "01 May 2026", "location": "APLIKASI PMB", "total": 73, "unassigned": 0, "fresh": 0, "noResp": 54, "minat": 0, "ragu": 0, "menolak": 6, "closing": 13, "t2Unlabeled": 0}, {"code": "EVT-2275", "name": "Edufair SMKN 1 Pengasih", "category": "Edufair", "date": "23 Apr 2026", "location": "SMKN 1 Pengasih", "total": 67, "unassigned": 0, "fresh": 0, "noResp": 61, "minat": 0, "ragu": 0, "menolak": 5, "closing": 1, "t2Unlabeled": 0}, {"code": "EVT-2276", "name": "Edufair SMKN 4 Yogyakarta", "category": "Edufair", "date": "29 Apr 2026", "location": "SMKN 4 Yogyakarta", "total": 67, "unassigned": 0, "fresh": 0, "noResp": 56, "minat": 0, "ragu": 0, "menolak": 11, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2282", "name": "Edufair MAN 1 Kulonprogo", "category": "Edufair", "date": "10 Feb 2026", "location": "MAN 1 Kulonprogo", "total": 63, "unassigned": 0, "fresh": 0, "noResp": 51, "minat": 0, "ragu": 0, "menolak": 12, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2434", "name": "Presentasi MAN 3 KP", "category": "Akademik", "date": "10 Sep 2025", "location": "Auditorium Utama", "total": 42, "unassigned": 0, "fresh": 0, "noResp": 38, "minat": 0, "ragu": 0, "menolak": 4, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2436", "name": "Edufair SMAN 1 Kalibawang", "category": "Akademik", "date": "22 Jan 2026", "location": "Auditorium Utama", "total": 40, "unassigned": 0, "fresh": 0, "noResp": 33, "minat": 0, "ragu": 0, "menolak": 6, "closing": 0, "t2Unlabeled": 1}], "agents": [{"name": "luksti nandiya sari", "total": 92, "fresh": 23, "noResp": 64, "minat": 0, "ragu": 0, "menolak": 5, "closing": 0, "t2Unlabeled": 0}, {"name": "Admin UBSI Yogyakarta", "total": 73, "fresh": 0, "noResp": 54, "minat": 0, "ragu": 0, "menolak": 6, "closing": 13, "t2Unlabeled": 0}, {"name": "adellima putri", "total": 69, "fresh": 21, "noResp": 45, "minat": 0, "ragu": 0, "menolak": 3, "closing": 0, "t2Unlabeled": 0}, {"name": "fatkan ardana", "total": 69, "fresh": 20, "noResp": 47, "minat": 0, "ragu": 0, "menolak": 2, "closing": 0, "t2Unlabeled": 0}, {"name": "firda azzukhruf", "total": 69, "fresh": 9, "noResp": 55, "minat": 0, "ragu": 0, "menolak": 5, "closing": 0, "t2Unlabeled": 0}, {"name": "Aisyah Atha Khalila", "total": 68, "fresh": 21, "noResp": 43, "minat": 0, "ragu": 0, "menolak": 4, "closing": 0, "t2Unlabeled": 0}, {"name": "affah della puspita", "total": 68, "fresh": 22, "noResp": 41, "minat": 0, "ragu": 0, "menolak": 5, "closing": 0, "t2Unlabeled": 0}, {"name": "amelia latifatus zahra", "total": 68, "fresh": 19, "noResp": 49, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "angeli diva maharani", "total": 68, "fresh": 22, "noResp": 43, "minat": 0, "ragu": 0, "menolak": 3, "closing": 0, "t2Unlabeled": 0}, {"name": "apriliani nur yasinta", "total": 68, "fresh": 10, "noResp": 42, "minat": 0, "ragu": 0, "menolak": 16, "closing": 0, "t2Unlabeled": 0}, {"name": "ardin fathan fawwaz ahabba", "total": 68, "fresh": 0, "noResp": 55, "minat": 0, "ragu": 0, "menolak": 13, "closing": 0, "t2Unlabeled": 0}, {"name": "aura ayunda putri", "total": 68, "fresh": 20, "noResp": 38, "minat": 0, "ragu": 0, "menolak": 10, "closing": 0, "t2Unlabeled": 0}, {"name": "brigida sasta leota febriana", "total": 68, "fresh": 22, "noResp": 44, "minat": 0, "ragu": 0, "menolak": 1, "closing": 0, "t2Unlabeled": 1}, {"name": "dwi sinta rahmadina", "total": 68, "fresh": 21, "noResp": 44, "minat": 0, "ragu": 0, "menolak": 3, "closing": 0, "t2Unlabeled": 0}, {"name": "einggal pangestu", "total": 68, "fresh": 22, "noResp": 44, "minat": 0, "ragu": 0, "menolak": 2, "closing": 0, "t2Unlabeled": 0}, {"name": "faisal yusuf", "total": 68, "fresh": 26, "noResp": 39, "minat": 0, "ragu": 0, "menolak": 3, "closing": 0, "t2Unlabeled": 0}, {"name": "layla pasha hafidz", "total": 68, "fresh": 15, "noResp": 43, "minat": 0, "ragu": 0, "menolak": 10, "closing": 0, "t2Unlabeled": 0}, {"name": "lintang fajar agustin", "total": 68, "fresh": 25, "noResp": 38, "minat": 0, "ragu": 0, "menolak": 5, "closing": 0, "t2Unlabeled": 0}, {"name": "Prida Anindita", "total": 67, "fresh": 0, "noResp": 63, "minat": 0, "ragu": 0, "menolak": 4, "closing": 0, "t2Unlabeled": 0}, {"name": "monica yessica puspitasari", "total": 67, "fresh": 23, "noResp": 38, "minat": 0, "ragu": 0, "menolak": 6, "closing": 0, "t2Unlabeled": 0}, {"name": "muhamad nazul khoir", "total": 67, "fresh": 20, "noResp": 46, "minat": 0, "ragu": 0, "menolak": 1, "closing": 0, "t2Unlabeled": 0}, {"name": "muhammad lutfhi prasetyo utomo", "total": 67, "fresh": 20, "noResp": 45, "minat": 0, "ragu": 0, "menolak": 2, "closing": 0, "t2Unlabeled": 0}, {"name": "musthofa yudhistira", "total": 67, "fresh": 0, "noResp": 53, "minat": 0, "ragu": 0, "menolak": 11, "closing": 0, "t2Unlabeled": 3}, {"name": "novalia prihatanti", "total": 67, "fresh": 20, "noResp": 45, "minat": 0, "ragu": 0, "menolak": 2, "closing": 0, "t2Unlabeled": 0}, {"name": "putri fiky 'amalina", "total": 67, "fresh": 18, "noResp": 47, "minat": 0, "ragu": 0, "menolak": 1, "closing": 1, "t2Unlabeled": 0}, {"name": "resya sahroni putri", "total": 67, "fresh": 19, "noResp": 46, "minat": 0, "ragu": 0, "menolak": 1, "closing": 1, "t2Unlabeled": 0}, {"name": "rifatun nisa", "total": 67, "fresh": 17, "noResp": 48, "minat": 0, "ragu": 0, "menolak": 2, "closing": 0, "t2Unlabeled": 0}, {"name": "zahra salsabila afifah", "total": 67, "fresh": 20, "noResp": 36, "minat": 0, "ragu": 0, "menolak": 11, "closing": 0, "t2Unlabeled": 0}, {"name": "satriyo kautsar inas respati", "total": 66, "fresh": 22, "noResp": 43, "minat": 0, "ragu": 0, "menolak": 1, "closing": 0, "t2Unlabeled": 0}, {"name": "putri yuli astuti", "total": 64, "fresh": 16, "noResp": 46, "minat": 0, "ragu": 0, "menolak": 1, "closing": 0, "t2Unlabeled": 1}], "topSchools": [{"name": "PMB", "total": 6, "closing": 6, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "PMB-Sistem Informasi", "total": 38, "closing": 4, "warm": 0, "fresh": 0, "noResp": 28}, {"name": "PMB-Pariwisata", "total": 29, "closing": 3, "warm": 0, "fresh": 0, "noResp": 26}, {"name": "SMKN 1 Sewon", "total": 82, "closing": 1, "warm": 2, "fresh": 0, "noResp": 61}, {"name": "SMKN 1 Pengasih", "total": 67, "closing": 1, "warm": 0, "fresh": 0, "noResp": 61}, {"name": "SMKN 1 Panjatan", "total": 150, "closing": 0, "warm": 0, "fresh": 0, "noResp": 142}, {"name": "SMAN 1 Jetis", "total": 85, "closing": 0, "warm": 1, "fresh": 0, "noResp": 78}, {"name": "SMAN 1 KARANGANOM", "total": 78, "closing": 0, "warm": 0, "fresh": 64, "noResp": 13}], "reasons": [{"reason": "Sudah daftar kampus lain", "count": 11}, {"reason": "nomor tidak terdaftar di WhatsApp", "count": 9}, {"reason": "Nomor tidak tersedia di Whatsapp", "count": 4}, {"reason": "Sudah dapat kampus", "count": 3}, {"reason": "sudah dapat kampus", "count": 3}, {"reason": "Sudah diterima di kampus lain", "count": 2}, {"reason": "nomor tidak aktif", "count": 2}, {"reason": "memilih kerja", "count": 2}, {"reason": "Nomor WA tidak terdaftar", "count": 2}, {"reason": "sudah diterima di perguruan tinggi lain", "count": 2}, {"reason": "Sudah diterima Univ lain", "count": 2}, {"reason": "Sedang mengikuti LPK Jepang", "count": 1}, {"reason": "Sudah diterima dikampus lain", "count": 1}, {"reason": "Nomor tidak terdaftar di whatsApp", "count": 1}, {"reason": "belum ingin kuliah", "count": 1}, {"reason": "Memilih bekerja", "count": 1}, {"reason": "sudah di Terima kuliah di Solo", "count": 1}, {"reason": "Sudah diterima di UPN", "count": 1}, {"reason": "Sudah diterima di Binus", "count": 1}, {"reason": "Sudah diterima di PTN lewat jalur SNBT", "count": 1}, {"reason": "Univ", "count": 1}, {"reason": "Sudah kuliah di UT", "count": 1}, {"reason": "Kerja", "count": 1}, {"reason": "Sudah mendaftar", "count": 1}, {"reason": "Nomor tidak terdaftar di WhatsApp", "count": 1}, {"reason": "tidak berniat kuliah", "count": 1}, {"reason": "Sudah menjadi mahasiswa di universitas lain", "count": 1}, {"reason": "Sudah", "count": 1}, {"reason": "Ingin kerja", "count": 1}, {"reason": "Nomor tidak terdaftar di WhatsApp/tidak aktif", "count": 1}, {"reason": "Sudah mempunyai pilihan sendiri", "count": 1}, {"reason": "Sudah masuk Univ lain", "count": 1}, {"reason": "Fokus kerja", "count": 1}, {"reason": "tidak ingin kuliah", "count": 1}, {"reason": "Sudah diterima menjadi maba UNS", "count": 1}, {"reason": "nomor tidak terdaftar di whatsapp", "count": 1}, {"reason": "Sudah diterima jadi mana UGM", "count": 1}, {"reason": "Tidak bisa pakai beasiswa KIP utk cabang Yogyakarta", "count": 1}, {"reason": "Merantau", "count": 1}, {"reason": "belum minat kuliah", "count": 1}, {"reason": "Undip Semarang", "count": 1}, {"reason": "Sudah dapat tempat kuliah", "count": 1}, {"reason": "Blom berminat, Masi ada keperluan lain", "count": 1}, {"reason": "Nomor salah input? Tidak dapat memunculkan kontak", "count": 1}, {"reason": "Sudah daftar dikampus lain", "count": 1}, {"reason": "Tahun ini belum rencana untuk kuliah", "count": 1}, {"reason": "Sudah lolos SNBT", "count": 1}, {"reason": "Sudah berkuliah di Marine Cruise Yogyakarta", "count": 1}, {"reason": "Mau fokus kerja", "count": 1}, {"reason": "Kedinasan", "count": 1}, {"reason": "sudah di terima di perguruan tinggi lain", "count": 1}, {"reason": "menjawab enggk ah", "count": 1}]}, {"no": 2, "kampus": "UBSI KAMPUS JATIWARINGIN", "group": "UBSI", "total": 1203, "assigned": 1203, "unassigned": 0, "fresh": 0, "freshAssigned": 0, "freshUnassigned": 0, "noResp": 1156, "minat": 0, "ragu": 12, "menolak": 33, "closing": 2, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 1201, "t2": 0, "t3": 2, "avgScore": 20.07, "fuCount": 1203, "logCount": 902, "batchCount": 8, "schoolCount": 6, "missingSchool": 0, "events": [{"code": "EVT-2350", "name": "Edufair SMAN 7", "category": "Edufair", "date": "22 Jan 2026", "location": "Lapangan Sekolah", "total": 306, "unassigned": 0, "fresh": 0, "noResp": 293, "minat": 0, "ragu": 0, "menolak": 11, "closing": 2, "t2Unlabeled": 0}, {"code": "EVT-1313", "name": "Edu fair SMAN 16 Bekasi", "category": "Edufair", "date": "16 Dec 2025", "location": "SMAN 16 Bekasi", "total": 289, "unassigned": 0, "fresh": 0, "noResp": 268, "minat": 0, "ragu": 5, "menolak": 16, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-977", "name": "Edu Fair SMA Hutama Bekasi", "category": "Edufair", "date": "14 Nov 2025", "location": "Lapangan Sekolah", "total": 222, "unassigned": 0, "fresh": 0, "noResp": 221, "minat": 0, "ragu": 0, "menolak": 1, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-1308", "name": "Edu Fair SMA Tulus Bhakti", "category": "Edufair", "date": "18 Nov 2025", "location": "SMA Tulus Bhakti", "total": 216, "unassigned": 0, "fresh": 0, "noResp": 207, "minat": 0, "ragu": 6, "menolak": 3, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2799", "name": "Edu Fair SMK PKP 1 Jakarta", "category": "Open Booth (Mall/CFD/Public Area)", "date": "22 Jan 2026", "location": "Lapangan Sekolah", "total": 120, "unassigned": 0, "fresh": 0, "noResp": 118, "minat": 0, "ragu": 1, "menolak": 1, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2191", "name": "Edugair SMAN 61 Jakarta", "category": "Non Akademik", "date": "10 Apr 2026", "location": "Sman 61 Jakarta", "total": 50, "unassigned": 0, "fresh": 0, "noResp": 49, "minat": 0, "ragu": 0, "menolak": 1, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Nani Agustina", "total": 483, "fresh": 0, "noResp": 459, "minat": 0, "ragu": 8, "menolak": 16, "closing": 0, "t2Unlabeled": 0}, {"name": "Arwircha genvil", "total": 464, "fresh": 0, "noResp": 448, "minat": 0, "ragu": 2, "menolak": 13, "closing": 1, "t2Unlabeled": 0}, {"name": "Astri Sumarti", "total": 255, "fresh": 0, "noResp": 249, "minat": 0, "ragu": 2, "menolak": 4, "closing": 0, "t2Unlabeled": 0}, {"name": "Ahmad Fauzi", "total": 1, "fresh": 0, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 1, "t2Unlabeled": 0}], "topSchools": [{"name": "SMAN 7 BEKASI", "total": 306, "closing": 2, "warm": 0, "fresh": 0, "noResp": 293}, {"name": "SMAN 16 BEKASI", "total": 289, "closing": 0, "warm": 5, "fresh": 0, "noResp": 268}, {"name": "SMA HUTAMA BEKASI", "total": 222, "closing": 0, "warm": 0, "fresh": 0, "noResp": 221}, {"name": "SMA TULUS BHAKTI", "total": 216, "closing": 0, "warm": 6, "fresh": 0, "noResp": 207}, {"name": "SMK PKP 1 Jakarta", "total": 120, "closing": 0, "warm": 1, "fresh": 0, "noResp": 118}, {"name": "SMAN 61 Jakarta", "total": 50, "closing": 0, "warm": 0, "fresh": 0, "noResp": 49}], "reasons": []}, {"no": 3, "kampus": "UBSI KAMPUS TANGERANG", "group": "UBSI", "total": 1081, "assigned": 79, "unassigned": 1002, "fresh": 1067, "freshAssigned": 65, "freshUnassigned": 1002, "noResp": 7, "minat": 1, "ragu": 3, "menolak": 3, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 1081, "t2": 0, "t3": 0, "avgScore": 0.35, "fuCount": 15, "logCount": 0, "batchCount": 5, "schoolCount": 5, "missingSchool": 0, "events": [{"code": "EVT-976", "name": "Edufair SMAN 4 Kabupaten Tangerang", "category": "Edufair", "date": "27 Nov 2025", "location": "Halaman Sekolah", "total": 296, "unassigned": 296, "fresh": 296, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-975", "name": "Edufair SMAN 4 Kota Tangerang", "category": "Edufair", "date": "20 Nov 2025", "location": "Kelas XII dan Halaman Sekolah", "total": 287, "unassigned": 287, "fresh": 287, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-974", "name": "Edufair MAN 2 Kota Tangerang", "category": "Edufair", "date": "19 Nov 2025", "location": "Kelas XII dan Halaman Sekolah", "total": 245, "unassigned": 245, "fresh": 245, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-973", "name": "Sosialisasi PMB SMK Avicena Rajeg", "category": "Sosialisasi", "date": "13 Nov 2025", "location": "Kelas XII", "total": 186, "unassigned": 174, "fresh": 186, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-972", "name": "Edufair SMAN 20 Kabupaten Tangerang", "category": "Edufair", "date": "28 Oct 2025", "location": "Halaman Sekolah", "total": 67, "unassigned": 0, "fresh": 53, "noResp": 7, "minat": 1, "ragu": 3, "menolak": 3, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Kampus Tangerang", "total": 79, "fresh": 65, "noResp": 7, "minat": 1, "ragu": 3, "menolak": 3, "closing": 0, "t2Unlabeled": 0}, {"name": "Belum diassign", "total": 1002, "fresh": 1002, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "SMAN 4 Kabupaten Tangerang", "total": 296, "closing": 0, "warm": 0, "fresh": 296, "noResp": 0}, {"name": "SMAN 4 Kota Tangerang", "total": 287, "closing": 0, "warm": 0, "fresh": 287, "noResp": 0}, {"name": "MAN 2 Kota Tangerang", "total": 245, "closing": 0, "warm": 0, "fresh": 245, "noResp": 0}, {"name": "SMK Avicena Rajeg", "total": 186, "closing": 0, "warm": 0, "fresh": 186, "noResp": 0}, {"name": "SMAN 20 Kabupaten Tangerang", "total": 67, "closing": 0, "warm": 4, "fresh": 53, "noResp": 7}], "reasons": []}, {"no": 4, "kampus": "UBSI KAMPUS FATMAWATI", "group": "UBSI", "total": 593, "assigned": 37, "unassigned": 556, "fresh": 510, "freshAssigned": 0, "freshUnassigned": 510, "noResp": 38, "minat": 27, "ragu": 6, "menolak": 3, "closing": 9, "t2Unlabeled": 0, "anomUnassigned": 46, "t1": 581, "t2": 1, "t3": 11, "avgScore": 5.89, "fuCount": 83, "logCount": 0, "batchCount": 4, "schoolCount": 0, "missingSchool": 593, "events": [{"code": "EVT-3433", "name": "Edu Fair SMAN 97 Jakarta", "category": "Edufair", "date": "23 Oct 2026", "location": "Halaman Sekolah", "total": 290, "unassigned": 290, "fresh": 290, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-962", "name": "Edufair SMKN 41 Jakarta", "category": "Edufair", "date": "13 May 2026", "location": "Halaman Sekolah", "total": 245, "unassigned": 244, "fresh": 207, "noResp": 18, "minat": 12, "ragu": 3, "menolak": 2, "closing": 3, "t2Unlabeled": 0}, {"code": "EVT-965", "name": "Konfirmasi data", "category": "Aplikasi PMB", "date": "10 Jun 2026", "location": "Front Office", "total": 36, "unassigned": 0, "fresh": 0, "noResp": 20, "minat": 8, "ragu": 1, "menolak": 1, "closing": 6, "t2Unlabeled": 0}, {"code": "EVT-2351", "name": "Data Lead Iklan Infojaksel", "category": "Aplikasi PMB", "date": "27 May 2026", "location": "Aplikasi", "total": 22, "unassigned": 22, "fresh": 13, "noResp": 0, "minat": 7, "ragu": 2, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "BSI Fatmawati", "total": 37, "fresh": 0, "noResp": 20, "minat": 9, "ragu": 1, "menolak": 1, "closing": 6, "t2Unlabeled": 0}, {"name": "Belum diassign", "total": 556, "fresh": 510, "noResp": 18, "minat": 18, "ragu": 5, "menolak": 2, "closing": 3, "t2Unlabeled": 0}], "topSchools": [], "reasons": []}, {"no": 5, "kampus": "UBSI KAMPUS TASIKMALAYA", "group": "UBSI", "total": 562, "assigned": 78, "unassigned": 484, "fresh": 484, "freshAssigned": 0, "freshUnassigned": 484, "noResp": 20, "minat": 10, "ragu": 20, "menolak": 5, "closing": 23, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 538, "t2": 0, "t3": 24, "avgScore": 7.78, "fuCount": 78, "logCount": 9, "batchCount": 2, "schoolCount": 34, "missingSchool": 7, "events": [{"code": "EVT-952", "name": "Job Edu Fair & Pelepasan Kelas XII SMKN 2 Ciamis", "category": "Edufair", "date": "19 May 2026", "location": "Auditorium Utama", "total": 484, "unassigned": 484, "fresh": 484, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2148", "name": "Incer Fest 2026 (Empowering Future Digital Inovators)", "category": "Non Akademik", "date": "02 Mar 2026", "location": "Aula Kampus UBSI Tasikmalaya", "total": 78, "unassigned": 0, "fresh": 0, "noResp": 20, "minat": 10, "ragu": 20, "menolak": 5, "closing": 23, "t2Unlabeled": 0}], "agents": [{"name": "Sri Mulyani", "total": 45, "fresh": 0, "noResp": 19, "minat": 1, "ragu": 6, "menolak": 5, "closing": 14, "t2Unlabeled": 0}, {"name": "Haerul Fatah", "total": 19, "fresh": 0, "noResp": 0, "minat": 7, "ragu": 10, "menolak": 0, "closing": 2, "t2Unlabeled": 0}, {"name": "Devi Novita", "total": 6, "fresh": 0, "noResp": 0, "minat": 0, "ragu": 3, "menolak": 0, "closing": 3, "t2Unlabeled": 0}, {"name": "Agung Baitul Hikmah", "total": 4, "fresh": 0, "noResp": 0, "minat": 1, "ragu": 1, "menolak": 0, "closing": 2, "t2Unlabeled": 0}, {"name": "Siti Fatimah", "total": 4, "fresh": 0, "noResp": 1, "minat": 1, "ragu": 0, "menolak": 0, "closing": 2, "t2Unlabeled": 0}, {"name": "Belum diassign", "total": 484, "fresh": 484, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "Tasikmalaya", "total": 23, "closing": 3, "warm": 6, "fresh": 0, "noResp": 12}, {"name": "Tasikamalaya", "total": 4, "closing": 2, "warm": 0, "fresh": 0, "noResp": 1}, {"name": "SMAN 5 Tasikmalaya", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMK PGRI Cikoneng", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "Belum ada info", "total": 10, "closing": 1, "warm": 9, "fresh": 0, "noResp": 0}, {"name": "Ponpes riyadhlul ulum waddawah", "total": 1, "closing": 1, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMA NEGERI 1 SINGAPARNA", "total": 1, "closing": 1, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMA Terpadu Cikanyere", "total": 1, "closing": 1, "warm": 0, "fresh": 0, "noResp": 0}], "reasons": []}, {"no": 6, "kampus": "UBSI KAMPUS CIBITUNG", "group": "UBSI", "total": 470, "assigned": 22, "unassigned": 448, "fresh": 447, "freshAssigned": 0, "freshUnassigned": 447, "noResp": 0, "minat": 9, "ragu": 3, "menolak": 1, "closing": 10, "t2Unlabeled": 0, "anomUnassigned": 1, "t1": 447, "t2": 12, "t3": 11, "avgScore": 3.54, "fuCount": 23, "logCount": 7, "batchCount": 8, "schoolCount": 4, "missingSchool": 0, "events": [{"code": "EVT-959", "name": "Support Event Perpisahan SMKN 15 Kota Bekasi", "category": "Edufair", "date": "11 May 2026", "location": "Lapangan SMKN 15 Kota Bekasi", "total": 308, "unassigned": 308, "fresh": 308, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-960", "name": "Support Event Perpisahan SMK Yapin Bekasi", "category": "Edufair", "date": "20 May 2026", "location": "Aula SMK Yapin Bekasi", "total": 93, "unassigned": 89, "fresh": 89, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 4, "t2Unlabeled": 0}, {"code": "EVT-969", "name": "Aplikasi Follow Up PMB", "category": "Aplikasi PMB", "date": "01 Jun 2026", "location": "Kampus Cibitung", "total": 42, "unassigned": 24, "fresh": 23, "noResp": 0, "minat": 9, "ragu": 3, "menolak": 1, "closing": 6, "t2Unlabeled": 0}, {"code": "EVT-961", "name": "Support Event Perpisahan SMK Yapin 02 Setu", "category": "Edufair", "date": "21 May 2026", "location": "Gedung Cazamora Bekasi", "total": 27, "unassigned": 27, "fresh": 27, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Dian Ardiansyah", "total": 22, "fresh": 0, "noResp": 0, "minat": 9, "ragu": 3, "menolak": 1, "closing": 9, "t2Unlabeled": 0}, {"name": "Belum diassign", "total": 448, "fresh": 447, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 1, "t2Unlabeled": 0}], "topSchools": [{"name": "SMK/SMA", "total": 42, "closing": 6, "warm": 12, "fresh": 23, "noResp": 0}, {"name": "SMK Yapin Bekasi", "total": 93, "closing": 4, "warm": 0, "fresh": 89, "noResp": 0}, {"name": "SMKN 15 Kota Bekasi", "total": 308, "closing": 0, "warm": 0, "fresh": 308, "noResp": 0}, {"name": "SMK Yapin 02 Setu", "total": 27, "closing": 0, "warm": 0, "fresh": 27, "noResp": 0}], "reasons": [{"reason": "Sudah diterima PTN", "count": 1}]}, {"no": 7, "kampus": "UNM KAMPUS RAWAMANGUN", "group": "UNM", "total": 470, "assigned": 470, "unassigned": 0, "fresh": 416, "freshAssigned": 416, "freshUnassigned": 0, "noResp": 33, "minat": 10, "ragu": 3, "menolak": 6, "closing": 2, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 460, "t2": 3, "t3": 7, "avgScore": 3.41, "fuCount": 54, "logCount": 102, "batchCount": 4, "schoolCount": 54, "missingSchool": 0, "events": [{"code": "EVT-2383", "name": "Edufair SMK N 40 jakarta", "category": "Edufair", "date": "07 May 2026", "location": "Lapangan SMK N 40 Jakarta", "total": 341, "unassigned": 0, "fresh": 319, "noResp": 16, "minat": 0, "ragu": 1, "menolak": 5, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-2804", "name": "FU Data Pendaftar PMB", "category": "Aplikasi PMB", "date": "25 Jul 2026", "location": "UNM Rawamangun", "total": 85, "unassigned": 0, "fresh": 80, "noResp": 5, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-916", "name": "NICE Prodi Informatika Kampus UNM RWM", "category": "Open House (Hangout/NICE/Cybertalk)", "date": "03 Jun 2026", "location": "UNM Rawamangun", "total": 44, "unassigned": 0, "fresh": 17, "noResp": 12, "minat": 10, "ragu": 2, "menolak": 1, "closing": 2, "t2Unlabeled": 0}], "agents": [{"name": "Galuh Tiasari Estiningtyas", "total": 171, "fresh": 148, "noResp": 15, "minat": 6, "ragu": 0, "menolak": 1, "closing": 1, "t2Unlabeled": 0}, {"name": "Amelia Rahmasari", "total": 95, "fresh": 72, "noResp": 16, "minat": 0, "ragu": 1, "menolak": 5, "closing": 1, "t2Unlabeled": 0}, {"name": "Adit", "total": 94, "fresh": 94, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Harnojo", "total": 94, "fresh": 94, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Maruloh", "total": 16, "fresh": 8, "noResp": 2, "minat": 4, "ragu": 2, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "SMK", "total": 129, "closing": 2, "warm": 12, "fresh": 97, "noResp": 17}, {"name": "SMK 40", "total": 222, "closing": 0, "warm": 1, "fresh": 200, "noResp": 16}, {"name": "SMK BINA PANGUDI LUHUR", "total": 24, "closing": 0, "warm": 0, "fresh": 24, "noResp": 0}, {"name": "SMK PEMBANGUNAN", "total": 16, "closing": 0, "warm": 0, "fresh": 16, "noResp": 0}, {"name": "SMK NURUL IMAN", "total": 11, "closing": 0, "warm": 0, "fresh": 11, "noResp": 0}, {"name": "SMA BINA PANGUDI LUHUR", "total": 4, "closing": 0, "warm": 0, "fresh": 4, "noResp": 0}, {"name": "SMK TIRTASARI SURYA", "total": 4, "closing": 0, "warm": 0, "fresh": 4, "noResp": 0}, {"name": "SMKN 50 JAKARTA", "total": 4, "closing": 0, "warm": 0, "fresh": 4, "noResp": 0}], "reasons": [{"reason": "Sudah kerja", "count": 1}, {"reason": "Sudah lolos PTN", "count": 1}, {"reason": "Sudah diterima di kampus lain", "count": 1}, {"reason": "Menunggu kip", "count": 1}, {"reason": "Fokus kerja", "count": 1}, {"reason": "Sudah diterima di ptn", "count": 1}]}, {"no": 8, "kampus": "UBSI KAMPUS CIKARANG", "group": "UBSI", "total": 377, "assigned": 377, "unassigned": 0, "fresh": 3, "freshAssigned": 3, "freshUnassigned": 0, "noResp": 95, "minat": 5, "ragu": 3, "menolak": 12, "closing": 259, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 116, "t2": 2, "t3": 259, "avgScore": 75.07, "fuCount": 374, "logCount": 124, "batchCount": 4, "schoolCount": 130, "missingSchool": 4, "events": [{"code": "EVT-2440", "name": "APLIKASI PMB", "category": "Aplikasi PMB", "date": "12 Jun 2026", "location": "UBSI CIKARANG", "total": 377, "unassigned": 0, "fresh": 3, "noResp": 95, "minat": 5, "ragu": 3, "menolak": 12, "closing": 259, "t2Unlabeled": 0}], "agents": [{"name": "Indah Novitasari", "total": 166, "fresh": 0, "noResp": 21, "minat": 0, "ragu": 1, "menolak": 5, "closing": 139, "t2Unlabeled": 0}, {"name": "Salsabila Qibtia", "total": 140, "fresh": 1, "noResp": 18, "minat": 3, "ragu": 0, "menolak": 2, "closing": 116, "t2Unlabeled": 0}, {"name": "Dipa", "total": 24, "fresh": 0, "noResp": 14, "minat": 2, "ragu": 2, "menolak": 3, "closing": 3, "t2Unlabeled": 0}, {"name": "Fahri Akbar", "total": 22, "fresh": 0, "noResp": 19, "minat": 0, "ragu": 0, "menolak": 2, "closing": 1, "t2Unlabeled": 0}, {"name": "Mereanus Lase", "total": 21, "fresh": 0, "noResp": 21, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Dame Sihite", "total": 2, "fresh": 2, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Febriyanti", "total": 2, "fresh": 0, "noResp": 2, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "SMKN 1 CIKARANG SELATAN", "total": 37, "closing": 37, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMKN 1 CIBARUSAH", "total": 17, "closing": 17, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAN 1 SERANG BARU", "total": 15, "closing": 15, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAN 1 CIBARUSAH", "total": 8, "closing": 8, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMK INFORMATIKA AMANAH BANGSA", "total": 8, "closing": 8, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "Data dari PMB", "total": 120, "closing": 6, "warm": 8, "fresh": 3, "noResp": 91}, {"name": "SMAN 2 CIKARANG SELATAN", "total": 6, "closing": 6, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMKN 1 TAMBUN SELATAN", "total": 6, "closing": 6, "warm": 0, "fresh": 0, "noResp": 0}], "reasons": []}, {"no": 9, "kampus": "UNM KAMPUS MARGONDA", "group": "UNM", "total": 309, "assigned": 0, "unassigned": 309, "fresh": 309, "freshAssigned": 0, "freshUnassigned": 309, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 309, "t2": 0, "t3": 0, "avgScore": 0.0, "fuCount": 0, "logCount": 0, "batchCount": 1, "schoolCount": 1, "missingSchool": 0, "events": [{"code": "EVT-3193", "name": "Edu Fair SMAN 1 Depok", "category": "Edufair", "date": "15 Sep 2026", "location": "SMAN 1 Depok", "total": 309, "unassigned": 309, "fresh": 309, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Belum diassign", "total": 309, "fresh": 309, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "SMAN 1 Depok", "total": 309, "closing": 0, "warm": 0, "fresh": 309, "noResp": 0}], "reasons": []}, {"no": 10, "kampus": "UBSI KAMPUS CIKAMPEK", "group": "UBSI", "total": 270, "assigned": 270, "unassigned": 0, "fresh": 0, "freshAssigned": 0, "freshUnassigned": 0, "noResp": 28, "minat": 87, "ragu": 0, "menolak": 1, "closing": 154, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 85, "t2": 31, "t3": 154, "avgScore": 76.85, "fuCount": 270, "logCount": 270, "batchCount": 16, "schoolCount": 64, "missingSchool": 116, "events": [{"code": "EVT-931", "name": "Database Aplikasi PMB UBSI Cikampek", "category": "Aplikasi PMB", "date": "15 Oct 2025", "location": "UBSI Cikampek", "total": 270, "unassigned": 0, "fresh": 0, "noResp": 28, "minat": 87, "ragu": 0, "menolak": 1, "closing": 154, "t2Unlabeled": 0}], "agents": [{"name": "Ironia Lestari", "total": 81, "fresh": 0, "noResp": 14, "minat": 20, "ragu": 0, "menolak": 0, "closing": 47, "t2Unlabeled": 0}, {"name": "Ani Nuraini", "total": 73, "fresh": 0, "noResp": 9, "minat": 17, "ragu": 0, "menolak": 0, "closing": 47, "t2Unlabeled": 0}, {"name": "Mahesa Ikni Rhamadan", "total": 45, "fresh": 0, "noResp": 0, "minat": 12, "ragu": 0, "menolak": 0, "closing": 33, "t2Unlabeled": 0}, {"name": "Irena Qurain Deskadika Djunaedi", "total": 37, "fresh": 0, "noResp": 1, "minat": 12, "ragu": 0, "menolak": 0, "closing": 24, "t2Unlabeled": 0}, {"name": "Annisa Alfianatu Syahadah", "total": 34, "fresh": 0, "noResp": 4, "minat": 26, "ragu": 0, "menolak": 1, "closing": 3, "t2Unlabeled": 0}], "topSchools": [{"name": "SMKN 1 JATISARI", "total": 22, "closing": 22, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAN 1 JATISARI", "total": 18, "closing": 18, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMKN 1 CIKAMPEK", "total": 6, "closing": 6, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAN 1 BANYUSARI", "total": 5, "closing": 5, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAN 1 BUNGURSARI", "total": 5, "closing": 5, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAN 1 PABUARAN SUBANG", "total": 5, "closing": 5, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAN 2 CIKAMPEK", "total": 5, "closing": 5, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMK INSAN TAZAKKA KARAWANG", "total": 5, "closing": 5, "warm": 0, "fresh": 0, "noResp": 0}], "reasons": [{"reason": "Memilih kampus lain", "count": 1}]}, {"no": 11, "kampus": "UBSI KAMPUS TEGAL", "group": "UBSI", "total": 204, "assigned": 0, "unassigned": 204, "fresh": 204, "freshAssigned": 0, "freshUnassigned": 204, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 204, "t2": 0, "t3": 0, "avgScore": 0.0, "fuCount": 0, "logCount": 0, "batchCount": 1, "schoolCount": 1, "missingSchool": 0, "events": [{"code": "EVT-2232", "name": "Tegal Eduucation Fair 2026", "category": "Edufair", "date": "01 May 2026 - 03 May 2026", "location": "JL.Pancasila Alun-Alun Kota Tegal", "total": 204, "unassigned": 204, "fresh": 204, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Belum diassign", "total": 204, "fresh": 204, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "SMA N 2 Slawi", "total": 204, "closing": 0, "warm": 0, "fresh": 204, "noResp": 0}], "reasons": []}, {"no": 12, "kampus": "UBSI KAMPUS BOGOR", "group": "UBSI", "total": 188, "assigned": 0, "unassigned": 188, "fresh": 188, "freshAssigned": 0, "freshUnassigned": 188, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 188, "t2": 0, "t3": 0, "avgScore": 0.0, "fuCount": 0, "logCount": 0, "batchCount": 2, "schoolCount": 3, "missingSchool": 127, "events": [{"code": "EVT-3258", "name": "Edufair SMAN 4 Kota Bogor", "category": "Edufair", "date": "19 Oct 2026", "location": "Lapangan SMAN 4 Kota Bogor", "total": 127, "unassigned": 127, "fresh": 127, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-3256", "name": "Edufair SMKN 1 Kota Bogor", "category": "Edufair", "date": "30 Sep 2026", "location": "Lapangan SMKN 1 Kota Bogor", "total": 61, "unassigned": 61, "fresh": 61, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Belum diassign", "total": 188, "fresh": 188, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "XI", "total": 31, "closing": 0, "warm": 0, "fresh": 31, "noResp": 0}, {"name": "X", "total": 16, "closing": 0, "warm": 0, "fresh": 16, "noResp": 0}, {"name": "XII", "total": 14, "closing": 0, "warm": 0, "fresh": 14, "noResp": 0}], "reasons": []}, {"no": 13, "kampus": "UBSI KAMPUS KALIABANG", "group": "UBSI", "total": 154, "assigned": 154, "unassigned": 0, "fresh": 126, "freshAssigned": 126, "freshUnassigned": 0, "noResp": 13, "minat": 5, "ragu": 2, "menolak": 1, "closing": 7, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 139, "t2": 8, "t3": 7, "avgScore": 8.77, "fuCount": 28, "logCount": 0, "batchCount": 1, "schoolCount": 1, "missingSchool": 0, "events": [{"code": "EVT-978", "name": "Data CRM Gel 3", "category": "Aplikasi PMB", "date": "04 Jun 2026", "location": "Auditorium Utama", "total": 154, "unassigned": 0, "fresh": 126, "noResp": 13, "minat": 5, "ragu": 2, "menolak": 1, "closing": 7, "t2Unlabeled": 0}], "agents": [{"name": "nasiela Azira", "total": 152, "fresh": 126, "noResp": 12, "minat": 5, "ragu": 2, "menolak": 1, "closing": 6, "t2Unlabeled": 0}, {"name": "Muhamad Tabrani", "total": 2, "fresh": 0, "noResp": 1, "minat": 0, "ragu": 0, "menolak": 0, "closing": 1, "t2Unlabeled": 0}], "topSchools": [{"name": "SLTA", "total": 154, "closing": 7, "warm": 7, "fresh": 126, "noResp": 13}], "reasons": []}, {"no": 14, "kampus": "UBSI KAMPUS BEKASI CUT MUTIA", "group": "UBSI", "total": 140, "assigned": 0, "unassigned": 140, "fresh": 140, "freshAssigned": 0, "freshUnassigned": 140, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 140, "t2": 0, "t3": 0, "avgScore": 0.0, "fuCount": 0, "logCount": 0, "batchCount": 1, "schoolCount": 0, "missingSchool": 140, "events": [{"code": "EVT-3231", "name": "Edufair SMA Ananda Bekasi", "category": "Edufair", "date": "25 Sep 2026", "location": "SMK Ananda Bekasi", "total": 140, "unassigned": 140, "fresh": 140, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Belum diassign", "total": 140, "fresh": 140, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [], "reasons": []}, {"no": 15, "kampus": "UBSI KAMPUS SLIPI", "group": "UBSI", "total": 140, "assigned": 0, "unassigned": 140, "fresh": 140, "freshAssigned": 0, "freshUnassigned": 140, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 140, "t2": 0, "t3": 0, "avgScore": 0.0, "fuCount": 0, "logCount": 0, "batchCount": 1, "schoolCount": 1, "missingSchool": 0, "events": [{"code": "EVT-2915", "name": "Open Booth CFD Jakarta Barat", "category": "Open Booth (Mall/CFD/Public Area)", "date": "09 Aug 2026", "location": "CFJ Jakarta Barat", "total": 140, "unassigned": 140, "fresh": 140, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Belum diassign", "total": 140, "fresh": 140, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "NN", "total": 140, "closing": 0, "warm": 0, "fresh": 140, "noResp": 0}], "reasons": []}, {"no": 16, "kampus": "UBSI KAMPUS CENGKARENG", "group": "UBSI", "total": 131, "assigned": 6, "unassigned": 125, "fresh": 124, "freshAssigned": 0, "freshUnassigned": 124, "noResp": 1, "minat": 0, "ragu": 0, "menolak": 0, "closing": 6, "t2Unlabeled": 0, "anomUnassigned": 1, "t1": 124, "t2": 1, "t3": 6, "avgScore": 4.73, "fuCount": 7, "logCount": 7, "batchCount": 1, "schoolCount": 0, "missingSchool": 131, "events": [{"code": "EVT-2555", "name": "FU Camaba Belum Bayar DU", "category": "Aplikasi PMB", "date": "29 Jun 2026 - 02 Jul 2026", "location": "Administrasi", "total": 131, "unassigned": 125, "fresh": 124, "noResp": 1, "minat": 0, "ragu": 0, "menolak": 0, "closing": 6, "t2Unlabeled": 0}], "agents": [{"name": "Yunita", "total": 6, "fresh": 0, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 6, "t2Unlabeled": 0}, {"name": "Belum diassign", "total": 125, "fresh": 124, "noResp": 1, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [], "reasons": []}, {"no": 17, "kampus": "UBSI KAMPUS SOLO", "group": "UBSI", "total": 110, "assigned": 35, "unassigned": 75, "fresh": 74, "freshAssigned": 0, "freshUnassigned": 74, "noResp": 0, "minat": 3, "ragu": 4, "menolak": 2, "closing": 27, "t2Unlabeled": 0, "anomUnassigned": 1, "t1": 82, "t2": 1, "t3": 27, "avgScore": 28.14, "fuCount": 36, "logCount": 13, "batchCount": 2, "schoolCount": 32, "missingSchool": 47, "events": [{"code": "EVT-1683", "name": "LIve Tiktok PMB", "category": "Non Akademik", "date": "28 Aug 2025", "location": "Perpustakaan UBSI Solo", "total": 110, "unassigned": 75, "fresh": 74, "noResp": 0, "minat": 3, "ragu": 4, "menolak": 2, "closing": 27, "t2Unlabeled": 0}], "agents": [{"name": "ahmad fauzi", "total": 35, "fresh": 0, "noResp": 0, "minat": 3, "ragu": 4, "menolak": 2, "closing": 26, "t2Unlabeled": 0}, {"name": "Belum diassign", "total": 75, "fresh": 74, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 1, "t2Unlabeled": 0}], "topSchools": [{"name": "SMK NEGERI 1 PEDAN KLATEN", "total": 10, "closing": 2, "warm": 0, "fresh": 6, "noResp": 0}, {"name": "SMK NEGERI 1 BOYOLALI", "total": 4, "closing": 2, "warm": 0, "fresh": 2, "noResp": 0}, {"name": "SMA NEGERI 1 ANDONG BOYOLALI", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMAS KETAPANG 3 BOGOR", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMK KARANGANYAR", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMK MUHAMMADIYAH 3 GIRIWOYO WONOGIRI", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMK TELEKOMUNIKASI TUNAS HARAPAN SEMARANG", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}, {"name": "SMK WIHDATUL UMMAH KARANGANYAR", "total": 2, "closing": 2, "warm": 0, "fresh": 0, "noResp": 0}], "reasons": []}, {"no": 18, "kampus": "UBSI KAMPUS KARAWANG", "group": "UBSI", "total": 87, "assigned": 0, "unassigned": 87, "fresh": 87, "freshAssigned": 0, "freshUnassigned": 87, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 87, "t2": 0, "t3": 0, "avgScore": 0.0, "fuCount": 0, "logCount": 0, "batchCount": 1, "schoolCount": 56, "missingSchool": 0, "events": [{"code": "EVT-2377", "name": "Bincang Kampus Bersama Orang Tua", "category": "BKOT/NKBO/TWOT", "date": "23 May 2026", "location": "Aula Kampus UBSI Karawang", "total": 87, "unassigned": 87, "fresh": 87, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Belum diassign", "total": 87, "fresh": 87, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "SMKN 3 KARAWANG", "total": 9, "closing": 0, "warm": 0, "fresh": 9, "noResp": 0}, {"name": "SMK INDUSTRI MANDIRI KARAWANG", "total": 8, "closing": 0, "warm": 0, "fresh": 8, "noResp": 0}, {"name": "MA NIHAYATUL AMAL RAWAMERTA KARAWANG", "total": 4, "closing": 0, "warm": 0, "fresh": 4, "noResp": 0}, {"name": "SMA NEGERI 1 KLARI KARAWANG", "total": 4, "closing": 0, "warm": 0, "fresh": 4, "noResp": 0}, {"name": "MA NEGERI 4 KARAWANG", "total": 3, "closing": 0, "warm": 0, "fresh": 3, "noResp": 0}, {"name": "SMAN 1 RAWAMERTA KARAWANG", "total": 3, "closing": 0, "warm": 0, "fresh": 3, "noResp": 0}, {"name": "SMAN 2 KARAWANG", "total": 3, "closing": 0, "warm": 0, "fresh": 3, "noResp": 0}, {"name": "SMA NEGERI 1 BATUJAYA KARAWANG", "total": 2, "closing": 0, "warm": 0, "fresh": 2, "noResp": 0}], "reasons": []}, {"no": 19, "kampus": "UBSI KAMPUS SUKABUMI", "group": "UBSI", "total": 84, "assigned": 84, "unassigned": 0, "fresh": 84, "freshAssigned": 84, "freshUnassigned": 0, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 84, "t2": 0, "t3": 0, "avgScore": 0.0, "fuCount": 0, "logCount": 0, "batchCount": 3, "schoolCount": 28, "missingSchool": 0, "events": [{"code": "EVT-1811", "name": "ASMILA BSI FLASH", "category": "Non Akademik", "date": "19 Dec 2025 - 20 Dec 2025", "location": "Aula UBSI Sukabumi Cemerlang", "total": 60, "unassigned": 0, "fresh": 60, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"code": "EVT-1855", "name": "Bootcamp Pesantren IT", "category": "Workshop", "date": "05 Mar 2026", "location": "Aula Kampus Veteran", "total": 24, "unassigned": 0, "fresh": 24, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Anisa Julia Utami", "total": 66, "fresh": 66, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Anita Rahmawati", "total": 6, "fresh": 6, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Dini", "total": 6, "fresh": 6, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Shindi", "total": 6, "fresh": 6, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [{"name": "SMAN 1 SUKARAJA", "total": 9, "closing": 0, "warm": 0, "fresh": 9, "noResp": 0}, {"name": "SMAN 1 PELABUHAN RATU", "total": 8, "closing": 0, "warm": 0, "fresh": 8, "noResp": 0}, {"name": "MAN 1 KOTA SUKABUMI", "total": 6, "closing": 0, "warm": 0, "fresh": 6, "noResp": 0}, {"name": "SMA IPRO DAARUSSALAAM", "total": 6, "closing": 0, "warm": 0, "fresh": 6, "noResp": 0}, {"name": "SMAN 1 CISAAT", "total": 6, "closing": 0, "warm": 0, "fresh": 6, "noResp": 0}, {"name": "SMK PASIM PLUS KOTA SUKABUMI", "total": 6, "closing": 0, "warm": 0, "fresh": 6, "noResp": 0}, {"name": "SMAN 2 KOTA SUKABUMI", "total": 5, "closing": 0, "warm": 0, "fresh": 5, "noResp": 0}, {"name": "SMK PLUS AL-FARHAN", "total": 4, "closing": 0, "warm": 0, "fresh": 4, "noResp": 0}], "reasons": []}, {"no": 20, "kampus": "UBSI KAMPUS PONTIANAK", "group": "UBSI", "total": 13, "assigned": 1, "unassigned": 12, "fresh": 12, "freshAssigned": 0, "freshUnassigned": 12, "noResp": 0, "minat": 1, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0, "anomUnassigned": 0, "t1": 13, "t2": 0, "t3": 0, "avgScore": 4.23, "fuCount": 1, "logCount": 0, "batchCount": 1, "schoolCount": 0, "missingSchool": 13, "events": [{"code": "EVT-950", "name": "Open booth", "category": "Open Booth (Mall/CFD/Public Area)", "date": "23 May 2026 - 24 May 2026", "location": "Kaisar Sport Center Pontianak", "total": 13, "unassigned": 12, "fresh": 12, "noResp": 0, "minat": 1, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "agents": [{"name": "Eri Bayu Pratama", "total": 1, "fresh": 0, "noResp": 0, "minat": 1, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}, {"name": "Belum diassign", "total": 12, "fresh": 12, "noResp": 0, "minat": 0, "ragu": 0, "menolak": 0, "closing": 0, "t2Unlabeled": 0}], "topSchools": [], "reasons": []}]
JSON_CAMPUSES;

$allCrmAgentsJson = <<<'JSON_AGENTS'
[{"name": "Indah Novitasari", "kampus": "UBSI KAMPUS CIKARANG", "total": 166, "fresh": 0, "noResp": 21, "warm": 1, "minat": 0, "ragu": 1, "t2Unlabeled": 0, "menolak": 5, "closing": 139, "convPct": 83.7, "avgScore": 86.7}, {"name": "Salsabila Qibtia", "kampus": "UBSI KAMPUS CIKARANG", "total": 140, "fresh": 1, "noResp": 18, "warm": 3, "minat": 3, "ragu": 0, "t2Unlabeled": 0, "menolak": 2, "closing": 116, "convPct": 82.9, "avgScore": 86.7}, {"name": "Ironia Lestari", "kampus": "UBSI KAMPUS CIKAMPEK", "total": 81, "fresh": 0, "noResp": 14, "warm": 20, "minat": 20, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 47, "convPct": 58.0, "avgScore": 75.1}, {"name": "Ani Nuraini", "kampus": "UBSI KAMPUS CIKAMPEK", "total": 73, "fresh": 0, "noResp": 9, "warm": 17, "minat": 17, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 47, "convPct": 64.4, "avgScore": 79.7}, {"name": "Mahesa Ikni Rhamadan", "kampus": "UBSI KAMPUS CIKAMPEK", "total": 45, "fresh": 0, "noResp": 0, "warm": 12, "minat": 12, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 33, "convPct": 73.3, "avgScore": 88.0}, {"name": "ahmad fauzi", "kampus": "UBSI KAMPUS SOLO", "total": 35, "fresh": 0, "noResp": 0, "warm": 7, "minat": 3, "ragu": 4, "t2Unlabeled": 0, "menolak": 2, "closing": 26, "convPct": 74.3, "avgScore": 85.6}, {"name": "Irena Qurain Deskadika Djunaedi", "kampus": "UBSI KAMPUS CIKAMPEK", "total": 37, "fresh": 0, "noResp": 1, "warm": 12, "minat": 12, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 24, "convPct": 64.9, "avgScore": 83.2}, {"name": "Sri Mulyani", "kampus": "UBSI KAMPUS TASIKMALAYA", "total": 45, "fresh": 0, "noResp": 19, "warm": 7, "minat": 1, "ragu": 6, "t2Unlabeled": 0, "menolak": 5, "closing": 14, "convPct": 31.1, "avgScore": 48.7}, {"name": "Admin UBSI Yogyakarta", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 73, "fresh": 0, "noResp": 54, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 6, "closing": 13, "convPct": 17.8, "avgScore": 33.0}, {"name": "Dian Ardiansyah", "kampus": "UBSI KAMPUS CIBITUNG", "total": 22, "fresh": 0, "noResp": 0, "warm": 12, "minat": 9, "ragu": 3, "t2Unlabeled": 0, "menolak": 1, "closing": 9, "convPct": 40.9, "avgScore": 71.1}, {"name": "BSI Fatmawati", "kampus": "UBSI KAMPUS FATMAWATI", "total": 37, "fresh": 0, "noResp": 20, "warm": 10, "minat": 9, "ragu": 1, "t2Unlabeled": 0, "menolak": 1, "closing": 6, "convPct": 16.2, "avgScore": 42.0}, {"name": "nasiela Azira", "kampus": "UBSI KAMPUS KALIABANG", "total": 152, "fresh": 126, "noResp": 12, "warm": 7, "minat": 5, "ragu": 2, "t2Unlabeled": 0, "menolak": 1, "closing": 6, "convPct": 3.9, "avgScore": 8.1}, {"name": "Yunita", "kampus": "UBSI KAMPUS CENGKARENG", "total": 6, "fresh": 0, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 6, "convPct": 100.0, "avgScore": 100.0}, {"name": "Annisa Alfianatu Syahadah", "kampus": "UBSI KAMPUS CIKAMPEK", "total": 34, "fresh": 0, "noResp": 4, "warm": 26, "minat": 26, "ragu": 0, "t2Unlabeled": 0, "menolak": 1, "closing": 3, "convPct": 8.8, "avgScore": 53.4}, {"name": "Dipa", "kampus": "UBSI KAMPUS CIKARANG", "total": 24, "fresh": 0, "noResp": 14, "warm": 4, "minat": 2, "ragu": 2, "t2Unlabeled": 0, "menolak": 3, "closing": 3, "convPct": 12.5, "avgScore": 34.0}, {"name": "Devi Novita", "kampus": "UBSI KAMPUS TASIKMALAYA", "total": 6, "fresh": 0, "noResp": 0, "warm": 3, "minat": 0, "ragu": 3, "t2Unlabeled": 0, "menolak": 0, "closing": 3, "convPct": 50.0, "avgScore": 77.5}, {"name": "Haerul Fatah", "kampus": "UBSI KAMPUS TASIKMALAYA", "total": 19, "fresh": 0, "noResp": 0, "warm": 17, "minat": 7, "ragu": 10, "t2Unlabeled": 0, "menolak": 0, "closing": 2, "convPct": 10.5, "avgScore": 59.7}, {"name": "Agung Baitul Hikmah", "kampus": "UBSI KAMPUS TASIKMALAYA", "total": 4, "fresh": 0, "noResp": 0, "warm": 2, "minat": 1, "ragu": 1, "t2Unlabeled": 0, "menolak": 0, "closing": 2, "convPct": 50.0, "avgScore": 77.5}, {"name": "Siti Fatimah", "kampus": "UBSI KAMPUS TASIKMALAYA", "total": 4, "fresh": 0, "noResp": 1, "warm": 1, "minat": 1, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 2, "convPct": 50.0, "avgScore": 68.8}, {"name": "Galuh Tiasari Estiningtyas", "kampus": "UNM KAMPUS RAWAMANGUN", "total": 171, "fresh": 148, "noResp": 15, "warm": 6, "minat": 6, "ragu": 0, "t2Unlabeled": 0, "menolak": 1, "closing": 1, "convPct": 0.6, "avgScore": 4.3}, {"name": "Arwircha genvil", "kampus": "UBSI KAMPUS JATIWARINGIN", "total": 464, "fresh": 0, "noResp": 448, "warm": 2, "minat": 0, "ragu": 2, "t2Unlabeled": 0, "menolak": 13, "closing": 1, "convPct": 0.2, "avgScore": 19.9}, {"name": "Amelia Rahmasari", "kampus": "UNM KAMPUS RAWAMANGUN", "total": 95, "fresh": 72, "noResp": 16, "warm": 1, "minat": 0, "ragu": 1, "t2Unlabeled": 0, "menolak": 5, "closing": 1, "convPct": 1.1, "avgScore": 5.3}, {"name": "putri fiky 'amalina", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 18, "noResp": 47, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 1, "closing": 1, "convPct": 1.5, "avgScore": 15.6}, {"name": "resya sahroni putri", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 19, "noResp": 46, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 1, "closing": 1, "convPct": 1.5, "avgScore": 15.3}, {"name": "Fahri Akbar", "kampus": "UBSI KAMPUS CIKARANG", "total": 22, "fresh": 0, "noResp": 19, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 2, "closing": 1, "convPct": 4.5, "avgScore": 22.3}, {"name": "Muhamad Tabrani", "kampus": "UBSI KAMPUS KALIABANG", "total": 2, "fresh": 0, "noResp": 1, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 1, "convPct": 50.0, "avgScore": 60.0}, {"name": "Ahmad Fauzi", "kampus": "UBSI KAMPUS JATIWARINGIN", "total": 1, "fresh": 0, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 1, "convPct": 100.0, "avgScore": 100.0}, {"name": "Nani Agustina", "kampus": "UBSI KAMPUS JATIWARINGIN", "total": 483, "fresh": 0, "noResp": 459, "warm": 8, "minat": 0, "ragu": 8, "t2Unlabeled": 0, "menolak": 16, "closing": 0, "convPct": 0.0, "avgScore": 20.1}, {"name": "Maruloh", "kampus": "UNM KAMPUS RAWAMANGUN", "total": 16, "fresh": 8, "noResp": 2, "warm": 6, "minat": 4, "ragu": 2, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 23.1}, {"name": "Kampus Tangerang", "kampus": "UBSI KAMPUS TANGERANG", "total": 79, "fresh": 65, "noResp": 7, "warm": 4, "minat": 1, "ragu": 3, "t2Unlabeled": 0, "menolak": 3, "closing": 0, "convPct": 0.0, "avgScore": 4.7}, {"name": "musthofa yudhistira", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 0, "noResp": 53, "warm": 3, "minat": 0, "ragu": 0, "t2Unlabeled": 3, "menolak": 11, "closing": 0, "convPct": 0.0, "avgScore": 19.1}, {"name": "Astri Sumarti", "kampus": "UBSI KAMPUS JATIWARINGIN", "total": 255, "fresh": 0, "noResp": 249, "warm": 2, "minat": 0, "ragu": 2, "t2Unlabeled": 0, "menolak": 4, "closing": 0, "convPct": 0.0, "avgScore": 20.0}, {"name": "brigida sasta leota febriana", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 22, "noResp": 44, "warm": 1, "minat": 0, "ragu": 0, "t2Unlabeled": 1, "menolak": 1, "closing": 0, "convPct": 0.0, "avgScore": 13.8}, {"name": "putri yuli astuti", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 64, "fresh": 16, "noResp": 46, "warm": 1, "minat": 0, "ragu": 0, "t2Unlabeled": 1, "menolak": 1, "closing": 0, "convPct": 0.0, "avgScore": 15.3}, {"name": "Eri Bayu Pratama", "kampus": "UBSI KAMPUS PONTIANAK", "total": 1, "fresh": 0, "noResp": 0, "warm": 1, "minat": 1, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 55.0}, {"name": "Adit", "kampus": "UNM KAMPUS RAWAMANGUN", "total": 94, "fresh": 94, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 0.0}, {"name": "Harnojo", "kampus": "UNM KAMPUS RAWAMANGUN", "total": 94, "fresh": 94, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 0.0}, {"name": "luksti nandiya sari", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 92, "fresh": 23, "noResp": 64, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 5, "closing": 0, "convPct": 0.0, "avgScore": 14.2}, {"name": "adellima putri", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 69, "fresh": 21, "noResp": 45, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 3, "closing": 0, "convPct": 0.0, "avgScore": 13.3}, {"name": "fatkan ardana", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 69, "fresh": 20, "noResp": 47, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 2, "closing": 0, "convPct": 0.0, "avgScore": 13.8}, {"name": "firda azzukhruf", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 69, "fresh": 9, "noResp": 55, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 5, "closing": 0, "convPct": 0.0, "avgScore": 16.3}, {"name": "Aisyah Atha Khalila", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 21, "noResp": 43, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 4, "closing": 0, "convPct": 0.0, "avgScore": 12.9}, {"name": "affah della puspita", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 22, "noResp": 41, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 5, "closing": 0, "convPct": 0.0, "avgScore": 12.4}, {"name": "amelia latifatus zahra", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 19, "noResp": 49, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 14.4}, {"name": "angeli diva maharani", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 22, "noResp": 43, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 3, "closing": 0, "convPct": 0.0, "avgScore": 12.9}, {"name": "apriliani nur yasinta", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 10, "noResp": 42, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 16, "closing": 0, "convPct": 0.0, "avgScore": 13.5}, {"name": "ardin fathan fawwaz ahabba", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 0, "noResp": 55, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 13, "closing": 0, "convPct": 0.0, "avgScore": 17.1}, {"name": "aura ayunda putri", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 20, "noResp": 38, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 10, "closing": 0, "convPct": 0.0, "avgScore": 11.9}, {"name": "dwi sinta rahmadina", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 21, "noResp": 44, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 3, "closing": 0, "convPct": 0.0, "avgScore": 13.2}, {"name": "einggal pangestu", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 22, "noResp": 44, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 2, "closing": 0, "convPct": 0.0, "avgScore": 13.1}, {"name": "faisal yusuf", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 26, "noResp": 39, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 3, "closing": 0, "convPct": 0.0, "avgScore": 11.7}, {"name": "layla pasha hafidz", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 15, "noResp": 43, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 10, "closing": 0, "convPct": 0.0, "avgScore": 13.4}, {"name": "lintang fajar agustin", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 68, "fresh": 25, "noResp": 38, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 5, "closing": 0, "convPct": 0.0, "avgScore": 11.5}, {"name": "Prida Anindita", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 0, "noResp": 63, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 4, "closing": 0, "convPct": 0.0, "avgScore": 19.1}, {"name": "monica yessica puspitasari", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 23, "noResp": 38, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 6, "closing": 0, "convPct": 0.0, "avgScore": 11.8}, {"name": "muhamad nazul khoir", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 20, "noResp": 46, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 1, "closing": 0, "convPct": 0.0, "avgScore": 13.8}, {"name": "muhammad lutfhi prasetyo utomo", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 20, "noResp": 45, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 2, "closing": 0, "convPct": 0.0, "avgScore": 13.6}, {"name": "novalia prihatanti", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 20, "noResp": 45, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 2, "closing": 0, "convPct": 0.0, "avgScore": 13.6}, {"name": "rifatun nisa", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 17, "noResp": 48, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 2, "closing": 0, "convPct": 0.0, "avgScore": 14.5}, {"name": "zahra salsabila afifah", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 67, "fresh": 20, "noResp": 36, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 11, "closing": 0, "convPct": 0.0, "avgScore": 11.6}, {"name": "Anisa Julia Utami", "kampus": "UBSI KAMPUS SUKABUMI", "total": 66, "fresh": 66, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 0.0}, {"name": "satriyo kautsar inas respati", "kampus": "UBSI KAMPUS YOGYAKARTA", "total": 66, "fresh": 22, "noResp": 43, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 1, "closing": 0, "convPct": 0.0, "avgScore": 13.1}, {"name": "Mereanus Lase", "kampus": "UBSI KAMPUS CIKARANG", "total": 21, "fresh": 0, "noResp": 21, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 20.0}, {"name": "Anita Rahmawati", "kampus": "UBSI KAMPUS SUKABUMI", "total": 6, "fresh": 6, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 0.0}, {"name": "Dini", "kampus": "UBSI KAMPUS SUKABUMI", "total": 6, "fresh": 6, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 0.0}, {"name": "Shindi", "kampus": "UBSI KAMPUS SUKABUMI", "total": 6, "fresh": 6, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 0.0}, {"name": "Dame Sihite", "kampus": "UBSI KAMPUS CIKARANG", "total": 2, "fresh": 2, "noResp": 0, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 0.0}, {"name": "Febriyanti", "kampus": "UBSI KAMPUS CIKARANG", "total": 2, "fresh": 0, "noResp": 2, "warm": 0, "minat": 0, "ragu": 0, "t2Unlabeled": 0, "menolak": 0, "closing": 0, "convPct": 0.0, "avgScore": 20.0}]
JSON_AGENTS;

$allCrmCampuses = json_decode($allCrmCampusesJson, true) ?: [];
$allCrmAgents = json_decode($allCrmAgentsJson, true) ?: [];

$normalizeCampusKey = static function (string $name): string {
    $n = strtoupper(trim($name));
    $n = preg_replace('/^(UBSI|UNM)\s+KAMPUS\s+/i', '', $n);
    $n = preg_replace('/^KAMPUS\s+/i', '', $n);
    return trim((string) $n);
};

$selectedCampusNo = 1;
$matchedCampus = null;

$targetSearchName = ($canViewAllCrmCampuses && $requestedCampusParam !== '')
    ? $requestedCampusParam
    : $userCampusFullName;

$targetKey = $normalizeCampusKey($targetSearchName);

foreach ($allCrmCampuses as $cRow) {
    $cName = strtoupper(trim((string) ($cRow['kampus'] ?? '')));
    if ($cName === $targetSearchName || $normalizeCampusKey($cName) === $targetKey) {
        $matchedCampus = $cRow;
        $selectedCampusNo = (int) $cRow['no'];
        break;
    }
}

if (! $canViewAllCrmCampuses) {
    if ($matchedCampus === null) {
        $groupPrefix = str_starts_with($userCampusFullName, 'UNM') ? 'UNM' : 'UBSI';
        $matchedCampus = [
            'no' => 1,
            'kampus' => $userCampusFullName,
            'group' => $groupPrefix,
            'total' => 0,
            'assigned' => 0,
            'unassigned' => 0,
            'fresh' => 0,
            'freshAssigned' => 0,
            'freshUnassigned' => 0,
            'noResp' => 0,
            'minat' => 0,
            'ragu' => 0,
            'menolak' => 0,
            'closing' => 0,
            't2Unlabeled' => 0,
            'anomUnassigned' => 0,
            't1' => 0,
            't2' => 0,
            't3' => 0,
            'avgScore' => 0.0,
            'fuCount' => 0,
            'logCount' => 0,
            'batchCount' => 0,
            'schoolCount' => 0,
            'missingSchool' => 0,
            'events' => [],
            'agents' => [],
            'topSchools' => [],
            'reasons' => [],
        ];
        $selectedCampusNo = 1;
    }
    $visibleCrmCampuses = [$matchedCampus];
    $matchedCampusNameUpper = strtoupper(trim((string) $matchedCampus['kampus']));
    $visibleCrmAgents = array_values(array_filter($allCrmAgents, static function (array $ag) use ($matchedCampusNameUpper): bool {
        return strtoupper(trim((string) ($ag['kampus'] ?? ''))) === $matchedCampusNameUpper;
    }));
} else {
    $visibleCrmCampuses = $allCrmCampuses;
    $visibleCrmAgents = $allCrmAgents;
}

$initialActiveTab = $canViewAllCrmCampuses
    ? ($requestedCampusParam !== '' ? 'campus-scoped' : 'admin-overview')
    : 'campus-scoped';
?>
<script src="<?= base_url('assets/js/lib/svg-chart-engine.js?v=' . @filemtime(FCPATH . 'assets/js/lib/svg-chart-engine.js')) ?>"></script>
<style>
  .crm-report-wrapper {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    color: #0f172a;
    width: 100%;
    max-width: 100%;
  }
  .crm-report-wrapper * {
    box-sizing: border-box;
  }
  .crm-report-wrapper canvas {
    display: none !important;
  }
  .crm-custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
  }
  .crm-custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 9999px;
  }
  .crm-custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 9999px;
  }
  .crm-custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }
  /* Explicit responsive layout rules immune to CSS Cascade Layer conflicts */
  .crm-header-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .crm-role-banner-row {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .crm-kpi-grid-6 {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 0.875rem;
  }
  .crm-rec-grid-2 {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1rem;
  }
  .crm-charts-12 {
    display: grid;
    grid-template-columns: repeat(1, minmax(0, 1fr));
    gap: 1.5rem;
  }
  .crm-col-7,
  .crm-col-5,
  .crm-col-12 {
    grid-column: span 1 / span 1;
    min-width: 0;
  }
  .crm-stage-grid-3 {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.5rem;
  }
  .crm-selector-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  .crm-toolbar-row {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
  }
  @media (min-width: 640px) {
    .crm-kpi-grid-6 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .crm-selector-row {
      flex-direction: row;
      align-items: flex-end;
    }
  }
  @media (min-width: 768px) {
    .crm-kpi-grid-6 {
      grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .crm-rec-grid-2 {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (min-width: 1024px) {
    .crm-header-row {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
    .crm-role-banner-row {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
    .crm-toolbar-row {
      flex-direction: row;
      align-items: center;
      justify-content: space-between;
    }
    .crm-kpi-grid-6 {
      grid-template-columns: repeat(6, minmax(0, 1fr));
    }
    .crm-charts-12 {
      grid-template-columns: repeat(12, minmax(0, 1fr));
    }
    .crm-col-7 {
      grid-column: span 7 / span 7;
    }
    .crm-col-5 {
      grid-column: span 5 / span 5;
    }
    .crm-col-12 {
      grid-column: span 12 / span 12;
    }
  }
</style>

<div class="crm-report-wrapper space-y-6 pb-8">

  <!-- Top Header Card -->
  <div class="bg-slate-900 text-white rounded-2xl border border-slate-800 shadow-md p-4 sm:p-5">
    <div class="crm-header-row">
      <div class="flex items-start sm:items-center gap-3">
        <div class="w-11 h-11 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0 shadow-inner">
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h1 class="text-lg sm:text-xl font-bold tracking-tight text-white">Dashboard &amp; Rekomendasi Hasil CRM Kampus</h1>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">Ekspor: 29 Oct 2026</span>
            <?php if ($canViewAllCrmCampuses): ?>
              <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">8.642 Prospek • 20 Kampus Pelaksana</span>
            <?php else: ?>
              <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">Kampus: <?= esc($matchedCampus['kampus'] ?? $userCampusFullName) ?></span>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-400 mt-1">
            <?php if ($canViewAllCrmCampuses): ?>
              Laporan Khusus CRM (Terpisah dari Strategi DMER): <strong class="text-indigo-300">View Admin (Semua Kampus)</strong> vs <strong class="text-emerald-300">View Masing-Masing Kampus Pelaksana</strong>
            <?php else: ?>
              Laporan &amp; Rekomendasi Operasional CRM Khusus <strong class="text-emerald-300"><?= esc($matchedCampus['kampus'] ?? $userCampusFullName) ?></strong> (Data kampus lain disembunyikan sesuai hak akses)
            <?php endif; ?>
          </p>
        </div>
      </div>

      <?php if ($canViewAllCrmCampuses): ?>
        <!-- Navigation Tabs (Admin Only) -->
        <nav class="flex flex-wrap items-center gap-1.5 bg-slate-800/90 p-1.5 rounded-xl border border-slate-700">
          <button id="tabBtn-admin-overview" type="button" data-tab="admin-overview" class="nav-tab-btn px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all bg-indigo-600 text-white shadow">
            🔐 View Admin: Rekomendasi (Semua Kampus)
          </button>
          <button id="tabBtn-admin-matrix" type="button" data-tab="admin-matrix" class="nav-tab-btn px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60">
            📊 View Admin: Matriks 20 Kampus &amp; Agent
          </button>
          <button id="tabBtn-campus-scoped" type="button" data-tab="campus-scoped" class="nav-tab-btn px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60">
            🏢 View Kampus Pelaksana (Per Kampus)
          </button>
          <button id="tabBtn-access-guide" type="button" data-tab="access-guide" class="nav-tab-btn px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60">
            🛡️ Skema Hak Akses CRM
          </button>
          <a href="<?= site_url('crm') ?>" class="px-3 py-1.5 rounded-lg text-xs sm:text-sm font-bold transition-all bg-emerald-500 hover:bg-emerald-600 text-slate-950 shadow">
            💬 Buka CRM WhatsApp &amp; AI Workspace &rarr;
          </a>
        </nav>
      <?php else: ?>
        <div class="flex flex-wrap items-center gap-2">
          <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-600/20 text-emerald-300 border border-emerald-500/30">
            🔒 Akses Terkunci: <?= esc($matchedCampus['kampus'] ?? $userCampusFullName) ?>
          </span>
          <a href="<?= site_url('crm') ?>" class="px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-slate-950 transition shadow">
            💬 Buka CRM WhatsApp &amp; AI Workspace &rarr;
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($canViewAllCrmCampuses): ?>
  <!-- =================================================================== -->
  <!-- TAB 1: VIEW ADMIN PUSAT — REKOMENDASI & ANALITIK SEMUA 20 KAMPUS    -->
  <!-- =================================================================== -->
  <section id="view-admin-overview" class="space-y-6">
    <!-- Role Banner -->
    <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white rounded-2xl p-5 shadow-md border border-indigo-500/30">
      <div class="crm-role-banner-row">
        <div class="space-y-1.5">
          <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 text-xs font-extrabold uppercase tracking-wider rounded-full bg-indigo-500 text-white">Mode Akses: Admin Pusat (Semua Kampus)</span>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">8.642 Siswa • 20 Kampus • 46 Event • 68 Agent CRM</span>
          </div>
          <h2 class="text-base sm:text-lg font-extrabold text-white">Rekomendasi Strategis &amp; Analitik Lintas Kampus (Khusus Admin Pusat)</h2>
          <p class="text-xs text-slate-300 max-w-4xl leading-relaxed">
            Tampilan ini <strong>hanya dapat dilihat oleh Admin Pusat</strong> untuk memonitor performa konversi seluruh 20 kampus di database CRM, mengintervensi tumpukan data <em>Belum diassign</em> (3.770 siswa) dan <em>Fresh tertahan di Agent</em> (1.207 siswa), serta mengevaluasi efektivitas kanal event dan kinerja 68 Agent CRM secara nasional.
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2.5 shrink-0">
          <button id="jumpToCampusViewBtn" type="button" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-600 text-slate-950 transition shadow-sm flex items-center gap-1.5">
            <span>🏢 Buka View Khusus Kampus Pelaksana</span>
            <span>&rarr;</span>
          </button>
          <button id="exportAdminCsvBtn1" type="button" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-white/10 hover:bg-white/20 text-white border border-white/20 transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            <span>Unduh Laporan Admin (CSV)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 6 National Macro KPI Cards -->
    <div class="crm-kpi-grid-6">
      <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Prospek CRM</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-indigo-50 text-indigo-700">20 Kampus</span>
        </div>
        <div class="text-2xl font-extrabold text-slate-900 mt-1.5">8.642</div>
        <div class="text-[11px] text-slate-500 mt-1"><strong>46 Event</strong> • <strong>79 Batch Upload</strong> (18 UBSI &amp; 2 UNM)</div>
      </div>

      <div class="bg-rose-50/70 rounded-2xl p-4 border border-rose-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800">Belum Di-Assign</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-rose-200 text-rose-900">43,6% Total</span>
        </div>
        <div class="text-2xl font-extrabold text-rose-700 mt-1.5">3.770</div>
        <div class="text-[11px] text-rose-800 mt-1"><strong>3.721 Fresh</strong> + 49 sudah berlabel tanpa nama agent</div>
      </div>

      <div class="bg-amber-50/70 rounded-2xl p-4 border border-amber-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-amber-900">Fresh Di-Assign</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-amber-200 text-amber-900">14,0% Total</span>
        </div>
        <div class="text-2xl font-extrabold text-amber-700 mt-1.5">1.207</div>
        <div class="text-[11px] text-amber-800 mt-1">Sudah ada Agent tapi belum dihubungi (Total Fresh: <strong>4.928</strong>)</div>
      </div>

      <div class="bg-emerald-50/70 rounded-2xl p-4 border border-emerald-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-900">Total Closing (T3)</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-200 text-emerald-900">Skor 100</span>
        </div>
        <div class="text-2xl font-extrabold text-emerald-700 mt-1.5">514 <span class="text-xs font-bold">(5,95%)</span></div>
        <div class="text-[11px] text-emerald-800 mt-1"><strong>13,84%</strong> dari 3.714 prospek yang sudah difollow-up</div>
      </div>

      <div class="bg-sky-50/70 rounded-2xl p-4 border border-sky-200 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-sky-900">Prospek Hangat</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-sky-200 text-sky-900">Skor 55</span>
        </div>
        <div class="text-2xl font-extrabold text-sky-700 mt-1.5">219 <span class="text-xs font-bold">(2,53%)</span></div>
        <div class="text-[11px] text-sky-800 mt-1"><strong>158 Minat</strong> • <strong>56 Ragu</strong> • <strong>5 T2 Belum Berlabel</strong></div>
      </div>

      <div class="bg-slate-100 rounded-2xl p-4 border border-slate-300 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700">Tidak Respon / Tolak</span>
          <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-slate-200 text-slate-800">34,5% Total</span>
        </div>
        <div class="text-2xl font-extrabold text-slate-800 mt-1.5">2.775 <span class="text-xs font-semibold text-rose-700">/ 206</span></div>
        <div class="text-[11px] text-slate-600 mt-1">91,5% Tidak Merespon ada di Yogyakarta &amp; Jatiwaringin</div>
      </div>
    </div>

    <!-- 6 Strategic Admin Recommendations -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="crm-toolbar-row pb-4 border-b border-slate-100">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-base font-extrabold text-slate-900">6 Rekomendasi Strategis Lintas Kampus (Khusus Admin Pusat)</h3>
            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-800">Evaluasi Nasional 20 Kampus</span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">Filter berdasarkan fokus kebijakan atau klik tombol pada kartu rekomendasi untuk membuka daftar kampus terdampak di Matriks Admin.</p>
        </div>
        <div class="flex flex-wrap items-center gap-1.5">
          <button type="button" data-rec-cat="ALL" class="admin-rec-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm">Semua (6)</button>
          <button type="button" data-rec-cat="SLA" class="admin-rec-filter-btn px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200">Distribusi &amp; SLA Agent (2)</button>
          <button type="button" data-rec-cat="CONV" class="admin-rec-filter-btn px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200">Konversi &amp; Re-Engagement (3)</button>
          <button type="button" data-rec-cat="DATA" class="admin-rec-filter-btn px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200">Kualitas &amp; Sistem Data (1)</button>
        </div>
      </div>

      <div id="adminStrategicRecGrid" class="crm-rec-grid-2 pt-4">
        <!-- Populated by JS -->
      </div>
    </div>

    <!-- Admin Cross-Campus Analytics Charts -->
    <div class="crm-charts-12">
      <div class="crm-col-7 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
          <div>
            <h3 class="text-sm font-bold text-slate-900">Komparasi Funnel &amp; Status Hasil CRM Lintas 20 Kampus</h3>
            <p class="text-xs text-slate-500">Perbandingan volume prospek berdasarkan status tindak lanjut per kampus pelaksana</p>
          </div>
          <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-lg">
            <button id="adminChartModeVolBtn" type="button" class="px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-slate-900 shadow-sm">Jumlah Siswa</button>
            <button id="adminChartModePctBtn" type="button" class="px-2.5 py-1 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900">Komposisi %</button>
          </div>
        </div>
        <div class="relative h-80 w-full">
          <canvas id="crmAdminCampusChart"></canvas>
        </div>
      </div>

      <div class="crm-col-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between space-y-4">
        <div>
          <h3 class="text-sm font-bold text-slate-900">Efektivitas 9 Kategori Event terhadap Konversi Closing</h3>
          <p class="text-xs text-slate-500 mb-2">Kontras tingkat konversi Aplikasi PMB (37,9%) &amp; Non Akademik (16,8%) dibanding Edufair (0,17%)</p>
          <div class="relative h-48 w-full">
            <canvas id="crmAdminCategoryChart"></canvas>
          </div>
        </div>
        <div class="pt-3 border-t border-slate-100">
          <h4 class="text-xs font-bold text-slate-800 mb-2">Peta Alasan Penolakan &amp; Kendala Nomor WA (91 Kasus Berlabel)</h4>
          <div class="relative h-36 w-full">
            <canvas id="crmAdminReasonChart"></canvas>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- =================================================================== -->
  <!-- TAB 2: VIEW ADMIN PUSAT — MATRIKS 20 KAMPUS & LEADERBOARD 68 AGENT  -->
  <!-- =================================================================== -->
  <section id="view-admin-matrix" class="hidden space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="crm-toolbar-row pb-4 border-b border-slate-100">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h2 class="text-base font-extrabold text-slate-900">Tabel Pengawasan Lintas 20 Kampus Pelaksana (Khusus Admin Pusat)</h2>
            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-indigo-100 text-indigo-800">8.642 Prospek</span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">Klik baris kampus mana saja untuk melihat simulasi tampilan internal yang hanya dapat diakses oleh kampus pelaksana tersebut.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <input id="adminCrmSearchInput" type="text" placeholder="Cari nama kampus..." class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium focus:border-indigo-600 focus:outline-none w-44" />
          <select id="adminCrmClusterFilter" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold bg-white focus:border-indigo-600 focus:outline-none">
            <option value="ALL">Semua 20 Kampus Pelaksana</option>
            <option value="URGENT_UNASSIGNED">Darurat: &gt;80% Belum Di-Assign (11 Kampus)</option>
            <option value="STALLED_ASSIGNED">Stagnan: Sudah Assign Tapi Masih Fresh (4 Kampus)</option>
            <option value="HIGH_NORESPONSE">Bottleneck: Tidak Merespon Tinggi (2 Kampus)</option>
            <option value="BENCHMARK_CLOSING">Benchmark: Konversi Closing Tinggi (3 Kampus)</option>
          </select>
          <select id="adminCrmSortSelect" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium bg-white focus:border-indigo-600 focus:outline-none">
            <option value="TOTAL_DESC">Urutkan: Total Prospek Terbanyak</option>
            <option value="CLOSING_DESC">Urutkan: Closing Tertinggi</option>
            <option value="UNASSIGNED_DESC">Urutkan: Belum Di-Assign Terbanyak</option>
            <option value="FRESH_DESC">Urutkan: Total Fresh Terbanyak</option>
            <option value="SCORE_DESC">Urutkan: Rata-rata Skor Tertinggi</option>
            <option value="NAME_ASC">Urutkan: Nama Kampus (A-Z)</option>
          </select>
          <button id="exportAdminCsvBtn2" type="button" class="px-3 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            <span>Unduh CSV</span>
          </button>
        </div>
      </div>

      <div class="mt-4 overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200">
        <table class="w-full text-left border-collapse text-xs">
          <thead>
            <tr class="bg-slate-900 text-white font-semibold uppercase tracking-wider">
              <th class="py-3 px-2.5 text-center">No</th>
              <th class="py-3 px-3">Kampus Pelaksana</th>
              <th class="py-3 px-2.5 text-center">Total Prospek</th>
              <th class="py-3 px-2.5 text-center bg-rose-950/60">Belum Assign</th>
              <th class="py-3 px-2.5 text-center bg-amber-950/60">Fresh (Total)</th>
              <th class="py-3 px-2.5 text-center">Tidak Respon</th>
              <th class="py-3 px-2.5 text-center bg-sky-950/60">Minat / Ragu</th>
              <th class="py-3 px-2.5 text-center">Menolak</th>
              <th class="py-3 px-2.5 text-center bg-emerald-950/60">Closing (T3)</th>
              <th class="py-3 px-2 text-center">Skor</th>
              <th class="py-3 px-3">Diagnosis &amp; Instruksi Admin Pusat</th>
              <th class="py-3 px-2.5 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="adminCrmTableBody" class="divide-y divide-slate-200 bg-white">
            <!-- Populated by JS -->
          </tbody>
        </table>
      </div>
    </div>

    <!-- Global 68 Agents Audit & Leaderboard Table (Admin Only) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="crm-toolbar-row pb-4 border-b border-slate-100">
        <div>
          <h3 class="text-base font-extrabold text-slate-900">Audit &amp; Leaderboard 68 Agent CRM Lintas Kampus (Hanya Dilihat Admin Pusat)</h3>
          <p class="text-xs text-slate-500">Memantau produktivitas seluruh Agent CRM di 14 kampus yang sudah menugaskan agent (6 kampus lainnya belum memiliki agent sama sekali).</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <input id="globalAgentSearchInput" type="text" placeholder="Cari nama agent / kampus..." class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-medium focus:border-indigo-600 focus:outline-none w-48" />
          <select id="globalAgentFilterSelect" class="rounded-xl border border-slate-300 px-3 py-2 text-xs font-semibold bg-white focus:border-indigo-600 focus:outline-none">
            <option value="ALL">Semua 68 Agent CRM</option>
            <option value="TOP_CLOSING">Pencetak Closing (&ge; 1 Closing)</option>
            <option value="HAS_WARM">Memiliki Prospek Minat/Ragu (&ge; 1)</option>
            <option value="STALLED_FRESH">Menumpuk Data Fresh (&ge; 10 Fresh)</option>
            <option value="ZERO_PROGRESS">Progres 0% (100% Data Masih Fresh)</option>
          </select>
        </div>
      </div>

      <div class="mt-4 overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200 max-h-96">
        <table class="w-full text-left border-collapse text-xs">
          <thead class="sticky top-0 bg-slate-800 text-white font-semibold uppercase">
            <tr>
              <th class="py-2.5 px-3 text-center">Rank</th>
              <th class="py-2.5 px-3">Nama Agent CRM</th>
              <th class="py-2.5 px-3">Kampus Pelaksana</th>
              <th class="py-2.5 px-2 text-center">Total Lead</th>
              <th class="py-2.5 px-2 text-center">Masih Fresh</th>
              <th class="py-2.5 px-2 text-center">Tidak Respon</th>
              <th class="py-2.5 px-2 text-center">Minat / Ragu</th>
              <th class="py-2.5 px-2 text-center">Menolak</th>
              <th class="py-2.5 px-2 text-center">Closing</th>
              <th class="py-2.5 px-2 text-center">% Closing</th>
              <th class="py-2.5 px-3">Status Audit Admin</th>
            </tr>
          </thead>
          <tbody id="globalAgentTableBody" class="divide-y divide-slate-200 bg-white">
            <!-- Populated by JS -->
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- =================================================================== -->
  <!-- TAB 3: VIEW MASING-MASING KAMPUS PELAKSANA (ROLE-SCOPED VIEW)       -->
  <!-- =================================================================== -->
  <section id="view-campus-scoped" class="<?= $canViewAllCrmCampuses ? 'hidden ' : '' ?>space-y-6">
    <!-- Top Role Isolation & Campus Selector Bar -->
    <div class="bg-white rounded-2xl shadow-sm border-2 border-emerald-500/40 p-4 sm:p-5">
      <div class="crm-charts-12 items-center">
        <div class="crm-col-7 space-y-3">
          <div class="flex flex-wrap items-center gap-2">
            <span class="px-2.5 py-0.5 text-xs font-extrabold uppercase tracking-wider rounded-full bg-emerald-600 text-white">
              🏢 Mode Akses: Kampus Pelaksana (Terisolasi Per Kampus)
            </span>
            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700 border border-slate-300">
              🔒 Data Kampus Lainnya Disembunyikan
            </span>
          </div>

          <?php if ($canViewAllCrmCampuses): ?>
            <div class="crm-selector-row">
              <div class="flex-1">
                <label for="crmCampusSelect" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  Pilih Akun Kampus Pelaksana (Simulasi View Kampus):
                </label>
                <div class="flex items-center gap-1.5">
                  <button id="crmPrevCampusBtn" type="button" class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition" title="Kampus Sebelumnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                  </button>
                  <select id="crmCampusSelect" class="w-full rounded-xl border border-emerald-500 bg-emerald-50/30 px-3.5 py-2.5 text-sm font-extrabold text-slate-900 shadow-sm focus:border-emerald-600 focus:outline-none">
                  </select>
                  <button id="crmNextCampusBtn" type="button" class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-slate-700 transition" title="Kampus Berikutnya">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                  </button>
                </div>
              </div>

              <div class="sm:w-56">
                <label for="crmCampusQuickFilter" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">
                  Filter Klaster Kampus:
                </label>
                <select id="crmCampusQuickFilter" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-xs font-semibold text-slate-700">
                  <option value="ALL">Semua 20 Kampus CRM</option>
                  <option value="URGENT_UNASSIGNED">Darurat Belum Assign (11)</option>
                  <option value="STALLED_ASSIGNED">Fresh Tertahan di Agent (4)</option>
                  <option value="HIGH_NORESPONSE">Tidak Merespon Tinggi (2)</option>
                  <option value="BENCHMARK_CLOSING">Konversi Closing Tinggi (3)</option>
                </select>
              </div>
            </div>
          <?php else: ?>
            <div class="p-3.5 rounded-xl bg-emerald-50/60 border border-emerald-300 flex flex-wrap items-center justify-between gap-2">
              <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-800">Kampus Pelaksana Aktif</div>
                <div class="text-base sm:text-lg font-extrabold text-slate-900"><?= esc($matchedCampus['kampus'] ?? $userCampusFullName) ?></div>
              </div>
              <span class="px-3 py-1 rounded-lg bg-emerald-600 text-white text-xs font-bold">
                ✓ Terautentikasi Sesuai Kampus Anda
              </span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Right: Campus Status & Role Boundary Box -->
        <div class="crm-col-5 bg-slate-900 text-white rounded-xl p-4 border border-slate-800 space-y-2">
          <div class="flex items-center justify-between">
            <span id="crmCampusClusterBadge" class="px-2.5 py-0.5 text-xs font-extrabold rounded-full bg-amber-400 text-slate-950">Status Kampus</span>
            <?php if ($canViewAllCrmCampuses): ?>
              <button id="backToAdminViewBtn" type="button" class="text-xs font-semibold text-indigo-300 hover:text-white underline">
                &larr; Kembali ke View Admin Pusat
              </button>
            <?php endif; ?>
          </div>
          <p class="text-xs text-slate-300 leading-relaxed">
            Tampilan ini <strong>hanya menampilkan data internal kampus <?= $canViewAllCrmCampuses ? 'yang dipilih' : 'Anda' ?></strong>: progres funnel kampus, daftar event lokal, beban kerja agent internal, asal sekolah pendaftar, dan daftar tugas harian kampus.
          </p>
          <div class="pt-1.5 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
            <span id="crmCampusMetaSubtext">12 Event • 30 Agent Aktif</span>
            <span class="text-emerald-400 font-semibold">✓ Privasi Antar-Kampus Terjaga</span>
          </div>
        </div>
      </div>
    </div>

    <!-- 6 Operational KPI Cards for Selected Implementing Campus -->
    <div id="crmCampusKpiRow" class="crm-kpi-grid-6">
      <!-- Populated by JS -->
    </div>

    <!-- Personalized Action Plan & Tactical Recommendations Only for This Campus -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="crm-toolbar-row pb-4 border-b border-slate-100">
        <div>
          <div class="flex flex-wrap items-center gap-2">
            <h3 class="text-base font-extrabold text-slate-900">
              Rekomendasi Operasional &amp; Daftar Tugas Harian: <span id="crmCampusRecTitle" class="text-emerald-700"><?= esc($matchedCampus['kampus'] ?? 'UBSI KAMPUS YOGYAKARTA') ?></span>
            </h3>
            <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">Khusus Internal Kampus Pelaksana</span>
          </div>
          <p class="text-xs text-slate-500 mt-0.5">
            Rekomendasi taktis ini disusun otomatis dari kondisi nyata event, agent, dan tahapan funnel di kampus ini (tidak ditampilkan ke kampus lain).
          </p>
        </div>
        <button id="exportSingleCampusCrmBtn" type="button" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-slate-900 hover:bg-slate-800 text-white transition flex items-center gap-1.5 shrink-0">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
          <span>Unduh Lembar Kerja Kampus Ini (CSV)</span>
        </button>
      </div>

      <div id="crmCampusRecList" class="crm-rec-grid-2 pt-4">
        <!-- Populated by JS -->
      </div>
    </div>

    <!-- Charts for Selected Campus: Funnel & Agent/Event Breakdown -->
    <div class="crm-charts-12">
      <div class="crm-col-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-900">Distribusi Status Hasil CRM &amp; Tahap Funnel Kampus Ini</h3>
        <p class="text-xs text-slate-500 mb-3">Proporsi prospek Fresh, Tidak Merespon, Minat/Ragu, Menolak, dan Closing (Tahap 1–3)</p>
        <div class="relative h-64 w-full">
          <canvas id="crmCampusFunnelChart"></canvas>
        </div>
        <div id="crmCampusStageSummaryPills" class="crm-stage-grid-3 mt-4 pt-3 border-t border-slate-100 text-center text-xs">
          <!-- Populated by JS -->
        </div>
      </div>

      <div class="crm-col-7 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <div class="flex items-center justify-between gap-2 mb-3">
          <div>
            <h3 class="text-sm font-bold text-slate-900">Distribusi Beban Kerja &amp; Hasil Follow-Up Agent di Kampus Ini</h3>
            <p class="text-xs text-slate-500">Memantau tumpukan data Fresh vs progres follow-up pada tim internal kampus</p>
          </div>
        </div>
        <div class="relative h-72 w-full">
          <canvas id="crmCampusAgentChart"></canvas>
        </div>
      </div>
    </div>

    <!-- 3 Internal Operational Tables for Implementing Campus -->
    <div class="crm-charts-12">
      <!-- Event Performance Table -->
      <div class="crm-col-12 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-900 mb-1">1. Evaluasi Performa per Event di Kampus Ini</h3>
        <p class="text-xs text-slate-500 mb-3">Rincian konversi setiap sumber kegiatan/event yang dilaksanakan oleh kampus ini beserta tindak lanjut spesifiknya</p>
        <div class="overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-800 text-white font-semibold uppercase">
                <th class="py-2.5 px-3">Kode &amp; Nama Event</th>
                <th class="py-2.5 px-2.5">Kategori</th>
                <th class="py-2.5 px-2.5">Tanggal &amp; Lokasi</th>
                <th class="py-2.5 px-2 text-center">Total</th>
                <th class="py-2.5 px-2 text-center">Unassigned</th>
                <th class="py-2.5 px-2 text-center">Fresh</th>
                <th class="py-2.5 px-2 text-center">Tidak Respon</th>
                <th class="py-2.5 px-2 text-center">Minat/Ragu</th>
                <th class="py-2.5 px-2 text-center">Menolak</th>
                <th class="py-2.5 px-2 text-center">Closing</th>
                <th class="py-2.5 px-3">Rekomendasi Tindak Lanjut Event</th>
              </tr>
            </thead>
            <tbody id="crmCampusEventTableBody" class="divide-y divide-slate-200">
              <!-- Populated by JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Agent Workload & SLA Table -->
      <div class="crm-col-7 bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <h3 class="text-sm font-bold text-slate-900 mb-1">2. Evaluasi Beban Kerja &amp; Kinerja Agent CRM Internal Kampus</h3>
        <p class="text-xs text-slate-500 mb-3">Daftar agent CRM di kampus ini beserta instruksi supervisi untuk koordinator kampus</p>
        <div class="overflow-x-auto crm-custom-scrollbar rounded-xl border border-slate-200 max-h-96">
          <table class="w-full text-left border-collapse text-xs">
            <thead class="sticky top-0 bg-slate-800 text-white font-semibold uppercase">
              <tr>
                <th class="py-2.5 px-3">Nama Agent CRM</th>
                <th class="py-2.5 px-2 text-center">Total</th>
                <th class="py-2.5 px-2 text-center">Fresh</th>
                <th class="py-2.5 px-2 text-center">Tdk Respon</th>
                <th class="py-2.5 px-2 text-center">Minat/Ragu</th>
                <th class="py-2.5 px-2 text-center">Closing</th>
                <th class="py-2.5 px-3">Evaluasi &amp; Instruksi Koordinator</th>
              </tr>
            </thead>
            <tbody id="crmCampusAgentTableBody" class="divide-y divide-slate-200">
              <!-- Populated by JS -->
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Origin Schools & Local Rejection Reasons -->
      <div class="crm-col-5 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 flex flex-col justify-between space-y-4">
        <div>
          <h3 class="text-sm font-bold text-slate-900 mb-1">3. Asal Sekolah Prospek Terbanyak di Kampus Ini</h3>
          <p class="text-xs text-slate-500 mb-2">Sekolah penyumbang prospek &amp; closing terbesar untuk prioritas kunjungan ulang</p>
          <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-52">
            <table class="w-full text-left border-collapse text-xs">
              <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                <tr>
                  <th class="py-2 px-3">Asal Sekolah</th>
                  <th class="py-2 px-2 text-center">Prospek</th>
                  <th class="py-2 px-2 text-center">Minat/Ragu</th>
                  <th class="py-2 px-2 text-center">Closing</th>
                </tr>
              </thead>
              <tbody id="crmCampusSchoolTableBody" class="divide-y divide-slate-200">
                <!-- Populated by JS -->
              </tbody>
            </table>
          </div>
        </div>

        <div class="pt-3 border-t border-slate-100">
          <h4 class="text-xs font-bold text-slate-900 mb-1.5">Catatan Alasan Menolak / Kendala Nomor WA di Kampus Ini</h4>
          <div id="crmCampusReasonList" class="space-y-1.5 max-h-40 overflow-y-auto crm-custom-scrollbar text-xs">
            <!-- Populated by JS -->
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if ($canViewAllCrmCampuses): ?>
  <!-- =================================================================== -->
  <!-- TAB 4: SKEMA HAK AKSES CRM (ADMIN PUSAT VS KAMPUS PELAKSANA)        -->
  <!-- =================================================================== -->
  <section id="view-access-guide" class="hidden space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <h2 class="text-base font-extrabold text-slate-900 mb-1">Matriks Pemisahan Hak Akses &amp; Visibilitas Rekomendasi CRM</h2>
      <p class="text-xs text-slate-500 mb-4">Pengaturan tata kelola informasi antara Admin Pusat (Pengawas Nasional) dan Koordinator/Tim CRM Kampus Pelaksana.</p>

      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left border-collapse text-xs sm:text-sm">
          <thead>
            <tr class="bg-slate-900 text-white font-bold">
              <th class="py-3 px-4">Modul / Informasi &amp; Rekomendasi CRM</th>
              <th class="py-3 px-4 text-center bg-indigo-950">🔐 View Admin Pusat (Semua Kampus)</th>
              <th class="py-3 px-4 text-center bg-emerald-950">🏢 View Kampus Pelaksana (Per Kampus)</th>
              <th class="py-3 px-4">Tujuan Pengaturan Hak Akses</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-200">
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Agregasi Funnel Nasional (8.642 Siswa) &amp; Peringkat 20 Kampus</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Terbuka Penuh (Semua 20 Kampus)</td>
              <td class="py-3 px-4 text-center font-bold text-rose-700 bg-rose-50/50">🔒 Terkunci (Hanya Lihat Kampus Sendiri)</td>
              <td class="py-3 px-4 text-xs text-slate-600">Mencegah kebocoran strategi &amp; menjaga fokus operasional masing-masing kampus.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Database Siswa, Nomor WhatsApp, &amp; Asal Sekolah</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Akses Audit &amp; Realokasi Lintas Kampus</td>
              <td class="py-3 px-4 text-center font-bold text-amber-800 bg-amber-50/50">⚠️ Terbatas Hanya Siswa di Kampusnya</td>
              <td class="py-3 px-4 text-xs text-slate-600">Melindungi privasi data calon mahasiswa (UU PDP) agar tidak dihubungi ganda oleh kampus cabang lain.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Distribusi Lead "Belum diassign" &amp; Penunjukan Agent CRM</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Monitoring SLA &amp; Intervensi Auto-Assign</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Eksekusi Pembagian Kuota ke Agent Lokal</td>
              <td class="py-3 px-4 text-xs text-slate-600">Koordinator kampus membagi tugas harian; Admin Pusat memantau kampus yang menumpuk data &gt;24 jam.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Leaderboard &amp; Evaluasi 68 Agent CRM</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Semua 68 Agent Lintas 20 Kampus</td>
              <td class="py-3 px-4 text-center font-bold text-amber-800 bg-amber-50/50">⚠️ Hanya Agent Internal Kampusnya</td>
              <td class="py-3 px-4 text-xs text-slate-600">Memudahkan koordinator kampus menegur agent yang masih menumpuk data Fresh di kampusnya.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Evaluasi ROI 9 Kategori Event (Aplikasi PMB vs Edufair)</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-700 bg-emerald-50/50">✓ Analitik Makro 46 Event Nasional</td>
              <td class="py-3 px-4 text-center font-bold text-amber-800 bg-amber-50/50">⚠️ Hanya Event yang Dilaksanakan Kampusnya</td>
              <td class="py-3 px-4 text-xs text-slate-600">Admin menentukan alokasi anggaran event nasional; kampus mengevaluasi tindak lanjut per event lokal.</td>
            </tr>
            <tr>
              <td class="py-3 px-4 font-bold text-slate-900">Rekomendasi Tindak Lanjut &amp; Action Plan</td>
              <td class="py-3 px-4 text-center font-bold text-indigo-800 bg-indigo-50/50">Kebijakan SLA, Standarisasi Sistem, &amp; Replikasi Best-Practice</td>
              <td class="py-3 px-4 text-center font-bold text-emerald-800 bg-emerald-50/50">Daftar Tugas Harian: Distribusi Unassigned, Follow-up Fresh, &amp; Closing Sprint</td>
              <td class="py-3 px-4 text-xs text-slate-600">Memastikan setiap level organisasi menerima rekomendasi yang tepat sesuai wewenangnya.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- Toast Notification -->
  <div id="toastNotice" class="fixed bottom-5 right-5 z-50 hidden bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-lg text-xs font-medium border border-slate-700">
    File CSV berhasil diunduh!
  </div>

</div>

<script>
  const CAN_VIEW_ALL_CRM_CAMPUSES = <?= $canViewAllCrmCampuses ? 'true' : 'false' ?>;
  const CRM_CAMPUS_LIST = <?= json_encode($visibleCrmCampuses, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  const CRM_AGENTS_GLOBAL = <?= json_encode($visibleCrmAgents, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  const CRM_CATEGORY_SUMMARY = [
    { category: 'Edufair', total: 6334, fresh: 3959, noResp: 2181, minat: 13, ragu: 18, t2Unlabeled: 4, menolak: 148, closing: 11, convPct: 0.17 },
    { category: 'Aplikasi PMB', total: 1190, fresh: 369, noResp: 216, minat: 121, ragu: 11, t2Unlabeled: 0, menolak: 22, closing: 451, convPct: 37.90 },
    { category: 'Non Akademik', total: 298, fresh: 134, noResp: 69, minat: 13, ragu: 24, t2Unlabeled: 0, menolak: 8, closing: 50, convPct: 16.78 },
    { category: 'Open Booth (Mall/CFD/Public Area)', total: 273, fresh: 152, noResp: 118, minat: 1, ragu: 1, t2Unlabeled: 0, menolak: 1, closing: 0, convPct: 0.00 },
    { category: 'Akademik', total: 206, fresh: 0, noResp: 179, minat: 0, ragu: 0, t2Unlabeled: 1, menolak: 26, closing: 0, convPct: 0.00 },
    { category: 'Sosialisasi', total: 186, fresh: 186, noResp: 0, minat: 0, ragu: 0, t2Unlabeled: 0, menolak: 0, closing: 0, convPct: 0.00 },
    { category: 'BKOT/NKBO/TWOT', total: 87, fresh: 87, noResp: 0, minat: 0, ragu: 0, t2Unlabeled: 0, menolak: 0, closing: 0, convPct: 0.00 },
    { category: 'Open House (Hangout/NICE/Cybertalk)', total: 44, fresh: 17, noResp: 12, minat: 10, ragu: 2, t2Unlabeled: 0, menolak: 1, closing: 2, convPct: 4.55 },
    { category: 'Workshop', total: 24, fresh: 24, noResp: 0, minat: 0, ragu: 0, t2Unlabeled: 0, menolak: 0, closing: 0, convPct: 0.00 }
  ];

  const ADMIN_STRATEGIC_RECS = [
    {
      id: 'REC-ADM-01',
      cat: 'SLA',
      priority: 'KRITIS (SLA < 24 JAM)',
      badgeColor: 'bg-rose-100 text-rose-800 border-rose-300',
      title: '1. Intervensi Darurat 3.770 Data "Belum Di-assign" (43,6% Populasi CRM)',
      finding: 'Sebanyak 3.770 dari 8.642 prospek belum ditugaskan ke Agent CRM sama sekali. Terdapat 11 kampus dengan rasio Unassigned >80%: Tangerang (1.002/92,7%), Fatmawati (556/93,8%), Tasikmalaya (484/86,1%), Cibitung (448/95,3%), UNM Margonda (309/100%), Tegal (204/100%), Bogor (188/100%), Bekasi Cut Mutia (140/100%), Slipi (140/100%), Cengkareng (125/95,4%), Karawang (87/100%), dan Pontianak (12/92,3%).',
      action: 'Terapkan kebijakan Auto-Assignment / Round-Robin di sistem CRM pusat jika Koordinator Kampus tidak membagi data dalam 1x24 jam setelah batch diunggah. Wajibkan penunjukan minimal 2–5 Agent CRM aktif di 6 kampus yang saat ini masih memiliki 0 agent (UNM Margonda, Tegal, Bogor, Bekasi Cut Mutia, Slipi, Karawang).',
      targetCluster: 'URGENT_UNASSIGNED',
      btnLabel: 'Lihat 11 Kampus Darurat Unassigned'
    },
    {
      id: 'REC-ADM-02',
      cat: 'SLA',
      priority: 'TINGGI (SUPERVISI AGENT)',
      badgeColor: 'bg-amber-100 text-amber-800 border-amber-300',
      title: '2. Penertiban 1.207 Data "Fresh Tertahan di Agent" (Sudah Di-assign Tapi Belum Dihubungi)',
      finding: 'Sebanyak 1.207 data sudah memiliki nama Agent CRM namun statusnya masih "Fresh" (belum ada tanggal follow-up), terpusat di 4 kampus: UBSI Yogyakarta (513 siswa pada event EVT-2855 Klaten Edu Expo), UNM Rawamangun (416 siswa di 5 agent, termasuk Adit [94 Fresh] & Harnojo [94 Fresh] yang belum menyentuh 1 pun data), UBSI Kaliabang (126 siswa di nasiela Azira), dan UBSI Sukabumi (84 siswa di 4 agent, 100% masih Fresh).',
      action: 'Aktifkan pengingat SLA harian dan fitur tarik-ulang otomatis (Re-assign) apabila data Fresh di tangan agent tidak disentuh lebih dari 3 hari kerja.',
      targetCluster: 'STALLED_ASSIGNED',
      btnLabel: 'Lihat 4 Kampus Stagnan di Agent'
    },
    {
      id: 'REC-ADM-03',
      cat: 'CONV',
      priority: 'STRATEGIS (BENCHMARK)',
      badgeColor: 'bg-emerald-100 text-emerald-800 border-emerald-300',
      title: '3. Replikasi SOP Kampus Benchmark Konversi Tinggi (Cikarang 68,7%, Cikampek 57,0%, Solo 24,5%)',
      finding: '80,4% dari seluruh Closing nasional (413 dari 514 Closing) disumbangkan hanya oleh 2 kampus: UBSI Cikarang (259 Closing dari 377 data Aplikasi PMB; Indah Novitasari 139 Closing & Salsabila Qibtia 116 Closing) dan UBSI Cikampek (154 Closing + 87 Minat dari 270 data Aplikasi PMB), disusul UBSI Solo (27 Closing / 24,5% dari Live TikTok PMB oleh ahmad fauzi).',
      action: 'Jadikan SOP pengawalan pendaftar Aplikasi PMB milik Cikarang & Cikampek (konversi kanal Aplikasi PMB mencapai 37,9%) serta strategi Live TikTok PMB milik Solo sebagai standar wajib bagi seluruh 17 kampus lainnya.',
      targetCluster: 'BENCHMARK_CLOSING',
      btnLabel: 'Lihat 3 Kampus Benchmark Closing'
    },
    {
      id: 'REC-ADM-04',
      cat: 'CONV',
      priority: 'CEPAT (QUICK WINS)',
      badgeColor: 'bg-sky-100 text-sky-800 border-sky-300',
      title: '4. Akselerasi "Closing Sprint" 219 Prospek Hangat (158 Minat, 56 Ragu-ragu, 5 Tahap 2 Belum Berlabel)',
      finding: 'Terdapat 219 prospek berskor 55 yang berada di ambang Closing: Cikampek (87 Minat), Fatmawati (27 Minat + 6 Ragu), Tasikmalaya (10 Minat + 20 Ragu), UNM Rawamangun (10 Minat + 3 Ragu), Cibitung (9 Minat + 3 Ragu), Jatiwaringin (12 Ragu), Cikarang (5 Minat + 3 Ragu), Kaliabang (5 Minat + 2 Ragu), Solo (3 Minat + 4 Ragu), Tangerang (1 Minat + 3 Ragu), Pontianak (1 Minat), serta 5 siswa "Tahap 2 belum berlabel" di Yogyakarta.',
      action: 'Instruksikan program khusus "Closing Sprint 7 Hari" (pendampingan langsung pengisian daftar ulang, konsultasi beasiswa/cicilan UKT) untuk mengonversi minimal 50% dari 219 prospek hangat ini menjadi +110 Closing baru.',
      targetCluster: 'ALL',
      btnLabel: 'Buka Matriks 20 Kampus'
    },
    {
      id: 'REC-ADM-05',
      cat: 'CONV',
      priority: 'TINGGI (RE-ENGAGEMENT)',
      badgeColor: 'bg-purple-100 text-purple-800 border-purple-300',
      title: '5. Solusi Bottleneck 2.775 Data "Tidak Merespon" di Yogyakarta (1.384) & Jatiwaringin (1.156)',
      finding: 'Sebanyak 91,5% kasus "Tidak Merespon" nasional terkonsentrasi di UBSI Yogyakarta (1.384 dari 2.056 data / 67,3%) dan UBSI Jatiwaringin (1.156 dari 1.203 data / 96,1%). Selain itu, kategori Edufair menyumbang 6.334 data (73,3% populasi) namun baru menghasilkan 11 Closing (0,17%) karena 2.181 tidak merespon dan 3.959 masih Fresh.',
      action: 'Ubah mekanisme pengumpulan data di booth Edufair dengan mewajibkan siswa memindai QR WhatsApp Official untuk mengunduh E-Brosur/Voucher Beasiswa di tempat, sehingga nomor WA langsung terverifikasi aktif dan membuka jendela percakapan dua arah.',
      targetCluster: 'HIGH_NORESPONSE',
      btnLabel: 'Lihat 2 Kampus Bottleneck Respon'
    },
    {
      id: 'REC-ADM-06',
      cat: 'DATA',
      priority: 'STANDARISASI SISTEM',
      badgeColor: 'bg-slate-200 text-slate-800 border-slate-400',
      title: '6. Perbaikan Anomali Sistem: 49 Data Berlabel Tanpa Agent, 1.178 Tanpa Sekolah, & 0% Catatan',
      finding: 'Ditemukan 3 masalah kualitas data di CRM: (a) 49 data berstatus "Belum diassign" tapi sudah memiliki hasil follow-up (Fatmawati 46 data termasuk 3 Closing & 18 Minat; Cibitung 1 Closing; Solo 1 Closing; Cengkareng 1 Tidak Merespon); (b) 1.178 baris (13,6%) tanpa Asal Sekolah (Fatmawati 593, Bekasi Cut Mutia 140, Cengkareng 131, Bogor 127, Cikampek 116); (c) 100% dari 8.642 baris memiliki kolom "Catatan" kosong (-).',
      action: 'Perbarui validasi sistem CRM agar perubahan Tahap/Label otomatis mengikat akun Agent yang login, jadikan kolom "Asal Sekolah" dan "Catatan Follow-Up" sebagai isian wajib (mandatory), serta aktifkan filter validasi nomor WhatsApp.',
      targetCluster: 'ALL',
      btnLabel: 'Audit di Matriks Kampus'
    }
  ];

  let state = {
    activeTab: '<?= esc($initialActiveTab) ?>',
    adminRecCat: 'ALL',
    adminChartMode: 'VOL',
    crmSelectedCampusNo: <?= (int) $selectedCampusNo ?>,
    crmCampusFilterGroup: 'ALL'
  };

  let crmAdminCampusChartInstance = null;
  let crmAdminCategoryChartInstance = null;
  let crmAdminReasonChartInstance = null;
  let crmCampusFunnelChartInstance = null;
  let crmCampusAgentChartInstance = null;

  function classifyCampusCluster(crm) {
    const safeTotal = Math.max(1, crm.total);
    const unassPct = crm.total > 0 ? Math.round((crm.unassigned / safeTotal) * 100) : 0;
    const closePct = crm.total > 0 ? ((crm.closing / safeTotal) * 100).toFixed(1) : '0.0';
    const noRespPct = crm.total > 0 ? Math.round((crm.noResp / safeTotal) * 100) : 0;

    if (crm.closing >= 25 && parseFloat(closePct) >= 20) {
      return {
        code: 'BENCHMARK_CLOSING',
        badge: `Benchmark Closing (${closePct}%)`,
        badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-300',
        adminNote: `Role Model Nasional: ${crm.closing} Closing dari ${crm.total} data (Skor ${crm.avgScore}). Kawal ${crm.minat + crm.ragu} prospek hangat tersisa.`
      };
    }
    if (unassPct >= 80) {
      return {
        code: 'URGENT_UNASSIGNED',
        badge: `Darurat Unassigned (${unassPct}%)`,
        badgeClass: 'bg-rose-100 text-rose-800 border-rose-300',
        adminNote: `${crm.unassigned} dari ${crm.total} siswa belum di-assign ke Agent CRM. Instruksikan distribusi seluruh lead maks 1x24 jam!`
      };
    }
    if (noRespPct >= 65) {
      return {
        code: 'HIGH_NORESPONSE',
        badge: `Bottleneck Tdk Respon (${noRespPct}%)`,
        badgeClass: 'bg-purple-100 text-purple-800 border-purple-300',
        adminNote: `${crm.noResp} siswa Tidak Merespon (${noRespPct}%) & ${crm.freshAssigned} masih Fresh di agent. Evaluasi template WA & jam hubungi.`
      };
    }
    if (crm.freshAssigned >= 80) {
      return {
        code: 'STALLED_ASSIGNED',
        badge: `Stagnan di Agent (${crm.freshAssigned} Fresh)`,
        badgeClass: 'bg-amber-100 text-amber-800 border-amber-300',
        adminNote: `Sudah di-assign ke ${crm.agents.filter(a=>a.name!=='Belum diassign').length} agent, tapi ${crm.freshAssigned} data (${Math.round(crm.freshAssigned/safeTotal*100)}%) belum disentuh.`
      };
    }
    return {
      code: 'NORMAL',
      badge: 'Aktif CRM',
      badgeClass: 'bg-indigo-100 text-indigo-800 border-indigo-300',
      adminNote: `Total ${crm.total} data, ${crm.closing} Closing.`
    };
  }

  function buildCampusSpecificCrmRecs(crm) {
    const recs = [];
    const safeTotal = Math.max(1, crm.total);
    const realAgents = crm.agents.filter((a) => a.name !== 'Belum diassign');
    const warmTotal = crm.minat + crm.ragu + crm.t2Unlabeled;

    if (crm.unassigned > 0) {
      const unassPct = Math.round((crm.unassigned / safeTotal) * 100);
      const topUnassEvents = crm.events
        .filter((e) => e.unassigned > 0)
        .map((e) => `${e.code} (${e.name}: ${e.unassigned} siswa)`)
        .join(', ');
      recs.push({
        priority: 'PRIORITAS 1 • DISTRIBUSI DATA BELUM DI-ASSIGN',
        color: 'border-rose-300 bg-rose-50/80 text-rose-950',
        badge: `${crm.unassigned} Siswa Unassigned (${unassPct}%)`,
        title: `Segera Bagikan ${crm.unassigned} Prospek "Belum diassign" ke Agent CRM Kampus`,
        detail: `Terdapat ${crm.unassigned} siswa yang belum memiliki PIC Agent pada event: ${topUnassEvents}. ${
          realAgents.length === 0
            ? 'Kampus Anda belum menugaskan 1 pun Agent CRM pada data yang diunggah. Koordinator kampus wajib segera menunjuk Agent CRM dan membagi kuota maksimal 50–70 siswa per agent.'
            : `Bagikan secara merata kepada tim agent kampus (saat ini ada ${realAgents.length} agent aktif: ${realAgents.slice(0,4).map(a=>a.name).join(', ')}) atau tambahkan agent baru agar seluruh data Fresh tersentuh minggu ini.`
        }`
      });
    }

    if (crm.freshAssigned > 0) {
      const stalledAgents = realAgents
        .filter((a) => a.fresh > 0)
        .slice(0, 5)
        .map((a) => `${a.name} (${a.fresh} Fresh dari ${a.total} data)`)
        .join(', ');
      recs.push({
        priority: 'PRIORITAS EKSEKUSI • FOLLOW-UP DATA FRESH DI AGENT',
        color: 'border-amber-300 bg-amber-50/80 text-amber-950',
        badge: `${crm.freshAssigned} Siswa Fresh Tertahan`,
        title: `Selesaikan Kontak Pertama (Tahap 1) untuk ${crm.freshAssigned} Data yang Sudah Di-assign`,
        detail: `Sebanyak ${crm.freshAssigned} siswa sudah diserahkan ke agent namun belum difollow-up sama sekali. Rincian beban agent tertinggi: ${stalledAgents}. Tetapkan target kuota wajib minimal 25–30 kontak WhatsApp per agent per hari hingga saldo Fresh menjadi 0.`
      });
    }

    if (warmTotal > 0) {
      recs.push({
        priority: 'QUICK WINS • KONVERSI PROSPEK HANGAT KE CLOSING',
        color: 'border-emerald-300 bg-emerald-50/80 text-emerald-950',
        badge: `${warmTotal} Prospek Hangat (Skor 55)`,
        title: `Kawal Khusus ${crm.minat} Siswa "Minat/Tertarik"${crm.ragu > 0 ? `, ${crm.ragu} "Ragu-ragu"` : ''}${crm.t2Unlabeled > 0 ? `, & ${crm.t2Unlabeled} "Tahap 2 belum berlabel"` : ''}`,
        detail: `Ini adalah peluang Closing terdekat di ${crm.kampus}. Tugaskan agent senior untuk menghubungi ulang melalui telepon/WA personal, tawarkan informasi beasiswa/potongan gelombang dan panduan pembayaran daftar ulang.${
          crm.t2Unlabeled > 0
            ? ' Khusus 5 data "Tahap 2 belum berlabel" (pada agent musthofa yudhistira [3], brigida sasta [1], putri yuli astuti [1]), segera lengkapi label hasil CRM-nya!'
            : ''
        }`
      });
    }

    if (crm.noResp > 0) {
      const noRespPct = Math.round((crm.noResp / safeTotal) * 100);
      recs.push({
        priority: 'OPTIMASI PESAN • RE-ENGAGEMENT TIDAK MERESPON',
        color: 'border-indigo-300 bg-indigo-50/80 text-indigo-950',
        badge: `${crm.noResp} Tidak Merespon (${noRespPct}%)`,
        title: `Ubah Jadwal & Pendekatan Follow-Up untuk ${crm.noResp} Siswa "Tidak Merespon"`,
        detail: `Saat ini ada ${crm.noResp} siswa yang belum membalas pesan (Tahap 1: ${crm.t1}, Tahap 2: ${crm.t2}). Hindari pengiriman teks panjang yang kaku pada jam sekolah; gunakan pesan singkat interaktif pada pukul 15.30–19.30 WIB berisi info kuota beasiswa/tryout atau undangan acara kampus.`
      });
    }

    const notes = [];
    if (crm.anomUnassigned > 0) {
      notes.push(`Terdapat ${crm.anomUnassigned} data yang sudah memiliki hasil follow-up (termasuk Closing/Minat) tetapi kolom Agent CRM masih tercatat "Belum diassign"—segera perbarui nama PIC Agent-nya.`);
    }
    if (crm.missingSchool > 0) {
      notes.push(`Sebanyak ${crm.missingSchool} dari ${crm.total} siswa belum memiliki data "Asal Sekolah" (masih tertulis "-"). Lengkapi asal sekolah dari file sumber agar pemetaan sekolah prioritas lebih akurat.`);
    }
    if (crm.reasons.length > 0) {
      const topR = crm.reasons.slice(0, 3).map((r) => `"${r.reason}" (${r.count})`).join(', ');
      notes.push(`Alasan menolak/kendala tercatat di kampus ini: ${topR}. Gunakan data ini untuk memfilter nomor WA yang tidak aktif dan menyiapkan counter-offer beasiswa.`);
    }
    notes.push(`Seluruh ${crm.total} baris data masih memiliki kolom "Catatan" kosong (-). Wajibkan agent mengisi ringkasan respon siswa di kolom Catatan pada setiap perubahan tahap.`);

    recs.push({
      priority: 'STANDARISASI KUALITAS DATA INTERNAL KAMPUS',
      color: 'border-slate-300 bg-slate-50 text-slate-900',
      badge: 'Hygiene Database',
      title: `Perbaikan Kelengkapan Data Sekolah, Catatan Agent, & Validasi Nomor WA`,
      detail: notes.join(' ')
    });

    return recs;
  }

  function renderAdminOverview() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const recContainer = document.getElementById('adminStrategicRecGrid');
    if (!recContainer) return;
    recContainer.innerHTML = '';

    const filteredRecs = ADMIN_STRATEGIC_RECS.filter(
      (r) => state.adminRecCat === 'ALL' || r.cat === state.adminRecCat
    );

    filteredRecs.forEach((r) => {
      const card = document.createElement('div');
      card.className = 'bg-slate-50/80 rounded-2xl p-4 border border-slate-200 flex flex-col justify-between hover:border-indigo-300 transition shadow-sm';
      card.innerHTML = `
        <div>
          <div class="flex items-center justify-between gap-2 mb-2">
            <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase rounded-full border ${r.badgeColor}">${r.priority}</span>
            <span class="text-[11px] font-mono font-bold text-slate-400">${r.id}</span>
          </div>
          <h4 class="text-sm font-extrabold text-slate-900 leading-snug">${r.title}</h4>
          <div class="mt-2.5 space-y-2 text-xs">
            <div class="p-2.5 rounded-xl bg-white border border-slate-200/80 text-slate-700">
              <strong class="text-slate-900">Temuan Data:</strong> ${r.finding}
            </div>
            <div class="p-2.5 rounded-xl bg-indigo-50/70 border border-indigo-200/80 text-indigo-950">
              <strong class="text-indigo-900">Rekomendasi Admin Pusat:</strong> ${r.action}
            </div>
          </div>
        </div>
        <div class="mt-3 pt-2.5 border-t border-slate-200/70 flex items-center justify-end">
          <button type="button" data-cluster-target="${r.targetCluster}" class="admin-rec-jump-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-600 hover:bg-indigo-700 text-white transition">
            ${r.btnLabel} &rarr;
          </button>
        </div>
      `;
      recContainer.appendChild(card);
    });

    recContainer.querySelectorAll('.admin-rec-jump-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-cluster-target');
        const filterEl = document.getElementById('adminCrmClusterFilter');
        if (filterEl) filterEl.value = target;
        switchTab('admin-matrix');
        renderAdminMatrixAndAgents();
      });
    });

    updateAdminOverviewCharts();
  }

  function updateAdminOverviewCharts() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const isPct = state.adminChartMode === 'PCT';
    const labels = CRM_CAMPUS_LIST.map((x) => x.kampus.replace('UBSI KAMPUS ', 'UBSI ').replace('UNM KAMPUS ', 'UNM '));
    const toVal = (val, tot) => (isPct ? Math.round((val / Math.max(1, tot)) * 1000) / 10 : val);

    const c1 = document.getElementById('crmAdminCampusChart');
    if (c1) {
      const ctx1 = c1.getContext('2d');
      if (crmAdminCampusChartInstance) crmAdminCampusChartInstance.destroy();
      crmAdminCampusChartInstance = new Chart(ctx1, {
        type: 'bar',
        data: {
          labels,
          datasets: [
            {
              label: 'Fresh Belum Di-Assign',
              data: CRM_CAMPUS_LIST.map((x) => toVal(x.freshUnassigned, x.total)),
              backgroundColor: 'rgba(225, 29, 72, 0.85)'
            },
            {
              label: 'Fresh Sudah Di-Assign',
              data: CRM_CAMPUS_LIST.map((x) => toVal(x.freshAssigned, x.total)),
              backgroundColor: 'rgba(245, 158, 11, 0.85)'
            },
            {
              label: 'Tidak Merespon',
              data: CRM_CAMPUS_LIST.map((x) => toVal(x.noResp, x.total)),
              backgroundColor: 'rgba(100, 116, 139, 0.85)'
            },
            {
              label: 'Minat / Ragu-ragu / T2',
              data: CRM_CAMPUS_LIST.map((x) => toVal(x.minat + x.ragu + x.t2Unlabeled, x.total)),
              backgroundColor: 'rgba(14, 165, 233, 0.9)'
            },
            {
              label: 'Menolak',
              data: CRM_CAMPUS_LIST.map((x) => toVal(x.menolak, x.total)),
              backgroundColor: 'rgba(168, 85, 247, 0.85)'
            },
            {
              label: 'Closing (Tahap 3)',
              data: CRM_CAMPUS_LIST.map((x) => toVal(x.closing, x.total)),
              backgroundColor: 'rgba(16, 185, 129, 0.95)'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: { stacked: true, ticks: { font: { size: 10 }, maxRotation: 45, minRotation: 30 } },
            y: {
              stacked: true,
              beginAtZero: true,
              max: isPct ? 100 : undefined,
              ticks: { callback: (v) => (isPct ? v + '%' : v) }
            }
          },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 11, font: { size: 10 } } }
          }
        }
      });
    }

    const c2 = document.getElementById('crmAdminCategoryChart');
    if (c2) {
      const ctx2 = c2.getContext('2d');
      if (crmAdminCategoryChartInstance) crmAdminCategoryChartInstance.destroy();
      const topCats = CRM_CATEGORY_SUMMARY.slice(0, 6);
      crmAdminCategoryChartInstance = new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: topCats.map((c) => c.category.replace(' (Mall/CFD/Public Area)', '')),
          datasets: [
            {
              label: 'Closing (Siswa)',
              data: topCats.map((c) => c.closing),
              backgroundColor: 'rgba(16, 185, 129, 0.9)',
              borderRadius: 4
            },
            {
              label: 'Minat & Ragu-ragu',
              data: topCats.map((c) => c.minat + c.ragu),
              backgroundColor: 'rgba(14, 165, 233, 0.85)',
              borderRadius: 4
            },
            {
              label: '% Konversi Closing',
              data: topCats.map((c) => c.convPct),
              type: 'line',
              borderColor: 'rgba(79, 70, 229, 1)',
              backgroundColor: 'rgba(79, 70, 229, 0.2)',
              yAxisID: 'y1',
              borderWidth: 2
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: { beginAtZero: true, title: { display: true, text: 'Siswa', font: { size: 10 } } },
            y1: {
              position: 'right',
              beginAtZero: true,
              max: 45,
              grid: { drawOnChartArea: false },
              ticks: { callback: (v) => v + '%' }
            }
          },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } }
          }
        }
      });
    }

    const c3 = document.getElementById('crmAdminReasonChart');
    if (c3) {
      const ctx3 = c3.getContext('2d');
      if (crmAdminReasonChartInstance) crmAdminReasonChartInstance.destroy();
      crmAdminReasonChartInstance = new Chart(ctx3, {
        type: 'bar',
        data: {
          labels: [
            'Sudah Diterima PTN/PTS Lain (48)',
            'Nomor WA Tidak Terdaftar/Aktif (21)',
            'Memilih Bekerja / LPK (13)',
            'Belum Ingin Kuliah / KIP (9)'
          ],
          datasets: [
            {
              label: 'Jumlah Kasus Tercatat',
              data: [48, 21, 13, 9],
              backgroundColor: [
                'rgba(79, 70, 229, 0.85)',
                'rgba(225, 29, 72, 0.85)',
                'rgba(245, 158, 11, 0.85)',
                'rgba(100, 116, 139, 0.85)'
              ],
              borderRadius: 5
            }
          ]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: { x: { beginAtZero: true } }
        }
      });
    }
  }

  function renderAdminMatrixAndAgents() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const tbody = document.getElementById('adminCrmTableBody');
    if (!tbody) return;
    const search = (document.getElementById('adminCrmSearchInput')?.value || '').trim().toLowerCase();
    const clusterFilter = document.getElementById('adminCrmClusterFilter')?.value || 'ALL';
    const sortBy = document.getElementById('adminCrmSortSelect')?.value || 'TOTAL_DESC';

    let rows = CRM_CAMPUS_LIST.map((crm) => ({
      crm,
      cluster: classifyCampusCluster(crm)
    }));

    rows = rows.filter(({ crm, cluster }) => {
      if (search && !crm.kampus.toLowerCase().includes(search)) return false;
      if (clusterFilter === 'ALL') return true;
      return cluster.code === clusterFilter;
    });

    rows.sort((a, b) => {
      if (sortBy === 'TOTAL_DESC') return b.crm.total - a.crm.total;
      if (sortBy === 'CLOSING_DESC') return b.crm.closing - a.crm.closing || b.crm.total - a.crm.total;
      if (sortBy === 'UNASSIGNED_DESC') return b.crm.unassigned - a.crm.unassigned;
      if (sortBy === 'FRESH_DESC') return b.crm.fresh - a.crm.fresh;
      if (sortBy === 'SCORE_DESC') return b.crm.avgScore - a.crm.avgScore;
      if (sortBy === 'NAME_ASC') return a.crm.kampus.localeCompare(b.crm.kampus);
      return b.crm.total - a.crm.total;
    });

    tbody.innerHTML = '';
    rows.forEach(({ crm, cluster }) => {
      const tr = document.createElement('tr');
      tr.className = 'hover:bg-indigo-50/40 transition cursor-pointer';
      const safeTotal = Math.max(1, crm.total);
      const unassPct = Math.round((crm.unassigned / safeTotal) * 100);
      const closePct = ((crm.closing / safeTotal) * 100).toFixed(1);
      const warm = crm.minat + crm.ragu + crm.t2Unlabeled;

      tr.innerHTML = `
        <td class="py-2.5 px-2.5 text-center font-medium text-slate-500">${crm.no}</td>
        <td class="py-2.5 px-3 font-bold text-slate-900">
          <div>${crm.kampus}</div>
          <div class="text-[10px] font-semibold text-slate-400">${crm.events.length} Event • ${crm.agents.filter(a=>a.name!=='Belum diassign').length} Agent Aktif</div>
        </td>
        <td class="py-2.5 px-2.5 text-center font-extrabold text-slate-900">${crm.total.toLocaleString('id-ID')}</td>
        <td class="py-2.5 px-2.5 text-center font-bold ${crm.unassigned > 0 ? 'bg-rose-50/80 text-rose-800' : 'text-slate-400'}">
          ${crm.unassigned > 0 ? `${crm.unassigned.toLocaleString('id-ID')} <span class="text-[10px] block">(${unassPct}%)</span>` : '0'}
        </td>
        <td class="py-2.5 px-2.5 text-center font-bold ${crm.fresh > 0 ? 'bg-amber-50/80 text-amber-800' : 'text-slate-400'}">
          ${crm.fresh > 0 ? `${crm.fresh.toLocaleString('id-ID')} <span class="text-[10px] block">(Asg:${crm.freshAssigned})</span>` : '0'}
        </td>
        <td class="py-2.5 px-2.5 text-center font-semibold ${crm.noResp > 0 ? 'text-slate-800' : 'text-slate-400'}">${crm.noResp.toLocaleString('id-ID')}</td>
        <td class="py-2.5 px-2.5 text-center font-bold ${warm > 0 ? 'bg-sky-50/80 text-sky-800' : 'text-slate-400'}">
          ${warm > 0 ? `${warm} <span class="text-[10px] block">(${crm.minat}M/${crm.ragu}R)</span>` : '0'}
        </td>
        <td class="py-2.5 px-2.5 text-center font-semibold ${crm.menolak > 0 ? 'text-rose-700' : 'text-slate-400'}">${crm.menolak}</td>
        <td class="py-2.5 px-2.5 text-center font-extrabold ${crm.closing > 0 ? 'bg-emerald-50/90 text-emerald-800' : 'text-slate-400'}">
          ${crm.closing > 0 ? `${crm.closing} <span class="text-[10px] block">(${closePct}%)</span>` : '0'}
        </td>
        <td class="py-2.5 px-2 text-center font-bold text-indigo-700">${crm.avgScore}</td>
        <td class="py-2.5 px-3">
          <span class="inline-block px-2 py-0.5 text-[10px] font-bold rounded-full border mb-1 ${cluster.badgeClass}">${cluster.badge}</span>
          <p class="text-[11px] text-slate-600 leading-snug">${cluster.adminNote}</p>
        </td>
        <td class="py-2.5 px-2.5 text-center">
          <button type="button" class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm whitespace-nowrap">
            🏢 View Kampus
          </button>
        </td>
      `;
      tr.addEventListener('click', () => openCrmCampusViewFor(crm.no));
      tbody.appendChild(tr);
    });

    renderGlobalAgentTable();
  }

  function renderGlobalAgentTable() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const agBody = document.getElementById('globalAgentTableBody');
    if (!agBody) return;
    const q = (document.getElementById('globalAgentSearchInput')?.value || '').trim().toLowerCase();
    const f = document.getElementById('globalAgentFilterSelect')?.value || 'ALL';

    let list = CRM_AGENTS_GLOBAL.filter((a) => {
      if (q && !a.name.toLowerCase().includes(q) && !a.kampus.toLowerCase().includes(q)) return false;
      if (f === 'TOP_CLOSING') return a.closing >= 1;
      if (f === 'HAS_WARM') return a.warm >= 1;
      if (f === 'STALLED_FRESH') return a.fresh >= 10;
      if (f === 'ZERO_PROGRESS') return a.fresh === a.total;
      return true;
    });

    agBody.innerHTML = '';
    list.forEach((a, idx) => {
      let statusBadge = '<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">Aktif Follow-Up</span>';
      if (a.closing >= 20) {
        statusBadge = '<span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 font-bold">★ Top Closer Nasional</span>';
      } else if (a.fresh === a.total) {
        statusBadge = '<span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">! Progres 0% (100% Fresh)</span>';
      } else if (a.fresh >= 15) {
        statusBadge = `<span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 font-bold">Tumpukan ${a.fresh} Fresh</span>`;
      } else if (a.warm >= 5) {
        statusBadge = `<span class="px-2 py-0.5 rounded bg-sky-100 text-sky-800 font-bold">Kawal ${a.warm} Prospek Hangat</span>`;
      }

      const tr = document.createElement('tr');
      tr.className = 'hover:bg-slate-50';
      tr.innerHTML = `
        <td class="py-2 px-3 text-center font-bold text-slate-400">#${idx + 1}</td>
        <td class="py-2 px-3 font-bold text-slate-900">${a.name}</td>
        <td class="py-2 px-3 font-medium text-slate-600">${a.kampus}</td>
        <td class="py-2 px-2 text-center font-extrabold">${a.total}</td>
        <td class="py-2 px-2 text-center font-bold ${a.fresh > 0 ? 'text-amber-700 bg-amber-50/50' : 'text-slate-400'}">${a.fresh}</td>
        <td class="py-2 px-2 text-center">${a.noResp}</td>
        <td class="py-2 px-2 text-center font-bold text-sky-700">${a.warm}</td>
        <td class="py-2 px-2 text-center text-rose-700">${a.menolak}</td>
        <td class="py-2 px-2 text-center font-extrabold text-emerald-700 bg-emerald-50/50">${a.closing}</td>
        <td class="py-2 px-2 text-center font-bold text-indigo-700">${a.convPct}%</td>
        <td class="py-2 px-3">${statusBadge}</td>
      `;
      agBody.appendChild(tr);
    });
  }

  function getFilteredCrmCampusList() {
    const f = state.crmCampusFilterGroup;
    return CRM_CAMPUS_LIST.filter((crm) => {
      if (f === 'ALL') return true;
      return classifyCampusCluster(crm).code === f;
    });
  }

  function populateCrmCampusDropdown() {
    const sel = document.getElementById('crmCampusSelect');
    if (!sel) return;
    const list = getFilteredCrmCampusList();
    sel.innerHTML = '';

    if (!list.some((c) => c.no === state.crmSelectedCampusNo)) {
      state.crmSelectedCampusNo = list[0] ? list[0].no : 1;
    }

    list.forEach((crm) => {
      const opt = document.createElement('option');
      opt.value = crm.no;
      opt.textContent = `${crm.no}. ${crm.kampus} — (${crm.total.toLocaleString('id-ID')} Data • ${crm.closing} Closing)`;
      if (crm.no === state.crmSelectedCampusNo) opt.selected = true;
      sel.appendChild(opt);
    });
  }

  function renderCrmCampusView() {
    const crm = CRM_CAMPUS_LIST.find((x) => x.no === state.crmSelectedCampusNo) || CRM_CAMPUS_LIST[0];
    if (!crm) return;
    const cluster = classifyCampusCluster(crm);
    const activeAgentsCount = crm.agents.filter((a) => a.name !== 'Belum diassign').length;
    const safeTotal = Math.max(1, crm.total);

    const titleEl = document.getElementById('crmCampusRecTitle');
    if (titleEl) titleEl.textContent = crm.kampus;
    const badgeEl = document.getElementById('crmCampusClusterBadge');
    if (badgeEl) {
      badgeEl.textContent = cluster.badge;
      badgeEl.className = `px-2.5 py-0.5 text-xs font-extrabold rounded-full border ${cluster.badgeClass}`;
    }
    const metaEl = document.getElementById('crmCampusMetaSubtext');
    if (metaEl) {
      metaEl.textContent = `${crm.events.length} Event • ${crm.batchCount} Batch Upload • ${activeAgentsCount} Agent CRM Aktif`;
    }

    const kpiRow = document.getElementById('crmCampusKpiRow');
    const unassPct = crm.total > 0 ? Math.round((crm.unassigned / safeTotal) * 100) : 0;
    const closePct = crm.total > 0 ? ((crm.closing / safeTotal) * 100).toFixed(1) : '0.0';
    const warmTotal = crm.minat + crm.ragu + crm.t2Unlabeled;

    if (kpiRow) {
      kpiRow.innerHTML = `
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Total Prospek Kampus</div>
          <div class="text-2xl font-extrabold text-slate-900 mt-1">${crm.total.toLocaleString('id-ID')}</div>
          <div class="text-[11px] text-slate-500 mt-1">${crm.events.length} Event • ${crm.batchCount} Batch Upload</div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm ${crm.unassigned > 0 ? 'bg-rose-50/70 border-rose-200' : 'bg-emerald-50/50 border-emerald-200'}">
          <div class="text-[11px] font-bold uppercase tracking-wider ${crm.unassigned > 0 ? 'text-rose-800' : 'text-emerald-800'}">Belum Di-Assign</div>
          <div class="text-2xl font-extrabold ${crm.unassigned > 0 ? 'text-rose-700' : 'text-emerald-700'} mt-1">${crm.unassigned.toLocaleString('id-ID')} <span class="text-xs font-bold">(${unassPct}%)</span></div>
          <div class="text-[11px] text-slate-600 mt-1">Sudah Di-assign: <strong>${crm.assigned.toLocaleString('id-ID')}</strong> (${activeAgentsCount} Agent)</div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm ${crm.fresh > 0 ? 'bg-amber-50/70 border-amber-200' : 'bg-white border-slate-200'}">
          <div class="text-[11px] font-bold uppercase tracking-wider text-amber-900">Saldo Fresh (Tahap 1)</div>
          <div class="text-2xl font-extrabold text-amber-700 mt-1">${crm.fresh.toLocaleString('id-ID')}</div>
          <div class="text-[11px] text-amber-800 mt-1">Di Agent: <strong>${crm.freshAssigned}</strong> • Unassigned: <strong>${crm.freshUnassigned}</strong></div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm bg-sky-50/70 border-sky-200">
          <div class="text-[11px] font-bold uppercase tracking-wider text-sky-900">Prospek Hangat (Skor 55)</div>
          <div class="text-2xl font-extrabold text-sky-700 mt-1">${warmTotal}</div>
          <div class="text-[11px] text-sky-800 mt-1"><strong>${crm.minat} Minat</strong> • <strong>${crm.ragu} Ragu</strong>${crm.t2Unlabeled > 0 ? ` • <strong>${crm.t2Unlabeled} T2</strong>` : ''}</div>
        </div>
        <div class="rounded-2xl p-4 border shadow-sm bg-emerald-50/70 border-emerald-200">
          <div class="text-[11px] font-bold uppercase tracking-wider text-emerald-900">Closing (Tahap 3)</div>
          <div class="text-2xl font-extrabold text-emerald-700 mt-1">${crm.closing} <span class="text-xs font-bold">(${closePct}%)</span></div>
          <div class="text-[11px] text-emerald-800 mt-1">Rata-rata Skor Kampus: <strong>${crm.avgScore}</strong></div>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
          <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Tdk Respon &amp; Kualitas Data</div>
          <div class="text-2xl font-extrabold text-slate-800 mt-1">${crm.noResp} <span class="text-xs font-semibold text-rose-700">/ ${crm.menolak} Tolak</span></div>
          <div class="text-[11px] text-slate-500 mt-1">${crm.schoolCount} Sekolah Terdata (${crm.missingSchool} tanpa sekolah)</div>
        </div>
      `;
    }

    const recs = buildCampusSpecificCrmRecs(crm);
    const recListEl = document.getElementById('crmCampusRecList');
    if (recListEl) {
      recListEl.innerHTML = '';
      recs.forEach((r) => {
        const div = document.createElement('div');
        div.className = `rounded-2xl p-4 border ${r.color} flex flex-col justify-between shadow-sm`;
        div.innerHTML = `
          <div>
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <span class="text-[10px] font-extrabold uppercase tracking-wider opacity-80">${r.priority}</span>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-white/80 border border-current/20">${r.badge}</span>
            </div>
            <h4 class="text-sm font-extrabold leading-snug mb-1.5">${r.title}</h4>
            <p class="text-xs leading-relaxed opacity-90">${r.detail}</p>
          </div>
        `;
        recListEl.appendChild(div);
      });
    }

    const pillsEl = document.getElementById('crmCampusStageSummaryPills');
    if (pillsEl) {
      pillsEl.innerHTML = `
        <div class="p-2 rounded-xl bg-slate-50 border border-slate-200">
          <div class="text-[10px] font-bold uppercase text-slate-500">Tahap 1 (Kontak Awal)</div>
          <div class="text-base font-extrabold text-slate-900 mt-0.5">${crm.t1.toLocaleString('id-ID')}</div>
        </div>
        <div class="p-2 rounded-xl bg-indigo-50 border border-indigo-200">
          <div class="text-[10px] font-bold uppercase text-indigo-700">Tahap 2 (Follow-Up Lanjut)</div>
          <div class="text-base font-extrabold text-indigo-900 mt-0.5">${crm.t2.toLocaleString('id-ID')}</div>
        </div>
        <div class="p-2 rounded-xl bg-emerald-50 border border-emerald-200">
          <div class="text-[10px] font-bold uppercase text-emerald-700">Tahap 3 (Tahap Closing)</div>
          <div class="text-base font-extrabold text-emerald-900 mt-0.5">${crm.t3.toLocaleString('id-ID')}</div>
        </div>
      `;
    }

    const evBody = document.getElementById('crmCampusEventTableBody');
    if (evBody) {
      evBody.innerHTML = '';
      if (crm.events.length === 0) {
        evBody.innerHTML = `<tr><td colspan="11" class="py-4 text-center text-slate-500">Belum ada batch event CRM yang diunggah pada kampus ini.</td></tr>`;
      } else {
        crm.events.forEach((e) => {
          let evRec = 'Pertahankan follow-up berkala';
          if (e.unassigned > 0) {
            evRec = `Darurat: Bagikan ${e.unassigned} data Belum diassign ke agent kampus!`;
          } else if (e.fresh > 0) {
            evRec = `Segera hubungi ${e.fresh} data Fresh yang sudah di-assign ke agent.`;
          } else if (e.minat + e.ragu > 0) {
            evRec = `Prioritaskan closing ${e.minat + e.ragu} prospek hangat (${e.minat} Minat, ${e.ragu} Ragu).`;
          } else if (e.noResp > e.closing) {
            evRec = `Kirim broadcast WA interaktif ulang ke ${e.noResp} siswa Tidak Merespon.`;
          }
          const tr = document.createElement('tr');
          tr.className = 'hover:bg-slate-50';
          tr.innerHTML = `
            <td class="py-2.5 px-3 font-bold text-slate-900">
              <span class="text-indigo-600 font-mono text-[11px]">${e.code}</span> — ${e.name}
            </td>
            <td class="py-2.5 px-2.5"><span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold">${e.category}</span></td>
            <td class="py-2.5 px-2.5 text-slate-600">${e.date}<div class="text-[10px] text-slate-400">${e.location}</div></td>
            <td class="py-2.5 px-2 text-center font-extrabold">${e.total}</td>
            <td class="py-2.5 px-2 text-center font-bold ${e.unassigned > 0 ? 'text-rose-700 bg-rose-50' : 'text-slate-400'}">${e.unassigned}</td>
            <td class="py-2.5 px-2 text-center font-bold ${e.fresh > 0 ? 'text-amber-700 bg-amber-50' : 'text-slate-400'}">${e.fresh}</td>
            <td class="py-2.5 px-2 text-center">${e.noResp}</td>
            <td class="py-2.5 px-2 text-center font-bold text-sky-700">${e.minat + e.ragu + e.t2Unlabeled}</td>
            <td class="py-2.5 px-2 text-center text-rose-700">${e.menolak}</td>
            <td class="py-2.5 px-2 text-center font-extrabold text-emerald-700 bg-emerald-50/50">${e.closing}</td>
            <td class="py-2.5 px-3 text-[11px] font-medium text-slate-700">${evRec}</td>
          `;
          evBody.appendChild(tr);
        });
      }
    }

    const agBody = document.getElementById('crmCampusAgentTableBody');
    if (agBody) {
      agBody.innerHTML = '';
      if (crm.agents.length === 0) {
        agBody.innerHTML = `<tr><td colspan="7" class="py-4 text-center text-slate-500">Belum ada Agent CRM yang ditugaskan di kampus ini.</td></tr>`;
      } else {
        crm.agents.forEach((a) => {
          const isUnass = a.name === 'Belum diassign';
          const warm = a.minat + a.ragu + a.t2Unlabeled;
          let instruction = 'Pertahankan ritme kerja';
          if (isUnass) {
            instruction = a.total > a.fresh
              ? `Anomali: ${a.total - a.fresh} data sudah berlabel tanpa nama agent! Segera assign semua ${a.total} data.`
              : `Wajib segera didistribusikan oleh Koordinator Kampus ke agent aktif!`;
          } else if (a.fresh === a.total) {
            instruction = `Belum menyentuh 1 pun dari ${a.total} data! Tegur & wajibkan kontak hari ini.`;
          } else if (a.fresh > 0) {
            instruction = `Selesaikan ${a.fresh} sisa data Fresh${warm > 0 ? ` & kawal ${warm} prospek hangat` : ''}.`;
          } else if (warm > 0) {
            instruction = `Semua data sudah dihubungi. Fokus konversi ${warm} prospek Minat/Ragu ke Closing!`;
          } else if (a.closing > 0) {
            instruction = `Performa baik (${a.closing} Closing). Lakukan follow-up tahap lanjut pada ${a.noResp} Tidak Merespon.`;
          } else {
            instruction = `Evaluasi script WA (dari ${a.total} data, ${a.noResp} Tidak Merespon & 0 Closing).`;
          }

          const tr = document.createElement('tr');
          tr.className = isUnass ? 'bg-rose-50/70 font-semibold' : 'hover:bg-slate-50';
          tr.innerHTML = `
            <td class="py-2 px-3 font-bold ${isUnass ? 'text-rose-800' : 'text-slate-900'}">${a.name}</td>
            <td class="py-2 px-2 text-center font-extrabold">${a.total}</td>
            <td class="py-2 px-2 text-center font-bold ${a.fresh > 0 ? 'text-amber-700' : 'text-slate-400'}">${a.fresh}</td>
            <td class="py-2 px-2 text-center">${a.noResp}</td>
            <td class="py-2 px-2 text-center font-bold text-sky-700">${warm}</td>
            <td class="py-2 px-2 text-center font-extrabold text-emerald-700">${a.closing}</td>
            <td class="py-2 px-3 text-[11px] text-slate-700">${instruction}</td>
          `;
          agBody.appendChild(tr);
        });
      }
    }

    const schBody = document.getElementById('crmCampusSchoolTableBody');
    if (schBody) {
      schBody.innerHTML = '';
      if (crm.topSchools.length === 0) {
        schBody.innerHTML = `<tr><td colspan="4" class="py-4 text-center text-slate-500">Semua ${crm.missingSchool} data siswa belum mencantumkan nama sekolah (-).</td></tr>`;
      } else {
        crm.topSchools.forEach((s) => {
          const tr = document.createElement('tr');
          tr.innerHTML = `
            <td class="py-2 px-3 font-semibold text-slate-800">${s.name}</td>
            <td class="py-2 px-2 text-center font-bold">${s.total}</td>
            <td class="py-2 px-2 text-center text-sky-700 font-bold">${s.warm}</td>
            <td class="py-2 px-2 text-center text-emerald-700 font-extrabold">${s.closing}</td>
          `;
          schBody.appendChild(tr);
        });
      }
    }

    const reasonEl = document.getElementById('crmCampusReasonList');
    if (reasonEl) {
      reasonEl.innerHTML = '';
      if (crm.reasons.length === 0) {
        reasonEl.innerHTML = `<div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-500">Belum ada keterangan alasan penolakan yang diisi pada kampus ini. Wajibkan agent mencatat alasan jika siswa menolak.</div>`;
      } else {
        crm.reasons.forEach((r) => {
          const item = document.createElement('div');
          item.className = 'flex items-center justify-between px-3 py-1.5 rounded-lg bg-slate-50 border border-slate-200';
          item.innerHTML = `<span class="font-medium text-slate-700">${r.reason}</span><span class="px-2 py-0.5 rounded bg-rose-100 text-rose-800 font-bold">${r.count} siswa</span>`;
          reasonEl.appendChild(item);
        });
      }
    }

    updateCrmCampusCharts(crm);
  }

  function updateCrmCampusCharts(crm) {
    const c1 = document.getElementById('crmCampusFunnelChart');
    if (c1) {
      const ctx1 = c1.getContext('2d');
      if (crmCampusFunnelChartInstance) crmCampusFunnelChartInstance.destroy();
      crmCampusFunnelChartInstance = new Chart(ctx1, {
        type: 'doughnut',
        data: {
          labels: [
            `Fresh Unassigned (${crm.freshUnassigned})`,
            `Fresh di Agent (${crm.freshAssigned})`,
            `Tidak Merespon (${crm.noResp})`,
            `Minat / Ragu / T2 (${crm.minat + crm.ragu + crm.t2Unlabeled})`,
            `Menolak (${crm.menolak})`,
            `Closing (${crm.closing})`
          ],
          datasets: [
            {
              data: [crm.freshUnassigned, crm.freshAssigned, crm.noResp, crm.minat + crm.ragu + crm.t2Unlabeled, crm.menolak, crm.closing],
              backgroundColor: [
                'rgba(225, 29, 72, 0.85)',
                'rgba(245, 158, 11, 0.85)',
                'rgba(100, 116, 139, 0.85)',
                'rgba(14, 165, 233, 0.9)',
                'rgba(168, 85, 247, 0.85)',
                'rgba(16, 185, 129, 0.95)'
              ],
              borderWidth: 1
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 11, font: { size: 10 } } }
          }
        }
      });
    }

    const c2 = document.getElementById('crmCampusAgentChart');
    if (c2) {
      const ctx2 = c2.getContext('2d');
      if (crmCampusAgentChartInstance) crmCampusAgentChartInstance.destroy();
      const topEntities = crm.agents.slice(0, 10);
      crmCampusAgentChartInstance = new Chart(ctx2, {
        type: 'bar',
        data: {
          labels: topEntities.map((a) => a.name.slice(0, 22)),
          datasets: [
            {
              label: 'Masih Fresh',
              data: topEntities.map((a) => a.fresh),
              backgroundColor: 'rgba(245, 158, 11, 0.85)'
            },
            {
              label: 'Tidak Merespon',
              data: topEntities.map((a) => a.noResp),
              backgroundColor: 'rgba(100, 116, 139, 0.85)'
            },
            {
              label: 'Minat / Ragu-ragu',
              data: topEntities.map((a) => a.minat + a.ragu + a.t2Unlabeled),
              backgroundColor: 'rgba(14, 165, 233, 0.9)'
            },
            {
              label: 'Menolak',
              data: topEntities.map((a) => a.menolak),
              backgroundColor: 'rgba(225, 29, 72, 0.8)'
            },
            {
              label: 'Closing',
              data: topEntities.map((a) => a.closing),
              backgroundColor: 'rgba(16, 185, 129, 0.95)'
            }
          ]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: { stacked: true, beginAtZero: true },
            y: { stacked: true, ticks: { font: { size: 10 } } }
          },
          plugins: {
            legend: { position: 'bottom', labels: { boxWidth: 11, font: { size: 10 } } }
          }
        }
      });
    }
  }

  function switchTab(tabName) {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) {
      tabName = 'campus-scoped';
    }
    state.activeTab = tabName;
    const tabs = ['admin-overview', 'admin-matrix', 'campus-scoped', 'access-guide'];
    tabs.forEach((t) => {
      const sec = document.getElementById(`view-${t}`);
      const btn = document.getElementById(`tabBtn-${t}`);
      if (sec) {
        if (t === tabName) {
          sec.classList.remove('hidden');
        } else {
          sec.classList.add('hidden');
        }
      }
      if (btn) {
        if (t === tabName) {
          btn.className = 'nav-tab-btn px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all bg-indigo-600 text-white shadow';
        } else {
          btn.className = 'nav-tab-btn px-3 py-1.5 rounded-lg text-xs sm:text-sm font-semibold transition-all text-slate-300 hover:text-white hover:bg-slate-700/60';
        }
      }
    });
    if (tabName === 'admin-overview') renderAdminOverview();
    if (tabName === 'admin-matrix') renderAdminMatrixAndAgents();
    if (tabName === 'campus-scoped') renderCrmCampusView();
  }

  function openCrmCampusViewFor(campusNo) {
    state.crmSelectedCampusNo = campusNo;
    state.crmCampusFilterGroup = 'ALL';
    const qf = document.getElementById('crmCampusQuickFilter');
    if (qf) qf.value = 'ALL';
    populateCrmCampusDropdown();
    switchTab('campus-scoped');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function exportAdminCrmCsv() {
    if (!CAN_VIEW_ALL_CRM_CAMPUSES) return;
    const headers = [
      'No', 'Kampus', 'Total_CRM', 'Belum_Diassign', 'Fresh_Total',
      'Fresh_Di_Agent', 'Tidak_Merespon', 'Minat', 'Ragu_Ragu', 'Menolak', 'Closing',
      'Rata_Skor', 'Status_Klaster', 'Rekomendasi_Admin'
    ];
    const lines = [headers.join(',')];
    CRM_CAMPUS_LIST.forEach((crm) => {
      const cl = classifyCampusCluster(crm);
      lines.push([
        crm.no, `"${crm.kampus}"`, crm.total, crm.unassigned, crm.fresh,
        crm.freshAssigned, crm.noResp, crm.minat, crm.ragu, crm.menolak, crm.closing,
        crm.avgScore, `"${cl.badge}"`, `"${cl.adminNote.replace(/"/g, '""')}"`
      ].join(','));
    });
    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'rekomendasi-crm-admin-20-kampus.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    const toast = document.getElementById('toastNotice');
    if (toast) {
      toast.classList.remove('hidden');
      setTimeout(() => toast.classList.add('hidden'), 2500);
    }
  }

  function exportSingleCampusCrmCsv() {
    const crm = CRM_CAMPUS_LIST.find((x) => x.no === state.crmSelectedCampusNo) || CRM_CAMPUS_LIST[0];
    if (!crm) return;
    const recs = buildCampusSpecificCrmRecs(crm);
    const lines = [
      `LAPORAN & REKOMENDASI OPERASIONAL CRM - ${crm.kampus}`,
      `Total Prospek,${crm.total},Belum Diassign,${crm.unassigned},Fresh Total,${crm.fresh},Tidak Merespon,${crm.noResp},Minat,${crm.minat},Ragu-ragu,${crm.ragu},Closing,${crm.closing}`,
      '',
      'REKOMENDASI PRIORITAS KAMPUS PELAKSANA:',
      'Prioritas,Judul Rekomendasi,Rincian Aksi'
    ];
    recs.forEach((r) => {
      lines.push(`"${r.priority}","${r.title.replace(/"/g, '""')}","${r.detail.replace(/"/g, '""')}"`);
    });
    lines.push('', 'DAFTAR EVENT KAMPUS:', 'Kode Event,Nama Event,Kategori,Tanggal,Total,Unassigned,Fresh,Tidak Merespon,Minat/Ragu,Menolak,Closing');
    crm.events.forEach((e) => {
      lines.push(`"${e.code}","${e.name.replace(/"/g, '""')}","${e.category}","${e.date}",${e.total},${e.unassigned},${e.fresh},${e.noResp},${e.minat + e.ragu},${e.menolak},${e.closing}`);
    });
    lines.push('', 'DAFTAR AGENT CRM KAMPUS:', 'Nama Agent,Total,Fresh,Tidak Merespon,Minat/Ragu,Menolak,Closing');
    crm.agents.forEach((a) => {
      lines.push(`"${a.name}",${a.total},${a.fresh},${a.noResp},${a.minat + a.ragu},${a.menolak},${a.closing}`);
    });

    const blob = new Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `rekomendasi-crm-${crm.kampus.toLowerCase().replace(/[^a-z0-9]+/g, '-')}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);

    const toast = document.getElementById('toastNotice');
    if (toast) {
      toast.classList.remove('hidden');
      setTimeout(() => toast.classList.add('hidden'), 2500);
    }
  }

  window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.nav-tab-btn').forEach((btn) => {
      btn.addEventListener('click', () => switchTab(btn.getAttribute('data-tab')));
    });

    document.querySelectorAll('.admin-rec-filter-btn').forEach((btn) => {
      btn.addEventListener('click', () => {
        state.adminRecCat = btn.getAttribute('data-rec-cat');
        document.querySelectorAll('.admin-rec-filter-btn').forEach((b) => {
          b.className = b === btn
            ? 'admin-rec-filter-btn px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-600 text-white shadow-sm'
            : 'admin-rec-filter-btn px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-700 hover:bg-slate-200';
        });
        renderAdminOverview();
      });
    });

    document.getElementById('adminChartModeVolBtn')?.addEventListener('click', () => {
      state.adminChartMode = 'VOL';
      document.getElementById('adminChartModeVolBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-slate-900 shadow-sm';
      document.getElementById('adminChartModePctBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900';
      updateAdminOverviewCharts();
    });

    document.getElementById('adminChartModePctBtn')?.addEventListener('click', () => {
      state.adminChartMode = 'PCT';
      document.getElementById('adminChartModePctBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md bg-white text-slate-900 shadow-sm';
      document.getElementById('adminChartModeVolBtn').className = 'px-2.5 py-1 text-xs font-semibold rounded-md text-slate-600 hover:text-slate-900';
      updateAdminOverviewCharts();
    });

    document.getElementById('jumpToCampusViewBtn')?.addEventListener('click', () => switchTab('campus-scoped'));
    document.getElementById('exportAdminCsvBtn1')?.addEventListener('click', exportAdminCrmCsv);
    document.getElementById('exportAdminCsvBtn2')?.addEventListener('click', exportAdminCrmCsv);

    document.getElementById('adminCrmSearchInput')?.addEventListener('input', renderAdminMatrixAndAgents);
    document.getElementById('adminCrmClusterFilter')?.addEventListener('change', renderAdminMatrixAndAgents);
    document.getElementById('adminCrmSortSelect')?.addEventListener('change', renderAdminMatrixAndAgents);
    document.getElementById('globalAgentSearchInput')?.addEventListener('input', renderGlobalAgentTable);
    document.getElementById('globalAgentFilterSelect')?.addEventListener('change', renderGlobalAgentTable);

    populateCrmCampusDropdown();
    document.getElementById('crmCampusSelect')?.addEventListener('change', (e) => {
      state.crmSelectedCampusNo = parseInt(e.target.value, 10);
      renderCrmCampusView();
    });

    document.getElementById('crmCampusQuickFilter')?.addEventListener('change', (e) => {
      state.crmCampusFilterGroup = e.target.value;
      populateCrmCampusDropdown();
      renderCrmCampusView();
    });

    document.getElementById('crmPrevCampusBtn')?.addEventListener('click', () => {
      const list = getFilteredCrmCampusList();
      if (!list.length) return;
      const idx = list.findIndex((c) => c.no === state.crmSelectedCampusNo);
      const prevIdx = (idx - 1 + list.length) % list.length;
      state.crmSelectedCampusNo = list[prevIdx].no;
      populateCrmCampusDropdown();
      renderCrmCampusView();
    });

    document.getElementById('crmNextCampusBtn')?.addEventListener('click', () => {
      const list = getFilteredCrmCampusList();
      if (!list.length) return;
      const idx = list.findIndex((c) => c.no === state.crmSelectedCampusNo);
      const nextIdx = (idx + 1) % list.length;
      state.crmSelectedCampusNo = list[nextIdx].no;
      populateCrmCampusDropdown();
      renderCrmCampusView();
    });

    document.getElementById('backToAdminViewBtn')?.addEventListener('click', () => switchTab('admin-overview'));
    document.getElementById('exportSingleCampusCrmBtn')?.addEventListener('click', exportSingleCampusCrmCsv);

    switchTab(state.activeTab);
    if (CAN_VIEW_ALL_CRM_CAMPUSES) {
      renderAdminOverview();
      renderAdminMatrixAndAgents();
    }
    renderCrmCampusView();
  });
</script>