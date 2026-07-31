<?php

/*
 * API v1 publik — Statistik Penduduk.
 * Bagian dari OpenSID (GPL-3.0). Read-only, hanya data agregat (tanpa data pribadi).
 * Rute: GET /api/v1/statistik/penduduk?stat=jenis_kelamin
 *
 * Catatan: versi awal mendukung stat=jenis_kelamin. Kategori lain (usia, pendidikan,
 * pekerjaan, dsb.) bisa ditambahkan memanfaatkan App\Services\LaporanPenduduk.
 */

use App\Models\Penduduk;

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

    // GET /api/v1/statistik/penduduk?stat=jenis_kelamin
    public function penduduk(): void
    {
        $stat = $this->input->get('stat') ?: 'jenis_kelamin';

        if ($stat !== 'jenis_kelamin') {
            $this->kirim(null, null, "Statistik '{$stat}' belum didukung. Gunakan stat=jenis_kelamin.", 400);

            return;
        }

        // Hanya penduduk berstatus hidup (status_dasar = 1)
        $laki      = Penduduk::where('status_dasar', 1)->where('sex', 1)->count();
        $perempuan = Penduduk::where('status_dasar', 1)->where('sex', 2)->count();
        $total     = $laki + $perempuan;

        $persen = static fn (int $n): float => $total > 0 ? round($n / $total * 100, 1) : 0.0;

        $data = [
            'judul'    => 'Penduduk Menurut Jenis Kelamin',
            'total'    => $total,
            'kategori' => [
                ['label' => 'Laki-laki', 'jumlah' => $laki, 'persen' => $persen($laki)],
                ['label' => 'Perempuan', 'jumlah' => $perempuan, 'persen' => $persen($perempuan)],
            ],
        ];

        $this->kirim($data);
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
