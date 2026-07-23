# websimpang — OpenSID (source terbaca)

Folder ini adalah **salinan lengkap** aplikasi OpenSID Premium desa
(`rilis-premiumv2412.02`), dengan **seluruh kode sumber yang tadinya di-obfuscate
kini dipulihkan menjadi source PHP yang terbaca**.

## Apa yang dilakukan

Kode asli OpenSID Premium tidak dienkripsi, hanya di-*obfuscate* berlapis
(`base64` → `zlib` → `base64` → source). Tidak diperlukan kunci apa pun
(`desa/app_key` hanya untuk session/cookie Laravel, tidak terkait).
Setiap file di-decode dengan membalik lapisan tersebut.

## Ringkasan hasil

| Bagian | Jumlah |
|---|---|
| Total file disalin | ~11.186 |
| File PHP kode inti (donjo-app, app, Modules) diproses | 2.292 |
| Di-decode dari obfuscation | 653 |
| Sudah polos (disalin apa adanya) | sisanya |
| Gagal decode | 0 |
| Error sintaks `php -l` pada hasil | 0 |
| Sisa loader obfuscation di kode inti | 0 |

Catatan: 3 file di `vendor/` (tcpdf, escpos-php) memakai `gzuncompress`/`gzinflate`
sebagai fungsi library normal — itu bukan obfuscation, dibiarkan apa adanya.

## Status

- File asli di `rilis-premiumv2412.02` **tidak diubah**.
- Ini fondasi untuk langkah berikutnya (mis. membuat frontend JS + API PHP).
- Lisensi: OpenSID GPL V3 — berhak digunakan, disalin, dan dimodifikasi.
