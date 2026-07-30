<?php

/*
 *
 * File ini bagian dari:
 *
 * OpenSID
 *
 * Sistem informasi desa sumber terbuka untuk memajukan desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * @copyright Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 * @link      https://github.com/OpenSID/OpenSID
 *
 */

// API v1 publik — untuk frontend Next.js (read-only).
// Controller di controllers/api/. Lihat docs/TDD-Frontend-Publik-NextJS.md
Route::group('api/v1', ['namespace' => 'api'], static function (): void {
    // Profil desa
    Route::get('desa/profil', 'Desa@profil');

    // Agregat halaman depan (slider, headline, artikel + semua widget sidebar)
    Route::get('beranda', 'Beranda@index');

    // Daftar kategori (untuk filter halaman berita)
    Route::get('kategori', 'Artikel@kategori');

    // Artikel / berita — rute spesifik didahulukan sebelum pola dinamis
    Route::get('artikel', 'Artikel@index');
    Route::get('artikel/headline', 'Artikel@headline');
    Route::get('artikel/captcha', 'Artikel@captcha');

    // Komentar: GET untuk membaca, POST untuk mengirim.
    // OPTIONS WAJIB didaftarkan — browser mengirim preflight CORS sebelum POST
    // ber-Content-Type: application/json. Tanpa ini preflight 404 dan
    // browser menolak request dengan pesan "Failed to fetch".
    Route::any('artikel/{id}/komentar', 'Artikel@komentar');

    Route::get('artikel/{thn}/{bln}/{hr}/{slug}', 'Artikel@detail');

    // Statistik
    Route::get('statistik/penduduk', 'Statistik@penduduk');

    // Galeri Foto & Album
    Route::get('galeri', 'Galeri@index');
    Route::get('galeri/{id}', 'Galeri@detail');

    // Pemerintahan & SOTK
    Route::get('pemerintahan/aparatur', 'Pemerintahan@aparatur');
    Route::get('pemerintahan/sotk', 'Pemerintahan@sotk');

    // Pembangunan
    Route::get('pembangunan', 'Pembangunan@index');
    Route::get('pembangunan/{id}', 'Pembangunan@detail');

    // Lapak UMKM
    Route::get('lapak', 'Lapak@index');

    // Status Desa (IDM & SDGs) — data dari API Kemendesa, di-cache OpenSID
    Route::get('status/idm', 'Status@idm');
    Route::get('status/sdgs', 'Status@sdgs');
});

// Internal API
Route::group('internal_api', ['namespace' => 'internal_api'], static function (): void {
    // Wilayah
    Route::get('wilayah/get_rw', 'Wilayah@get_rw');
    Route::get('wilayah/get_rt', 'Wilayah@get_rt');
    Route::get('apipenduduksuplemen', 'Suplemen@apipenduduksuplemen');

    // Rute untuk PPID
    Route::get('ppid', 'Api_informasi_publik@ppid');
});

// Eksternal API
Route::group('external_api', ['namespace' => 'external_api'], static function (): void {
    // Sign
    Route::get('sign/pdf', 'Sign@pdf');
    // Surat Kecamatan
    Route::group('surat_kecamatan', static function (): void {
        Route::post('/kirim', 'Surat_kecamatan@kirim');
        Route::get('/download/{jenis}/{nomor}/{desa}/{bulan}/{tahun}', 'Surat_kecamatan@download');
    });

    // TTE
    Route::group('tte', static function (): void {
        Route::get('/periksa_status/{nik?}', 'Tte@periksa_status');
        Route::post('/sign_invisible', 'Tte@sign_invisible');
        Route::post('/sign_visible', 'Tte@sign_visible');
    });
});
