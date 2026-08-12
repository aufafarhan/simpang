<?php

/*
 * API v1 publik — Regulasi (Produk Hukum & Informasi Publik).
 * Bagian dari OpenSID (GPL-3.0). Read-only.
 *
 * Sumbernya tabel `dokumen`, dikelompokkan lewat kolom `kategori`
 * (ref_dokumen: 1 Informasi Publik, 2 Keputusan Kepala Desa, 3 Peraturan)
 * dan `kategori_info_publik` (KategoriPublikEnum: 1 Berkala, 2 Serta-merta,
 * 3 Setiap Saat, 4 Dikecualikan).
 *
 * Rute: GET /api/v1/regulasi?jenis=produk-hukum|informasi-publik
 */

use App\Enums\KategoriPublikEnum;
use App\Models\Dokumen;

defined('BASEPATH') || exit('No direct script access allowed');

class Regulasi extends MY_Controller
{
    /** ref_dokumen.id yang dianggap Produk Hukum. */
    private const KATEGORI_PRODUK_HUKUM = [2, 3];

    /** ref_dokumen.id untuk Informasi Publik. */
    private const KATEGORI_INFORMASI_PUBLIK = 1;

    public function __construct()
    {
        parent::__construct();

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);

            exit;
        }
    }

    // GET /api/v1/regulasi?jenis=produk-hukum
    public function index(): void
    {
        $jenis = $this->input->get('jenis') ?: 'produk-hukum';

        if (! in_array($jenis, ['produk-hukum', 'informasi-publik'], true)) {
            $this->kirim(null, null, "Jenis '{$jenis}' tidak dikenal.", 404);

            return;
        }

        $infoPublik = $jenis === 'informasi-publik';

        // Filter opsional — dipakai dropdown "Tahun" & "Jenis Peraturan".
        $tahun    = trim((string) $this->input->get('tahun'));
        $kategori = trim((string) $this->input->get('kategori'));

        $dasar = static fn ($x) => $x
            ->where('enabled', Dokumen::ENABLE)
            ->where(static fn ($w) => $w->whereNull('deleted')->orWhere('deleted', 0))
            ->when(
                $infoPublik,
                static fn ($y) => $y->where('kategori', self::KATEGORI_INFORMASI_PUBLIK),
                static fn ($y) => $y->whereIn('kategori', self::KATEGORI_PRODUK_HUKUM)
            );

        // Daftar tahun untuk dropdown diambil SEBELUM filter tahun diterapkan,
        // supaya pilihannya tidak menyusut jadi satu setelah pengguna memilih.
        $tahunTersedia = $dasar(Dokumen::query())
            ->whereNotNull('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(static fn ($t) => (int) $t)
            ->values()
            ->all();

        $q = $dasar(Dokumen::query())
            ->when($tahun !== '', static fn ($x) => $x->where('tahun', $tahun))
            ->when($kategori !== '', static fn ($x) => $x->where('kategori', (int) $kategori))
            ->with('kategoriDokumen:id,nama')
            ->orderByDesc('tahun')
            ->orderByDesc('tgl_upload');

        $items = $q->get()->map(fn ($d) => [
            'id'        => (int) $d->id,
            'nama'      => $d->nama,
            'kategori'  => $d->kategoriDokumen->nama ?? null,
            // Hanya relevan untuk Informasi Publik; null pada Produk Hukum.
            'klasifikasi' => $infoPublik ? $this->labelInfoPublik($d->kategori_info_publik) : null,
            'tahun'     => $d->tahun !== null ? (int) $d->tahun : null,
            'tanggal'   => $d->tgl_upload ? date('c', strtotime((string) $d->tgl_upload)) : null,
            'keterangan' => $d->keterangan,
            // Berkas diunduh lewat controller OpenSID agar hak akses tetap
            // ditangani di sisi backend, bukan dengan menebak path berkas.
            'berkas_url' => ! empty($d->satuan)
                ? base_url('dokumen_web/unduh_berkas/' . $d->id)
                : ($d->url ?: null),
        ])->all();

        $this->kirim($items, [
            'jenis'          => $jenis,
            'total'          => count($items),
            'tahun_tersedia' => $tahunTersedia,
            // Pilihan dropdown "Jenis Peraturan" — hanya relevan di Produk Hukum.
            'jenis_peraturan' => $infoPublik ? [] : [
                ['id' => 3, 'nama' => 'Peraturan'],
                ['id' => 2, 'nama' => 'Keputusan Kepala Desa'],
            ],
        ]);
    }

    private function labelInfoPublik($kode): ?string
    {
        if ($kode === null || $kode === '') {
            return null;
        }

        return KategoriPublikEnum::allKeyLabel()[(int) $kode] ?? null;
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
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');
    }
}
