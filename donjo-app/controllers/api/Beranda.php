<?php

/*
 * API v1 publik — Data agregat halaman depan.
 * Bagian dari OpenSID (GPL-3.0). Read-only.
 *
 * Mengembalikan SEMUA data yang dibutuhkan beranda (slider, headline, artikel,
 * dan 14 widget sidebar) dalam satu panggilan, dengan memanfaatkan
 * Web_Controller::_get_common_data() sehingga tidak menulis ulang logika OpenSID.
 *
 * Rute: GET /api/v1/beranda?page=1
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Beranda extends Web_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('first_artikel_m');
        $this->load->model('shortcode_model');
    }

    public function index(): void
    {
        $data = [];
        // Mengisi: statistik_pengunjung, menu_atas, menu_kiri, slide_artikel,
        // slider_gambar, teks_berjalan, latar_website + seluruh data widget.
        $this->_get_common_data($data);

        // --- Artikel terkini (berpaginasi) ---
        $page   = max(1, (int) $this->input->get('page'));
        $paging = $this->first_artikel_m->paging($page);
        $items  = $this->first_artikel_m->artikel_show($paging->offset, $paging->per_page);

        $headline = $this->first_artikel_m->get_headline();

        $payload = [
            'artikel' => [
                'items' => array_map(fn ($a) => $this->mapRingkas($a), $items),
                'meta'  => [
                    'page'        => (int) $paging->page,
                    'per_page'    => (int) $paging->per_page,
                    'total'       => (int) $paging->num_rows,
                    'total_pages' => (int) $paging->num_page,
                ],
            ],
            'headline' => $headline ? $this->mapRingkas($headline) : null,
            'slider'   => $this->mapSlider($data['slide_artikel'] ?? [], $data['slider_gambar'] ?? []),

            'menu_atas'     => $data['menu_atas'] ?? [],
            'teks_berjalan' => $data['teks_berjalan'] ?? [],
            'latar_website' => ! empty($data['latar_website']) ? base_url($data['latar_website']) : null,

            'widgets' => [
                'jam_kerja'            => $data['jam_kerja'] ?? [],
                'statistik_penduduk'   => $this->mapStatPenduduk($data['stat_widget'] ?? []),
                'statistik_pengunjung' => $this->mapPengunjung($data['statistik_pengunjung'] ?? []),
                'arsip'                => [
                    'terkini' => $this->mapArsip($data['arsip_terkini'] ?? []),
                    'populer' => $this->mapArsip($data['arsip_populer'] ?? []),
                    'acak'    => $this->mapArsip($data['arsip_acak'] ?? []),
                ],
                'menu_kategori'  => $data['menu_kiri'] ?? [],
                'aparatur'       => $this->mapAparatur($data['aparatur_desa'] ?? []),
                'galeri'         => $this->mapGaleri($data['w_gal'] ?? []),
                'agenda'         => [
                    'hari_ini' => $data['hari_ini'] ?? [],
                    'yad'      => $data['yad'] ?? [],
                    'lama'     => $data['lama'] ?? [],
                ],
                'sosmed'          => $data['sosmed'] ?? [],
                'komentar'        => $data['komen'] ?? [],
                'sinergi_program' => $data['sinergi_program'] ?? null,
                'keuangan'        => $data['widget_keuangan'] ?? null,
            ],
        ];

        $this->kirim($payload);
    }

    // ---- Pemetaan ----

    private function mapSlider(array $artikel, $gambar): array
    {
        $out = [];

        foreach ($artikel as $a) {
            $out[] = [
                'judul'      => $a['judul'] ?? null,
                'url'        => isset($a['thn'], $a['bln'], $a['hri'], $a['slug'])
                    ? '/artikel/' . $a['thn'] . '/' . $a['bln'] . '/' . $a['hri'] . '/' . $a['slug']
                    : null,
                'gambar_url' => $this->urlBerkas(LOKASI_FOTO_ARTIKEL, $a['gambar'] ?? null, 'sedang_'),
            ];
        }

        return $out;
    }

    /**
     * Statistik pengunjung — HANYA angka agregat.
     * Field ip_address/os/browser milik pengunjung terakhir sengaja DIBUANG
     * agar tidak terekspos ke publik (lihat TDD §7 keamanan).
     */
    private function mapPengunjung($s): array
    {
        $s = (array) $s;

        return [
            'hari_ini' => (int) ($s['hari_ini'] ?? 0),
            'kemarin'  => (int) ($s['kemarin'] ?? 0),
            'total'    => (int) ($s['total'] ?? 0),
        ];
    }

    private function mapAparatur($a): array
    {
        $a    = (array) $a;
        $list = $a['daftar_perangkat'] ?? [];
        $out  = [];

        foreach ((array) $list as $p) {
            $p     = (array) $p;
            $out[] = [
                'id'       => isset($p['pamong_id']) ? (int) $p['pamong_id'] : null,
                'nama'     => $p['nama'] ?? null,
                'jabatan'  => $p['jabatan'] ?? null,
                'foto_url' => $p['foto'] ?? null, // sudah URL absolut dari model
            ];
        }

        return $out;
    }

    /**
     * Bangun URL gambar OpenSID.
     *
     * Penting: OpenSID hanya menyimpan turunan "kecil_" (440px) dan "sedang_" (880px),
     * berkas asli tidak disimpan. Database menyimpan nama dasar saja, sehingga
     * prefiks ukuran wajib ditambahkan. Nama berkas juga bisa mengandung spasi,
     * jadi harus di-encode agar tautannya tidak rusak.
     */
    private function urlBerkas(string $folder, ?string $namaBerkas, string $ukuran = 'sedang_'): ?string
    {
        if (empty($namaBerkas)) {
            return null;
        }

        return base_url($folder . rawurlencode($ukuran . $namaBerkas));
    }

    /**
     * Normalisasi nilai apa pun (array, stdClass, model Eloquent) menjadi array asosiatif.
     * Cast (array) tidak aman untuk model Eloquent, jadi lewat JSON.
     */
    private function toArray($v): array
    {
        if (is_array($v)) {
            return $v;
        }

        return json_decode(json_encode($v), true) ?: [];
    }

    private function mapGaleri($items): array
    {
        $out = [];

        foreach ($this->toArray($items) as $g) {
            $g   = $this->toArray($g);
            $img = $g['url_gambar'] ?? null;

            if (empty($img) && ! empty($g['gambar'])) {
                $img = $this->urlBerkas(LOKASI_GALERI, $g['gambar'], 'sedang_');
            }

            $out[] = [
                'id'         => isset($g['id']) ? (int) $g['id'] : null,
                'nama'       => $g['nama'] ?? null,
                'gambar_url' => $img,
            ];
        }

        return $out;
    }

    /**
     * Ubah struktur statistik penduduk OpenSID (berkunci 0,1,2..,'total')
     * menjadi daftar rapi untuk grafik.
     */
    private function mapStatPenduduk($stat): array
    {
        $stat  = (array) $stat;
        $total = isset($stat['total']) ? (array) $stat['total'] : null;
        $items = [];

        foreach ($stat as $key => $row) {
            if ($key === 'total') {
                continue;
            }
            $row = (array) $row;
            // Lewati baris ringkasan bawaan OpenSID
            if (in_array((int) ($row['id'] ?? 0), [666, 888], true)) {
                continue;
            }
            $items[] = [
                'label'     => $row['nama'] ?? null,
                'jumlah'    => (int) ($row['jumlah'] ?? 0),
                'laki'      => (int) ($row['laki'] ?? 0),
                'perempuan' => (int) ($row['perempuan'] ?? 0),
                'persen'    => $row['persen'] ?? null,
            ];
        }

        return [
            'judul'    => 'Jumlah Penduduk',
            'total'    => (int) ($total['jumlah'] ?? 0),
            'kategori' => $items,
        ];
    }

    private function mapArsip($items): array
    {
        $out = [];

        foreach ($this->toArray($items) as $a) {
            $a     = $this->toArray($a);
            $out[] = [
                'id'      => isset($a['id']) ? (int) $a['id'] : null,
                'judul'   => $a['judul'] ?? null,
                'url'     => isset($a['thn'], $a['bln'], $a['hri'], $a['slug'])
                    ? '/artikel/' . $a['thn'] . '/' . $a['bln'] . '/' . $a['hri'] . '/' . $a['slug']
                    : null,
                'tanggal' => $this->iso($a['tgl_upload'] ?? null),
                'dilihat' => isset($a['hit']) ? (int) $a['hit'] : 0,
            ];
        }

        return $out;
    }

    private function mapRingkas(array $a): array
    {
        return [
            'id'         => (int) $a['id'],
            'judul'      => $a['judul'],
            'slug'       => $a['slug'],
            'url'        => '/artikel/' . $a['thn'] . '/' . $a['bln'] . '/' . $a['hri'] . '/' . $a['slug'],
            'ringkasan'      => $this->ringkas($a['isi'] ?? ''),
            'gambar_url'     => $this->urlBerkas(LOKASI_FOTO_ARTIKEL, $a['gambar'] ?? null, 'sedang_'),
            'thumbnail_url'  => $this->urlBerkas(LOKASI_FOTO_ARTIKEL, $a['gambar'] ?? null, 'kecil_'),
            'kategori'   => ! empty($a['id_kategori'])
                ? ['id' => (int) $a['id_kategori'], 'nama' => $a['kategori'] ?? null]
                : null,
            'tanggal'    => $this->iso($a['tgl_upload'] ?? null),
            'penulis'    => $a['owner'] ?? 'Admin',
            'dilihat'    => (int) ($a['hit'] ?? 0),
            'headline'   => (bool) ($a['headline'] ?? false),
        ];
    }

    private function ringkas($isi): string
    {
        $t = trim(strip_tags(html_entity_decode((string) $isi)));

        return mb_substr($t, 0, 180) . (mb_strlen($t) > 180 ? '…' : '');
    }

    private function iso($tgl): ?string
    {
        if (empty($tgl)) {
            return null;
        }
        $ts = strtotime($tgl);

        return $ts ? date('c', $ts) : $tgl;
    }

    private function kirim($data, ?array $meta = null, string $message = 'OK', int $status = 200): void
    {
        header('Access-Control-Allow-Origin: *'); // Produksi: batasi ke domain frontend
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        $payload = ['data' => $data];
        if ($meta !== null) {
            $payload['meta'] = $meta;
        }
        $payload['message'] = $message;
        json($payload, $status);
    }
}
