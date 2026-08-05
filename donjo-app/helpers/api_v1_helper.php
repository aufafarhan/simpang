<?php

/*
 * Helper pendukung API v1 publik (frontend Next.js).
 *
 * Berkas ini BUKAN bawaan OpenSID — bagian dari lapisan API kustom bersama
 * donjo-app/controllers/api/ dan blok route `api/v1` di donjo-app/Routes/api.php.
 * Ikut sertakan bila memutakhirkan OpenSID.
 */

use App\Libraries\Shortcode;
use App\Models\Artikel;
use App\Models\Galery;
use App\Models\Kategori;
use App\Models\Komentar;
use App\Models\Menu;
use App\Models\Pembangunan;
use App\Models\PembangunanDokumentasi;
use Carbon\Carbon;
use Modules\Lapak\Models\Produk;

defined('BASEPATH') || exit('No direct script access allowed');

/*
 * CATATAN VERSI (OpenSID 2412 -> 2607)
 * -----------------------------------
 * 2607 membuang seluruh model CodeIgniter lama dan menggantinya dengan
 * Eloquent. Fungsi di bawah menggantikan model yang dihapus, dengan menjaga
 * BENTUK DATA tetap sama seperti sebelumnya supaya controller API v1 dan
 * kontrak frontend (lib/types.ts) tidak ikut berubah.
 *
 *   First_artikel_m  -> App\Models\Artikel   (scope active/onlyArticle/berdasarkan)
 *   First_menu_m     -> App\Models\Menu
 *   Shortcode_model  -> App\Libraries\Shortcode
 *
 * Penskopan config_id ditangani global scope trait App\Traits\ConfigId.
 */

if (! function_exists('shortcode_api')) {
    /** Pengganti Shortcode_model::shortcode(). */
    function shortcode_api(?string $isi): string
    {
        return (new Shortcode())->shortcode((string) $isi);
    }
}

if (! function_exists('artikel_baris_api')) {
    /**
     * Menyeragamkan satu artikel Eloquent menjadi array asosiatif dengan kunci
     * yang sama seperti keluaran First_artikel_m (dipakai mapRingkas()).
     */
    function artikel_baris_api($a): array
    {
        $tgl = $a->tgl_upload ? Carbon::parse($a->tgl_upload) : null;

        return [
            'id'          => (int) $a->id,
            'judul'       => $a->judul,
            // `slug` punya mutator di model — ambil nilai mentahnya.
            'slug'        => $a->getRawOriginal('slug'),
            'thn'         => $tgl?->format('Y'),
            'bln'         => $tgl?->format('m'),
            'hri'         => $tgl?->format('d'),
            'isi'         => $a->isi,
            'gambar'      => $a->getRawOriginal('gambar'),
            'id_kategori' => $a->id_kategori,
            // Hindari accessor $a->kategori — itu mengembalikan id/tipe, bukan nama.
            'kategori'    => $a->category->kategori ?? null,
            'tgl_upload'  => $a->tgl_upload,
            'owner'       => $a->author->nama ?? 'Admin',
            'hit'         => (int) $a->hit,
            'headline'    => (int) $a->headline,
        ];
    }
}

if (! function_exists('artikel_query_api')) {
    /** Query dasar artikel publik: aktif, sudah terbit, bukan statis/agenda/keuangan. */
    function artikel_query_api($kategori = null, ?string $cari = null)
    {
        // Sengaja TIDAK memakai scope onlyArticle(): di OpenSID 2607 tipe
        // kembaliannya salah dideklarasikan sebagai Query\Builder, padahal
        // scope Eloquent selalu mengembalikan Eloquent\Builder — memanggilnya
        // menghasilkan TypeError. Kondisinya ditulis langsung di sini.
        return Artikel::active()
            ->whereNotIn('tipe', Artikel::TIPE_NOT_IN_ARTIKEL)
            ->when($kategori !== null && $kategori !== '', static fn ($q) => $q->kategori($kategori))
            ->when($cari !== null && $cari !== '', static fn ($q) => $q->where(
                static fn ($w) => $w->where('judul', 'like', "%{$cari}%")->orWhere('isi', 'like', "%{$cari}%")
            ));
    }
}

