# Tools — Skrip Perawatan Database

Skrip CLI untuk memperbaiki masalah struktur database OpenSID.
Kredensial dibaca otomatis dari `.env` di root proyek (tidak ditulis di kode).

## Masalah yang ditangani

Database `db_nagarisimpang` di-impor dari dump yang **kehilangan definisi kunci**.
Akibatnya hampir semua tabel tidak punya `PRIMARY KEY` maupun `AUTO_INCREMENT`,
sehingga **setiap baris baru tersimpan dengan `id = 0`**.

Dampaknya ke seluruh aplikasi (termasuk panel admin OpenSID):

- Artikel, penduduk, komentar, pengaduan baru → semuanya ber-id 0
- Balasan komentar bertingkat (`parent_id`) rusak
- Hapus/ubah per-id tidak bisa diandalkan

## Perintah

Gunakan PHP 8.4 Laragon (sama seperti `serve.bat`):

```bash
C:\laragon\bin\php\php-8.4.23-Win32-vs17-x64\php.exe tools/periksa-kunci-db.php
```

| Perintah | Fungsi |
|---|---|
| `php tools/periksa-kunci-db.php` | **Hanya membaca.** Laporan tabel mana yang bermasalah + penghalang |
| `php tools/perbaiki-kunci-db.php` | **Uji coba (default).** Menampilkan SQL, tidak mengubah apa pun |
| `php tools/perbaiki-kunci-db.php --backup-saja` | Hanya membuat backup `.sql` |
| `php tools/perbaiki-kunci-db.php --jalankan` | Backup dulu, lalu terapkan perubahan |

## Pengaman pada `--jalankan`

1. **Backup otomatis wajib** ke `backup_db/` via `mysqldump`. Kalau backup gagal → **dibatalkan**, tidak ada perubahan.
2. **VIEW dikecualikan** (`dokumen_hidup`, `keluarga_aktif`) — tidak bisa diberi PK.
3. **Tabel tanpa kolom `id` dilewati.**
4. **Tabel dengan id duplikat / id 0 / NULL dilewati** — `ALTER` pasti gagal di sana.
5. **Tipe kolom asli dipertahankan** (`int`, `int unsigned`, `bigint`, …).
6. `AUTO_INCREMENT` diset ke `MAX(id) + 1` agar tidak bentrok dengan data lama.
7. Gagal per tabel dicatat, proses lanjut, ada ringkasan di akhir.

> ⚠️ DDL MySQL **tidak bisa di-rollback**. Backup adalah satu-satunya jaring pengaman.

## Memulihkan bila terjadi masalah

```bash
mysql -u root db_nagarisimpang < backup_db/db_nagarisimpang_<tanggal>.sql
```

## Verifikasi setelah perbaikan

```bash
php tools/periksa-kunci-db.php
```

Harus melaporkan **0 tabel perlu diperbaiki**. Uji juga dengan menambah satu
artikel/komentar baru dan pastikan `id`-nya bukan 0.

## Catatan

`backup_db/` sudah masuk `.gitignore` karena berisi data pribadi warga —
**jangan pernah di-commit atau di-push.**
