<?php

/*
 * API v1 publik — Artikel / Berita.
 * Bagian dari OpenSID (GPL-3.0). Read-only (kecuali komentar, menyusul).
 * Rute (lihat donjo-app/config/routes.php):
 *   GET /api/v1/artikel                              -> index()
 *   GET /api/v1/artikel/headline                     -> headline()
 *   GET /api/v1/artikel/{thn}/{bln}/{hr}/{slug}      -> detail()
 *   GET /api/v1/artikel/{id}/komentar                -> komentar()
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Artikel extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('first_artikel_m');
        $this->load->model('shortcode_model');

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);
            exit;
        }
    }

    /**
     * GET /api/v1/artikel?page=1&cari=kata&kategori=slug-atau-id
     *
     * Catatan: parameter `cari` dibaca langsung oleh model dari query string,
     * jadi tidak perlu diteruskan manual.
     */
    public function index(): void
    {
        $page     = max(1, (int) $this->input->get('page'));
        $kategori = trim((string) $this->input->get('kategori'));

        if ($kategori !== '') {
            // Daftar artikel pada satu kategori (menerima slug maupun id)
            $paging = $this->first_artikel_m->paging_kat($page, $kategori);
            $items  = $this->first_artikel_m->list_artikel(
                $paging->offset,
                $paging->per_page,
                $kategori
            );
        } else {
            $paging = $this->first_artikel_m->paging($page);
            $items  = $this->first_artikel_m->artikel_show($paging->offset, $paging->per_page);
        }

        $data = array_map(fn ($a) => $this->mapRingkas($a), $items);

        $meta = [
            'page'        => (int) $paging->page,
            'per_page'    => (int) $paging->per_page,
            'total'       => (int) $paging->num_rows,
            'total_pages' => (int) $paging->num_page,
        ];

        $this->kirim($data, $meta);
    }

    // GET /api/v1/artikel/headline
    public function headline(): void
    {
        $h    = $this->first_artikel_m->get_headline();
        $data = $h ? [$this->mapRingkas($h)] : [];
        $this->kirim($data);
    }

    // GET /api/v1/artikel/{thn}/{bln}/{hr}/{slug}
    public function detail($thn, $bln, $hr, $slug): void
    {
        $a = $this->first_artikel_m->get_artikel($thn, $bln, $hr, $slug);

        if (empty($a)) {
            $this->kirim(null, null, 'Artikel tidak ditemukan', 404);

            return;
        }

        // Catat kunjungan (dibatasi 1x per sesi di dalam model)
        $this->first_artikel_m->hit($slug);

        $isi = $this->perbaikiUrlBerkas($this->shortcode_model->shortcode($a['isi']));

        $dokumen     = [];
        $dokumenNama = null;

        try {
            $dokumenNama = $this->first_artikel_m->get_dokumen_artikel($a['id']);
        } catch (\Throwable $e) {
            $dokumenNama = null;
        }

        if (! empty($dokumenNama)) {
            $dokumen[] = [
                'id'   => (int) $a['id'],
                'nama' => $dokumenNama,
                'url'  => base_url('first/unduh_dokumen_artikel/' . $a['id']),
            ];
        }

        $base   = $this->mapRingkas($a);
        $detail = array_merge($base, [
            'isi'     => $isi,
            'agenda'  => null, // TODO: petakan tabel agenda bila strukturnya sudah dipastikan
            'dokumen' => $dokumen,
            'seo'     => [
                'title'       => $a['judul'] . ' — ' . identitas('nama_desa'),
                'description' => $this->ringkas($a['isi']),
                'og_image'    => $base['gambar_url'],
            ],
        ]);

        $this->kirim($detail);
    }

    // GET /api/v1/kategori — untuk filter kategori di halaman berita
    public function kategori(): void
    {
        $this->load->model('first_menu_m');
        $list = $this->first_menu_m->list_menu_kiri();
        $out  = [];

        foreach ((array) $list as $k) {
            $k = (array) $k;
            if (empty($k['slug'])) {
                continue;
            }
            $out[] = [
                'id'   => isset($k['id']) ? (int) $k['id'] : null,
                'nama' => $k['kategori'] ?? null,
                'slug' => $k['slug'],
            ];
        }

        $this->kirim($out);
    }

    // GET /api/v1/artikel/captcha
    public function captcha(): void
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);
        $jawaban = $num1 + $num2;
        $salt = bin2hex(random_bytes(4));
        $hash = hash('sha256', $jawaban . $salt . (config_item('encryption_key') ?: 'nagari'));
        
        $this->kirim([
            'pertanyaan' => "$num1 + $num2 = ?",
            'token' => $salt . '|' . $hash
        ]);
    }

    // GET & POST /api/v1/artikel/{id}/komentar
    public function komentar($id): void
    {
        if (strtolower($this->input->method()) === 'post') {
            $raw = file_get_contents('php://input');
            $post = json_decode($raw, true) ?: $this->input->post(null, true);
            
            $nama = $post['nama'] ?? '';
            $email = $post['email'] ?? '';
            $isi = $post['isi'] ?? '';
            $jawaban = $post['captcha_jawaban'] ?? '';
            $token = $post['captcha_token'] ?? '';
            
            if (empty($nama) || empty($email) || empty($isi)) {
                $this->kirim(null, null, 'Nama, Email, dan Komentar wajib diisi.', 400);
                return;
            }

            if (empty($token) || strpos($token, '|') === false) {
                $this->kirim(null, null, 'Sesi captcha tidak valid.', 400);
                return;
            }

            list($salt, $hash) = explode('|', $token);
            $expected = hash('sha256', $jawaban . $salt . (config_item('encryption_key') ?: 'nagari'));
            if ($hash !== $expected) {
                $this->kirim(null, null, 'Jawaban captcha salah.', 400);
                return;
            }

            $data = [
                'id_artikel' => (int) $id,
                'owner'      => $nama,
                'email'      => $email,
                'komentar'   => $isi,
                'status'     => 2, // Menunggu persetujuan
                'tgl_upload' => date('Y-m-d H:i:s')
            ];
            $this->first_artikel_m->insert_comment($data);
            
            $this->kirim(null, null, 'Komentar berhasil dikirim dan menunggu persetujuan admin.');
            return;
        }

        $list = $this->first_artikel_m->list_komentar((int) $id, null);
        $this->kirim($this->mapKomentar($list));
    }

    // ---- Pemetaan data ----

    private function mapRingkas(array $a): array
    {
        return [
            'id'         => (int) $a['id'],
            'judul'      => $a['judul'],
            'slug'       => $a['slug'],
            'url'        => '/artikel/' . $a['thn'] . '/' . $a['bln'] . '/' . $a['hri'] . '/' . $a['slug'],
            'ringkasan'  => $this->ringkas($a['isi'] ?? ''),
            // OpenSID hanya menyimpan turunan "kecil_"/"sedang_", bukan berkas asli.
            'gambar_url'    => $this->urlGambar($a['gambar'] ?? null, 'sedang_'),
            'thumbnail_url' => $this->urlGambar($a['gambar'] ?? null, 'kecil_'),
            'kategori'   => ! empty($a['id_kategori'])
                ? ['id' => (int) $a['id_kategori'], 'nama' => $a['kategori'] ?? null]
                : null,
            'tanggal'    => $this->iso($a['tgl_upload'] ?? null),
            'penulis'    => $a['owner'] ?? 'Admin',
            'dilihat'    => (int) ($a['hit'] ?? 0),
            'headline'   => (bool) ($a['headline'] ?? false),
        ];
    }

    private function mapKomentar(array $items): array
    {
        return array_map(function (array $k): array {
            return [
                'id'       => (int) $k['id'],
                'nama'     => $k['owner'] ?? 'Anonim',
                'isi'      => $k['komentar'] ?? '',
                'tanggal'  => $this->iso($k['tgl_upload'] ?? null),
                'children' => ! empty($k['children']) && is_array($k['children'])
                    ? $this->mapKomentar($k['children'])
                    : [],
            ];
        }, $items);
    }

    /**
     * Perbaiki URL berkas di dalam isi artikel.
     *
     * Editor OpenSID menyimpan URL ABSOLUT berikut nama domain saat artikel ditulis
     * (mis. https://www.nagarisimpang.web.id/assets/../desa/upload/media/foto.jpg).
     * Akibatnya gambar rusak ketika situs diakses dari domain/lingkungan lain.
     *
     * Di sini semua URL yang menunjuk ke folder unggahan desa ditulis ulang agar
     * memakai base_url() server yang sedang berjalan, apa pun domain aslinya.
     */
    private function perbaikiUrlBerkas(?string $html): string
    {
        if (empty($html)) {
            return (string) $html;
        }

        // Buang segmen "assets/../" yang menyisa dari editor
        $html = str_ireplace('/assets/../desa/', '/desa/', $html);

        // Ganti host apa pun di depan folder unggahan desa dengan host saat ini
        return preg_replace_callback(
            '#https?://[^/"\'\s]+/(desa/(?:upload|logo|pengaturan)/[^"\'\s>]*)#i',
            static fn (array $m): string => base_url($m[1]),
            $html
        ) ?? $html;
    }

    /**
     * URL gambar artikel. OpenSID menyimpan nama dasar di database, sedangkan
     * berkas fisiknya berprefiks ukuran ("kecil_" 440px / "sedang_" 880px).
     * Nama berkas bisa mengandung spasi sehingga perlu di-encode.
     */
    private function urlGambar(?string $gambar, string $ukuran = 'sedang_'): ?string
    {
        if (empty($gambar)) {
            return null;
        }

        return base_url(LOKASI_FOTO_ARTIKEL . rawurlencode($ukuran . $gambar));
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
