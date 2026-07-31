<?php

/*
 * API v1 publik — Pembangunan.
 * Bagian dari OpenSID (GPL-3.0). Read-only.
 * Rute:
 *   GET /api/v1/pembangunan       -> index()
 *   GET /api/v1/pembangunan/{id}  -> detail()
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Pembangunan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pembangunan_model');
        $this->load->model('pembangunan_dokumentasi_model');

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);
            exit;
        }
    }

    /**
     * GET /api/v1/pembangunan?page=1&cari=&tahun=
     *
     * Catatan penting soal `set_tipe('')`:
     * Pembangunan_model default ke tipe 'rencana', yang MENYARING hanya proyek
     * tanpa progres (`d.persentase IS NULL`). Untuk halaman publik kita butuh
     * SEMUA proyek — tipe kosong berarti tanpa penyaringan (lihat get_tipe()).
     *
     * `get_data()` mengembalikan Query Builder, bukan array — wajib dieksekusi
     * dengan ->get()->result_array().
     */
    public function index(): void
    {
        $search  = trim((string) $this->input->get('cari'));
        $tahun   = trim((string) $this->input->get('tahun')) ?: 'semua';
        $page    = max(1, (int) $this->input->get('page'));
        $perPage = 12;

        $semua = $this->pembangunan_model
            ->set_tipe('')
            ->get_data($search, $tahun)
            ->get()
            ->result_array();

        $total      = count($semua);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $potongan   = array_slice($semua, ($page - 1) * $perPage, $perPage);

        json([
            'data' => array_map(fn ($p) => $this->mapProyek($p), $potongan),
            'meta' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $total,
                'total_pages' => $totalPages,
            ],
            'message' => 'OK',
        ]);
    }

    /**
     * GET /api/v1/pembangunan/{id}
     *
     * Menerima id numerik maupun slug. Pembangunan_model tidak punya
     * get_pembangunan(); kita pakai builder get_data() lalu saring.
     */
    public function detail($id = 0): void
    {
        $builder = $this->pembangunan_model->set_tipe('')->get_data();

        if (is_numeric($id)) {
            $builder->where('p.id', (int) $id);
        } else {
            $builder->where('p.slug', $id);
        }

        $proyek = $builder->get()->row_array();

        if (empty($proyek)) {
            json(['data' => null, 'message' => 'Proyek pembangunan tidak ditemukan'], 404);

            return;
        }

        // find_dokumentasi() mengembalikan objek (->result()), bukan array asosiatif.
        $dokumentasi = $this->pembangunan_dokumentasi_model->find_dokumentasi((int) $proyek['id']);

        $fotoList = array_map(function ($doc) {
            $d = (array) $doc;

            return [
                'id'         => (int) ($d['id'] ?? 0),
                'gambar_url' => $this->urlGambar($d['gambar'] ?? null),
                'persentase' => (int) str_replace('%', '', (string) ($d['persentase'] ?? 0)),
                'keterangan' => $d['keterangan'] ?? '',
                'tgl_upload' => ! empty($d['created_at']) ? date('c', strtotime($d['created_at'])) : null,
            ];
        }, $dokumentasi);

        $mapped                     = $this->mapProyek($proyek);
        $mapped['foto_dokumentasi'] = $fotoList;

        json(['data' => $mapped, 'message' => 'OK']);
    }

    /**
     * URL gambar pembangunan.
     *
     * Gambar pembangunan (cover maupun dokumentasi) disimpan di LOKASI_GALERI
     * (`desa/upload/galeri/`) — BUKAN folder `desa/upload/pembangunan/`.
     * Ini mengikuti tema resmi OpenSID (vendor/themes/esensi/partials/pembangunan/).
     * Tidak ada turunan ukuran `kecil_`/`sedang_` untuk berkas ini.
     *
     * Berkas diperiksa keberadaannya dulu supaya tidak mengirim URL rusak.
     */
    private function urlGambar(?string $namaBerkas): ?string
    {
        if (empty($namaBerkas)) {
            return null;
        }

        if (! is_file(FCPATH . LOKASI_GALERI . $namaBerkas)) {
            return null;
        }

        return base_url(LOKASI_GALERI . rawurlencode($namaBerkas));
    }

    private function mapProyek(array $p): array
    {
        $anggaran = (float) ($p['jml_anggaran'] ?? $p['anggaran'] ?? 0);
        $progres  = (int) str_replace('%', '', $p['max_persentase'] ?? '0');

        $statusStr = 'Dalam Proses';
        if ($progres >= 100) {
            $statusStr = 'Selesai';
        } elseif ($progres <= 0) {
            $statusStr = 'Perencanaan';
        }

        $coverUrl = $this->urlGambar($p['foto'] ?? null);

        return [
            'id'             => (int) $p['id'],
            'judul'          => $p['judul'] ?? '',
            'lokasi'         => $p['lokasi'] ?? '',
            'anggaran'       => $anggaran,
            'sumber_dana'    => $p['sumber_dana'] ?? '',
            'tahun_anggaran' => (int) ($p['tahun_anggaran'] ?? date('Y')),
            'pelaksana'      => $p['pelaksana_kegiatan'] ?? '',
            'progres_persen' => $progres,
            'status'         => $statusStr,
            'foto_cover'     => $coverUrl,
            'deskripsi'      => $p['keterangan'] ?? '',
        ];
    }
}
