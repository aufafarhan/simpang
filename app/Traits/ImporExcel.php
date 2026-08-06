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

namespace App\Traits;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\XLSX\Reader;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

/**
 * Helper bersama untuk fitur impor Excel (.xlsx) di seluruh modul.
 *
 * Mereplikasi pola yang sudah dipakai Penduduk/Vaksin Covid: buka file dengan
 * OpenSpout, validasi header per posisi kolom, lookup kode referensi dari teks,
 * lalu flash ringkasan sukses/gagal/duplikat ke session untuk ditampilkan ulang.
 */
trait ImporExcel
{
    /**
     * Buka file .xlsx yang diunggah lewat input bernama $inputName.
     */
    protected function bukaReaderExcel(string $inputName = 'userfile'): Reader
    {
        if (! isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Berkas Excel tidak ditemukan atau gagal diunggah.');
        }

        $ekstensi = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
        if ($ekstensi !== 'xlsx') {
            throw new RuntimeException('Berkas harus berformat .xlsx (Excel 2007 ke atas).');
        }

        $reader = new Reader();
        $reader->open($_FILES[$inputName]['tmp_name']);

        return $reader;
    }

    /**
     * Ambil nilai mentah tiap sel pada satu baris OpenSpout sebagai array biasa.
     */
    protected function nilaiBaris($row): array
    {
        return array_map(
            static fn ($cell) => is_string($nilai = $cell->getValue()) ? trim($nilai) : $nilai,
            $row->getCells()
        );
    }

    /**
     * Bandingkan header sheet (posisi per kolom) dengan daftar kolom yang diharapkan.
     * Return null jika cocok, atau pesan error jika tidak.
     */
    protected function validasiHeaderExcel(array $header, array $kolomWajib): ?string
    {
        foreach ($kolomWajib as $i => $namaKolom) {
            $headerKolom = strtolower(trim((string) ($header[$i] ?? '')));
            if ($headerKolom !== strtolower($namaKolom)) {
                return 'Format kolom tidak sesuai template. Kolom ke-' . ($i + 1) . " seharusnya '{$namaKolom}'.";
            }
        }

        return null;
    }

    /**
     * Terjemahkan teks bebas (mis. dari sel Excel) ke kode referensi numerik,
     * dengan mencocokkan label pada $lookupMap (kode => label) secara case-insensitive.
     */
    protected function resolveKodeReferensi(array $lookupMap, $nilaiTeks): ?int
    {
        $nilaiTeks = strtolower(trim((string) $nilaiTeks));
        if ($nilaiTeks === '') {
            return null;
        }

        foreach ($lookupMap as $kode => $label) {
            if (strtolower(trim((string) $label)) === $nilaiTeks) {
                return (int) $kode;
            }
        }

        return null;
    }

    /**
     * Peta nik => id_penduduk untuk desa aktif, dipakai modul yang perlu
     * menerjemahkan kolom NIK menjadi penduduk_id/id_terdata.
     */
    protected function petaNikPenduduk(): array
    {
        $baris = $this->db
            ->select('id, nik')
            ->from('tweb_penduduk')
            ->where('config_id', $this->config_id)
            ->get()
            ->result_array();

        return array_column($baris, 'id', 'nik');
    }

    /**
     * Flash ringkasan hasil impor ke session, dibaca oleh partial
     * admin.components.impor_ringkasan pada halaman redirect.
     */
    protected function flashRingkasanImpor(string $sessionKey, int $sukses, int $gagal, int $ganda, string $pesanHtml): void
    {
        set_session($sessionKey, [
            'sukses' => $sukses,
            'gagal'  => $gagal,
            'ganda'  => $ganda,
            'pesan'  => $pesanHtml,
        ]);

        if ($gagal > 0) {
            set_session('error', "Impor selesai: {$sukses} baris berhasil, {$gagal} baris gagal.");
        } else {
            set_session('success', "Impor berhasil, {$sukses} baris data tersimpan.");
        }
    }

    /**
     * Alirkan workbook .xlsx satu-baris-header (template kosong) langsung ke browser.
     */
    protected function unduhTemplateImpor(array $daftarKolom, string $namaFile): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $namaFile . '"');
        header('Cache-Control: max-age=0');

        $writer = new Writer();
        $writer->openToBrowser($namaFile);
        $writer->addRow(Row::fromValues($daftarKolom));
        $writer->close();
        exit;
    }
}
