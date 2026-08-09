<?php

/*
 * API v1 publik — Statistik.
 * Bagian dari OpenSID (GPL-3.0). Read-only, hanya data agregat (tanpa data pribadi).
 * Rute: GET /api/v1/statistik/penduduk?stat=<slug>
 *
 * Perhitungannya TIDAK ditulis ulang — seluruhnya didelegasikan ke
 * App\Services\LaporanPenduduk, mesin statistik bawaan OpenSID yang juga dipakai
 * panel admin. Jadi angka di frontend dijamin sama persis dengan angka di admin.
 *
 * `stat` menerima slug (mis. `jenis-kelamin`, `golongan-darah`, `kelas-sosial`)
 * maupun key lama (`jenis_kelamin`) supaya pemanggil versi awal tetap jalan.
 */

use App\Enums\Statistik\StatistikJenisBantuanEnum;
use App\Enums\Statistik\StatistikKeluargaEnum;
use App\Enums\Statistik\StatistikPendudukEnum;
use App\Services\LaporanPenduduk;

defined('BASEPATH') || exit('No direct script access allowed');

class Statistik extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);
            exit;
        }
    }

    // GET /api/v1/statistik/penduduk?stat=<slug>
    public function penduduk(): void
    {
        $stat = trim((string) $this->input->get('stat')) ?: 'jenis-kelamin';
        $key  = $this->keyStatistik($stat);

        if ($key === null) {
            $this->kirim(null, null, "Statistik '{$stat}' tidak dikenal.", 404);

            return;
        }

        try {
            $baris = (new LaporanPenduduk())->listData($key);
        } catch (\Throwable $e) {
            // ponytail: sebagian statistik (mis. bantuan-keluarga) melempar
            // exception saat tabel sumbernya kosong. Dikembalikan sebagai daftar
            // kosong supaya halaman menampilkan "belum ada data", bukan HTTP 500.
            // Kalau nanti datanya sudah diisi dan tetap gagal, telusuri
            // App\Services\LaporanPenduduk::select_per_kategori().
            log_message('error', "Statistik '{$key}' gagal: " . $e->getMessage());

            $this->kirim([
                'judul'    => LaporanPenduduk::judulStatistik($key),
                'total'    => 0,
                'kategori' => [],
            ]);

            return;
        }

        // listData() menyisipkan kunci 'total' di samping baris bernomor, dan
        // dua baris ringkasan (JUMLAH & BELUM MENGISI) yang tidak ikut ditampilkan
        // sebagai kategori — totalnya sudah diberikan terpisah.
        $ringkasan = $baris['total'] ?? [];
        unset($baris['total']);

        $kategori = [];

        foreach ($baris as $b) {
            $nama = trim((string) ($b['nama'] ?? ''));

            if ($nama === '' || in_array($nama, ['JUMLAH', 'BELUM MENGISI'], true)) {
                continue;
            }

            $kategori[] = [
                'label'     => $nama,
                'jumlah'    => (int) ($b['jumlah'] ?? 0),
                'laki'      => (int) ($b['laki'] ?? 0),
                'perempuan' => (int) ($b['perempuan'] ?? 0),
                'persen'    => (float) ($b['persen'] ?? 0),
            ];
        }

        $this->kirim([
            'judul'    => LaporanPenduduk::judulStatistik($key),
            'total'    => (int) ($ringkasan['jumlah'] ?? 0),
            'kategori' => $kategori,
        ]);
    }

    /**
     * Menerjemahkan slug/key dari URL menjadi key yang dipahami LaporanPenduduk.
     * Slug dicoba ke tiga enum karena himpunannya terpisah (penduduk, keluarga,
     * bantuan). Key mentah juga diterima demi kompatibilitas pemanggil lama.
     */
    private function keyStatistik(string $stat)
    {
        // Pemanggil lama memakai underscore (`jenis_kelamin`) padahal slug resmi
        // OpenSID memakai tanda hubung (`jenis-kelamin`). Disamakan di sini.
        $slug = str_replace('_', '-', $stat);

        foreach ([StatistikPendudukEnum::class, StatistikKeluargaEnum::class, StatistikJenisBantuanEnum::class] as $enum) {
            if (($key = $enum::keyFromSlug($slug)) !== null) {
                return $key;
            }

            // Pemanggil lama memakai key ber-underscore, mis. `jenis_kelamin`.
            if (array_key_exists($stat, $enum::allKeyLabel())) {
                return $stat;
            }
        }

        return null;
    }

    private function kirim($data, ?array $meta = null, string $message = 'OK', int $status = 200): void
    {
        $this->cors();
        $payload = ['data' => $data];
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }
        $payload['message'] = $message;
        json($payload, $status);
    }

    private function cors(): void
    {
        header('Access-Control-Allow-Origin: *'); // Produksi: batasi ke domain frontend
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
    }
}
