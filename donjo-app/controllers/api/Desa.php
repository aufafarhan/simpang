<?php

/*
 * API v1 publik — Profil Desa.
 * Bagian dari OpenSID (GPL-3.0). Menyediakan data identitas desa dalam format JSON
 * untuk dikonsumsi frontend Next.js. Read-only.
 * Rute: GET /api/v1/desa/profil  (lihat donjo-app/config/routes.php)
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Desa extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // theme_helper tidak ikut di-autoload; media_sosial() didefinisikan di sana.
        $this->load->helper('theme');
        // menu_atas_api() — pengganti First_menu_m yang dihapus di OpenSID 2607.
        $this->load->helper('api_v1');

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);
            exit;
        }
    }

    // GET /api/v1/desa/profil
    public function profil(): void
    {
        $c = identitas(); // App\Models\Config

        $data = [
            'nama_desa'    => $c->nama_desa,
            'sebutan_desa' => setting('sebutan_desa') ?: 'desa',
            'kepala_desa'  => $c->nama_kepala_desa,
            'alamat'       => $c->alamat_kantor,
            'kode_pos'     => $c->kode_pos,
            'kecamatan'    => $c->nama_kecamatan,
            'kabupaten'    => $c->nama_kabupaten,
            'provinsi'     => $c->nama_propinsi,
            'email'        => $c->email_desa,
            'telepon'      => $c->telepon,
            'logo_url'     => ! empty($c->logo)
                ? base_url(LOKASI_LOGO_DESA . rawurlencode($c->logo))
                : null,
            'favicon_url'  => null,
            'peta'         => [
                'lat'  => $c->lat !== null ? (float) $c->lat : null,
                'lng'  => $c->lng !== null ? (float) $c->lng : null,
                'zoom' => $c->zoom !== null ? (int) $c->zoom : 13,
            ],
            'tema'         => [
                'warna_utama' => '#14396b',
                'judul_web'   => setting('judul_website') ?: ('Website Resmi ' . $c->nama_desa),
            ],
            'sosial_media' => media_sosial(),

            // Menu navigasi asli dari OpenSID (Pemerintahan, Potensi Desa, dst.)
            'menu_atas'    => menu_atas_api(),

            // Menu ringkas milik frontend baru (halaman yang sudah tersedia di JS)
            'menu'         => [
                ['judul' => 'Beranda', 'url' => '/', 'urut' => 1],
                ['judul' => 'Profil', 'url' => '/profil', 'urut' => 2],
                ['judul' => 'Statistik', 'url' => '/statistik', 'urut' => 3],
                ['judul' => 'Buku Tamu', 'url' => '/buku-tamu', 'urut' => 4],
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
