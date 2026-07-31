<?php

/*
 * API v1 publik — Status Desa (IDM & SDGs).
 * Bagian dari OpenSID (GPL-3.0). Read-only.
 *
 * Rute:
 *   GET /api/v1/status/idm?tahun=2024  -> idm()
 *   GET /api/v1/status/sdgs            -> sdgs()
 *
 * CATATAN SUMBER DATA
 * Kedua data ini TIDAK tersimpan di tabel database. Keduanya diambil dari API
 * Kemendesa lewat helper bawaan OpenSID, lalu di-cache setahun di desa/cache/:
 *   - idm()  : https://idm.kemendesa.go.id  — pakai kode_desa Kemendagri
 *   - sdgs() : https://sid.kemendesa.go.id  — pakai kode_desa_bps (BPS)
 * Dua kode itu BERBEDA dan bukan salah data.
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Status extends MY_Controller
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

    /**
     * GET /api/v1/status/idm?tahun=2024
     */
    public function idm(): void
    {
        $kodeDesa = (string) identitas('kode_desa');
        $tersedia = $this->tahunTersedia($kodeDesa);
        $tahun    = (int) ($this->input->get('tahun') ?: ($tersedia ? max($tersedia) : (int) date('Y')));

        $hasil = idm($kodeDesa, $tahun);
        $err   = $this->pesanError($hasil);

        if ($err !== null) {
            $this->kirim(
                ['tahun' => $tahun, 'tahun_tersedia' => $tersedia],
                null,
                $err,
                503
            );

            return;
        }

        $d         = $this->toArray($hasil);
        $ringkas   = $this->toArray($d['SUMMARIES'] ?? []);
        $identitas = $this->toArray($d['IDENTITAS'] ?? []);

        // IDENTITAS bisa berbentuk [ {...} ] atau langsung {...}
        if (isset($identitas[0]) && is_array($identitas[0])) {
            $identitas = $identitas[0];
        }

        $skor = (float) ($ringkas['SKOR_SAAT_INI'] ?? 0);

        $indikator = [];

        foreach ($this->toArray($d['ROW'] ?? []) as $r) {
            $r = $this->toArray($r);

            // Kolom "YANG DAPAT MELAKSANAKAN KEGIATAN" — enam pihak, sering kosong.
            $bersih      = static fn ($v) => trim((string) ($v ?? '')) !== '' ? trim((string) $v) : null;
            $indikator[] = [
                'no'         => (int) ($r['NO'] ?? 0),
                'indikator'  => trim((string) ($r['INDIKATOR'] ?? '')),
                'skor'       => is_numeric($r['SKOR'] ?? null) ? (float) $r['SKOR'] : null,
                'keterangan' => $r['KETERANGAN'] ?? '',
                'kegiatan'   => $r['KEGIATAN'] ?? '',
                'nilai'      => $r['NILAI'] ?? null,
                'pelaksana'  => [
                    'pusat'     => $bersih($r['PUSAT'] ?? null),
                    'provinsi'  => $bersih($r['PROV'] ?? null),
                    'kabupaten' => $bersih($r['KAB'] ?? null),
                    'desa'      => $bersih($r['DESA'] ?? null),
                    'csr'       => $bersih($r['CSR'] ?? null),
                    'lainnya'   => $bersih($r['LAINNYA'] ?? null),
                ],
            ];
        }

        $this->kirim([
            'tahun'          => $tahun,
            'tahun_tersedia' => $tersedia,
            'skor'           => round($skor, 4),
            'status'         => $ringkas['STATUS'] ?? null,
            'target_status'  => $ringkas['TARGET_STATUS'] ?? null,
            'skor_minimal'   => isset($ringkas['SKOR_MINIMAL']) ? (float) $ringkas['SKOR_MINIMAL'] : null,
            'penambahan'     => isset($ringkas['PENAMBAHAN']) ? round((float) $ringkas['PENAMBAHAN'], 4) : null,
            'wilayah'        => [
                'desa'      => $identitas['nama_desa'] ?? null,
                'kecamatan' => $identitas['nama_kecamatan'] ?? null,
                'kabupaten' => $identitas['nama_kab_kota'] ?? null,
                'provinsi'  => $identitas['nama_provinsi'] ?? null,
            ],
            'indikator' => $indikator,
        ]);
    }

    /**
     * GET /api/v1/status/sdgs
     */
    public function sdgs(): void
    {
        $hasil = sdgs();
        $err   = $this->pesanError($hasil);

        if ($err !== null) {
            $this->kirim(null, null, strip_tags($err), 503);

            return;
        }

        $d     = $this->toArray($hasil);
        $goals = [];

        foreach ($this->toArray($d['data'] ?? []) as $g) {
            $g       = $this->toArray($g);
            $gambar  = $g['image'] ?? '';
            $goals[] = [
                'nomor'      => (int) ($g['goals'] ?? 0),
                'judul'      => $g['title'] ?? '',
                'skor'       => isset($g['score']) ? (float) $g['score'] : null,
                // Ikon 18 tujuan SDGs Desa dibundel lokal di assets/images/sdgs/
                'gambar_url' => $gambar !== '' ? base_url('assets/images/sdgs/' . rawurlencode($gambar)) : null,
            ];
        }

        usort($goals, static fn ($a, $b) => $a['nomor'] <=> $b['nomor']);

        $this->kirim([
            'skor_rata_rata' => isset($d['average']) ? (float) $d['average'] : null,
            'jumlah_tujuan'  => count($goals),
            'tujuan'         => $goals,
        ]);
    }

    // ------------------------------------------------------------------ bantu

    /**
     * Daftar tahun IDM yang BENAR-BENAR punya data, dibaca dari desa/cache/.
     *
     * Penting: OpenSID juga meng-cache respons GAGAL. Contohnya berkas
     * idm_2020_*.json di nagari ini hanya berisi {"error_msg": "..."} karena API
     * Kemendesa tidak punya data 2020. Tahun seperti itu dikecualikan supaya
     * pemilih tahun di frontend tidak menawarkan tahun yang pasti kosong.
     */
    private function tahunTersedia(string $kodeDesa): array
    {
        $tahun = [];

        foreach (glob(DESAPATH . 'cache/idm_*_' . $kodeDesa . '.json') ?: [] as $berkas) {
            if (! preg_match('/idm_(\d{4})_/', basename($berkas), $m)) {
                continue;
            }

            $isi = @unserialize((string) file_get_contents($berkas));
            $d   = $this->toArray($isi['data'] ?? $isi);

            // Lewati cache yang isinya error / tanpa ringkasan skor
            if (! empty($d['error_msg']) || empty($d['SUMMARIES'])) {
                continue;
            }

            $tahun[] = (int) $m[1];
        }

        sort($tahun);

        return $tahun;
    }

    /** Helper OpenSID mengembalikan objek ber-`error_msg` bila gagal. */
    private function pesanError($hasil): ?string
    {
        if (is_object($hasil) && ! empty($hasil->error_msg)) {
            return (string) $hasil->error_msg;
        }

        if (is_array($hasil) && ! empty($hasil['error_msg'])) {
            return (string) $hasil['error_msg'];
        }

        if (empty($hasil)) {
            return 'Data belum tersedia.';
        }

        return null;
    }

    /** Normalisasi objek/array bersarang menjadi array asosiatif. */
    private function toArray($v): array
    {
        if (is_array($v)) {
            return $v;
        }

        return json_decode(json_encode($v), true) ?: [];
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
