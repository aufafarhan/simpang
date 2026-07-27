<?php

/*
 * API v1 publik — Pemerintahan & SOTK.
 * Bagian dari OpenSID (GPL-3.0). Read-only.
 * Rute:
 *   GET /api/v1/pemerintahan/aparatur  -> aparatur()
 *   GET /api/v1/pemerintahan/sotk      -> sotk()
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Pemerintahan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('pamong_model');

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);
            exit;
        }
    }

    /**
     * GET /api/v1/pemerintahan/aparatur
     */
    public function aparatur(): void
    {
        $items = $this->pamong_model->list_data(0, 100);

        $waliNagari = null;
        $perangkat  = [];

        foreach ($items as $item) {
            if ((int) ($item['pamong_status'] ?? 1) !== 1) {
                continue;
            }

            $mapped = $this->mapPamong($item);

            // Cek apakah Wali Nagari / Kepala Desa
            $jabatanLower = strtolower($item['jabatan'] ?? $item['pamong_jabatan'] ?? '');
            if (str_contains($jabatanLower, 'wali') || str_contains($jabatanLower, 'kepala desa')) {
                if (! $waliNagari) {
                    $waliNagari = $mapped;
                    continue;
                }
            }

            $perangkat[] = $mapped;
        }

        json([
            'data' => [
                'wali_nagari' => $waliNagari,
                'perangkat'   => $perangkat,
            ],
            'message' => 'OK',
        ]);
    }

    /**
     * GET /api/v1/pemerintahan/sotk
     *
     * Mengembalikan SATU node akar (Wali Nagari) berisi `bawahan` berjenjang,
     * bukan daftar datar — sesuai kontrak `SotkNode` di frontend.
     *
     * Hierarki dibangun dari kolom `atasan` pada tabel tweb_desa_pamong.
     * Data itu sering belum dikonfigurasi rapi oleh operator, jadi diberi
     * beberapa pengaman: rujukan ke diri sendiri, atasan yang tidak ada,
     * dan siklus (A -> B -> A) semuanya diturunkan menjadi anak langsung akar.
     */
    public function sotk(): void
    {
        $items = $this->pamong_model->list_data(0, 100);

        $aktif = array_values(array_filter(
            $items,
            static fn ($i) => (int) ($i['pamong_status'] ?? 1) === 1
        ));

        if ($aktif === []) {
            json(['data' => null, 'message' => 'Data aparatur belum tersedia']);

            return;
        }

        // --- Tentukan akar: Wali Nagari / Kepala Desa ---
        $kadesJabatan = (int) (kades()->id ?? 0);
        $akar         = null;

        foreach ($aktif as $i) {
            if ($kadesJabatan > 0 && (int) ($i['jabatan_id'] ?? 0) === $kadesJabatan) {
                $akar = (int) $i['pamong_id'];
                break;
            }
        }

        if ($akar === null) {
            foreach ($aktif as $i) {
                $j = strtolower((string) ($i['jabatan'] ?? ''));
                if (str_contains($j, 'wali') || str_contains($j, 'kepala desa')) {
                    $akar = (int) $i['pamong_id'];
                    break;
                }
            }
        }

        $akar ??= (int) $aktif[0]['pamong_id'];

        // --- Indeks node & peta atasan ---
        $node   = [];
        $atasan = [];

        foreach ($aktif as $i) {
            $id          = (int) $i['pamong_id'];
            $node[$id]   = $this->mapPamong($i) + ['bawahan' => []];
            $atasan[$id] = $i['atasan'] === null ? null : (int) $i['atasan'];
        }

        // --- Tentukan induk yang sah untuk tiap node ---
        $anak = [];

        foreach (array_keys($node) as $id) {
            if ($id === $akar) {
                continue;
            }

            $induk = $atasan[$id] ?? null;

            // Tidak sah bila: kosong, menunjuk diri sendiri, atau node tidak ada
            if ($induk === null || $induk === $id || ! isset($node[$induk])) {
                $induk = $akar;
            } elseif ($this->menimbulkanSiklus($id, $induk, $atasan, $node)) {
                $induk = $akar;
            }

            $anak[$induk][] = $id;
        }

        json(['data' => $this->rakitNode($akar, $anak, $node), 'message' => 'OK']);
    }

    /** Cek apakah menjadikan $induk sebagai atasan $id akan membentuk siklus. */
    private function menimbulkanSiklus(int $id, int $induk, array $atasan, array $node): bool
    {
        $kunjung = [];
        $kini    = $induk;

        while ($kini !== null && isset($node[$kini])) {
            if ($kini === $id || isset($kunjung[$kini])) {
                return true;
            }
            $kunjung[$kini] = true;
            $kini           = $atasan[$kini] ?? null;
        }

        return false;
    }

    /** Rakit node beserta bawahannya secara rekursif, diurutkan berdasarkan `urut`. */
    private function rakitNode(int $id, array $anak, array $node, array $sudah = []): array
    {
        $hasil       = $node[$id];
        $sudah[$id]  = true;
        $bawahan     = [];

        foreach ($anak[$id] ?? [] as $idAnak) {
            if (isset($sudah[$idAnak])) {
                continue; // pengaman terakhir terhadap rekursi tak berujung
            }
            $bawahan[] = $this->rakitNode($idAnak, $anak, $node, $sudah);
        }

        usort($bawahan, static fn ($a, $b) => ($a['urut'] ?? 99) <=> ($b['urut'] ?? 99));
        $hasil['bawahan'] = $bawahan;

        return $hasil;
    }

    private function mapPamong(array $p): array
    {
        $fotoName = $p['foto'] ?? '';
        $fotoUrl  = ! empty($fotoName) ? AmbilFoto($fotoName, 'sedang') : null;

        return [
            'id'           => (int) ($p['id'] ?? $p['pamong_id'] ?? 0),
            'nama'         => $p['nama'] ?? $p['pamong_nama'] ?? '',
            'jabatan'      => $p['jabatan'] ?? $p['pamong_jabatan'] ?? '',
            'nip_niap'     => $p['pamong_nip'] ?? $p['pamong_niap'] ?? $p['nik'] ?? '',
            'foto_url'     => $fotoUrl,
            'masa_jabatan' => $p['pamong_masa_jabatan'] ?? null,
            'deskripsi'    => $p['pamong_tupoksi'] ?? null,
            'urut'         => (int) ($p['urut'] ?? 99),
        ];
    }
}