if (! function_exists('artikel_paging_api')) {
    /**
     * Pengganti First_artikel_m::paging()/paging_kat().
     * Mengembalikan objek dengan bentuk yang sama: page, per_page, offset,
     * num_rows, num_page.
     */
    function artikel_paging_api(int $page = 1, $kategori = null, ?string $cari = null): object
    {
        $perPage = (int) (setting('web_artikel_per_page') ?: 10);
        $perPage = max(1, $perPage);
        $jml     = (int) artikel_query_api($kategori, $cari)->count();
        $numPage = max(1, (int) ceil($jml / $perPage));
        $page    = min(max(1, $page), $numPage);

        return (object) [
            'page'     => $page,
            'per_page' => $perPage,
            'offset'   => ($page - 1) * $perPage,
            'num_rows' => $jml,
            'num_page' => $numPage,
        ];
    }
}

if (! function_exists('artikel_list_api')) {
    /** Pengganti First_artikel_m::artikel_show()/list_artikel(). */
    function artikel_list_api(int $offset, int $limit, $kategori = null, ?string $cari = null): array
    {
        return artikel_query_api($kategori, $cari)
            ->with(['category:id,kategori', 'author:id,nama'])
            ->orderBy('tgl_upload', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(static fn ($a) => artikel_baris_api($a))
            ->all();
    }
}

if (! function_exists('artikel_headline_api')) {
    /** Pengganti First_artikel_m::get_headline(). */
    function artikel_headline_api(): ?array
    {
        $a = artikel_query_api()
            ->headline()
            ->with(['category:id,kategori', 'author:id,nama'])
            ->orderBy('tgl_upload', 'desc')
            ->first();

        return $a ? artikel_baris_api($a) : null;
    }
}

if (! function_exists('artikel_detail_api')) {
    /** Pengganti First_artikel_m::get_artikel() — memakai scope berdasarkan(). */
    function artikel_detail_api($thn, $bln, $hr, $url): ?array
    {
        $a = artikel_query_api()
            ->berdasarkan($thn, $bln, $hr, $url)
            ->with(['category:id,kategori', 'author:id,nama'])
            ->first();

        return $a ? artikel_baris_api($a) : null;
    }
}

if (! function_exists('artikel_hit_api')) {
    /** Pengganti First_artikel_m::hit() — menaikkan penghitung dibaca. */
    function artikel_hit_api($slug): void
    {
        Artikel::where('slug', $slug)->increment('hit');
    }
}

if (! function_exists('paging_api')) {
    /** Objek paging bersama, bentuknya sama seperti library paging CI lama. */
    function paging_api(int $jml, int $page, int $perPage): object
    {
        $perPage = max(1, $perPage);
        $numPage = max(1, (int) ceil($jml / $perPage));
        $page    = min(max(1, $page), $numPage);

        return (object) [
            'page'     => $page,
            'per_page' => $perPage,
            'offset'   => ($page - 1) * $perPage,
            'num_rows' => $jml,
            'num_page' => $numPage,
        ];
    }
}

/*
 * ---- Galeri (pengganti First_gallery_m) ----
 * Album = baris `gambar_gallery` dengan parrent = 0; isinya = anak-anaknya.
 */

if (! function_exists('galeri_query_api')) {
    function galeri_query_api(int $parent = 0)
    {
        return Galery::where('parrent', $parent)->where('enabled', 1);
    }
}

if (! function_exists('galeri_paging_api')) {
    function galeri_paging_api(int $page = 1, int $parent = 0): object
    {
        return paging_api(
            (int) galeri_query_api($parent)->count(),
            $page,
            (int) (setting('web_galeri_per_page') ?: 12)
        );
    }
}

if (! function_exists('galeri_list_api')) {
    /** Baris galeri sebagai array asosiatif (mapAlbum()/mapFoto() menerima array). */
    function galeri_list_api(int $parent, int $offset, int $limit): array
    {
        return galeri_query_api($parent)
            ->orderBy('tgl_upload', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get()
            ->map(static fn ($g) => $g->attributesToArray())
            ->all();
    }
}

if (! function_exists('galeri_album_api')) {
    /** Pengganti First_gallery_m::get_parent(). */
    function galeri_album_api(int $id): ?array
    {
        $g = Galery::where('id', $id)->first();

        return $g ? $g->attributesToArray() : null;
    }
}

/*
 * ---- Pembangunan (pengganti Pembangunan_model & Pembangunan_dokumentasi_model) ----
 * Catatan 2412: get_data() default menyaring tipe 'rencana' sehingga proyek
 * berprogres ikut hilang — karena itu dulu dipanggil set_tipe(''). Di sini
 * penyaringan tipe memang tidak dipakai sama sekali.
 */

if (! function_exists('pembangunan_baris_api')) {
    function pembangunan_baris_api($p): array
    {
        return [
            'id'                 => (int) $p->id,
            'judul'              => $p->judul,
            'slug'               => $p->slug,
            'lokasi'             => $p->lokasi_lengkap ?: $p->lokasi,
            'jml_anggaran'       => $p->sumber_dana_jumlah ?? $p->anggaran ?? 0,
            'sumber_dana'        => $p->sumber_dana,
            'tahun_anggaran'     => $p->tahun_anggaran,
            'pelaksana_kegiatan' => $p->pelaksana_kegiatan,
            'max_persentase'     => $p->max_persentase,
            'foto'               => $p->foto,
            'keterangan'         => $p->keterangan,
        ];
    }
}

if (! function_exists('pembangunan_list_api')) {
    function pembangunan_list_api(?string $cari = null, ?string $tahun = null): array
    {
        return Pembangunan::when(
            $cari !== null && $cari !== '',
            static fn ($q) => $q->where(
                static fn ($w) => $w->where('judul', 'like', "%{$cari}%")->orWhere('keterangan', 'like', "%{$cari}%")
            )
        )
            ->when(
                $tahun !== null && $tahun !== '' && $tahun !== 'semua',
                static fn ($q) => $q->where('tahun_anggaran', $tahun)
            )
            ->orderBy('tahun_anggaran', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(static fn ($p) => pembangunan_baris_api($p))
            ->all();
    }
}

if (! function_exists('pembangunan_detail_api')) {
    /** Menerima id numerik maupun slug. */
    function pembangunan_detail_api($id): ?array
    {
        $p = Pembangunan::when(
            is_numeric($id),
            static fn ($q) => $q->where('id', (int) $id),
            static fn ($q) => $q->where('slug', $id)
        )->first();

        return $p ? pembangunan_baris_api($p) : null;
    }
}

if (! function_exists('pembangunan_dokumentasi_api')) {
    /** Pengganti Pembangunan_dokumentasi_model::find_dokumentasi(). */
    function pembangunan_dokumentasi_api(int $idPembangunan): array
    {
        return PembangunanDokumentasi::where('id_pembangunan', $idPembangunan)
            ->orderBy('persentase')
            ->get()
            ->map(static fn ($d) => $d->attributesToArray())
            ->all();
    }
}

if (! function_exists('lapak_produk_api')) {
    /**
     * Pengganti Lapak_model::get_produk(). Di 2607 Lapak menjadi modul HMVC
     * (Modules/Lapak) dengan model Eloquent sendiri.
     *
     * scopeActive() pada Produk dideklarasikan `protected` sehingga tidak bisa
     * dipanggil dari luar model — syaratnya ditulis ulang di sini: produk aktif
     * milik kategori & pelapak yang juga aktif.
     */
    function lapak_produk_api(): array
    {
        return Produk::where('status', 1)
            ->whereHas('kategori', static fn ($q) => $q->where('status', 1))
            ->whereHas('pelapak', static fn ($q) => $q->where('status', 1))
            ->with(['kategori:id,kategori', 'pelapak:id,nama,telepon'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(static fn ($p) => (object) [
                'id'        => $p->id,
                'nama'      => $p->nama,
                'kategori'  => $p->kategori->kategori ?? 'Lainnya',
                'harga'     => $p->harga,
                'satuan'    => $p->satuan,
                'deskripsi' => $p->deskripsi,
                'foto'      => $p->getRawOriginal('foto'),
                'pelapak'   => $p->pelapak->nama ?? 'Pelapak',
                'telepon'   => $p->pelapak->telepon ?? null,
            ])
            ->all();
    }
}

if (! function_exists('komentar_list_api')) {
    /**
     * Pengganti First_artikel_m::list_komentar() — komentar bertingkat.
     * Hanya komentar yang sudah disetujui (status 1) yang ditampilkan.
     */
    function komentar_list_api(int $idArtikel, $parent = null): array
    {
        return Komentar::where('id_artikel', $idArtikel)
            ->where('status', 1)
            ->when($parent === null, static fn ($q) => $q->where(static fn ($w) => $w->whereNull('parent_id')->orWhere('parent_id', 0)))
            ->when($parent !== null, static fn ($q) => $q->where('parent_id', $parent))
            ->orderBy('tgl_upload')
            ->get()
            ->map(static function ($k) use ($idArtikel) {
                $baris             = $k->attributesToArray();
                $baris['children'] = komentar_list_api($idArtikel, $k->id);

                return $baris;
            })
            ->all();
    }
}

if (! function_exists('komentar_simpan_api')) {
    /** Pengganti First_artikel_m::insert_comment(). */
    function komentar_simpan_api(array $data): void
    {
        Komentar::create($data);
    }
}

if (! function_exists('kategori_list_api')) {
    /**
     * Pengganti First_menu_m::list_menu_kiri() — daftar kategori artikel.
     * Kategori::daftar() adalah padanan resmi 2607; itu pula yang dipakai
     * Web_Controller::viewShare() untuk `menu_kiri`.
     */
    function kategori_list_api(): array
    {
        $keluaran = [];

        foreach (Kategori::daftar() as $k) {
            $k = is_array($k) ? $k : (array) $k;

            if (empty($k['slug'])) {
                continue;
            }

            $keluaran[] = [
                'id'   => isset($k['id']) ? (int) $k['id'] : null,
                'nama' => $k['kategori'] ?? null,
                'slug' => $k['slug'],
            ];
        }

        return $keluaran;
    }
}

if (! function_exists('artikel_dokumen_api')) {
    /** Pengganti First_artikel_m::get_dokumen_artikel() — nama berkas lampiran. */
    function artikel_dokumen_api(int $id): ?string
    {
        return Artikel::where('id', $id)->value('dokumen');
    }
}

if (! function_exists('menu_atas_api')) {
    /**
     * Menu navigasi tingkat atas beserta submenu-nya, dalam bentuk yang
     * dikonsumsi Header.tsx di frontend: { id, nama, link, submenu[] }.
     *
     * CATATAN VERSI (2412 -> 2607): dulu disediakan
     * First_menu_m::list_menu_atas(). Model itu dihapus di 2607, jadi
     * logikanya disalin ke sini — menu aktif dengan `parrent` = 0, diurut
     * naik, tautan dilewatkan menu_slug() kecuali link_tipe 99 (eksternal).
     *
     * Penskopan config_id tidak perlu ditulis: ditangani global scope pada
     * trait App\Traits\ConfigId yang dipakai model Menu.
     */
    function menu_atas_api(): array
    {
        $tautan = static fn ($m): string => (int) $m->link_tipe === 99
            ? (string) $m->link
            : (string) menu_slug($m->link);

        return Menu::where('parrent', 0)
            ->where('enabled', 1)
            ->orderBy('urut')
            ->with(['childrens' => static fn ($q) => $q->orderBy('urut')])
            ->get()
            ->map(static fn ($m) => [
                'id'      => (string) $m->id,
                'nama'    => $m->nama,
                'link'    => $tautan($m),
                'submenu' => $m->childrens
                    ->map(static fn ($s) => [
                        'id'   => (string) $s->id,
                        'nama' => $s->nama,
                        'link' => $tautan($s),
                    ])
                    ->all(),
            ])
            ->all();
    }
}
