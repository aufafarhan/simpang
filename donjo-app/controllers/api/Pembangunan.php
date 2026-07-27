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
     * GET /api/v1/pembangunan?page=1&status=
     */
    public function index(): void
    {
        $search = trim((string) $this->input->get('cari'));
        $tahun  = trim((string) $this->input->get('tahun')) ?: 'semua';
        $items  = $this->pembangunan_model->get_data($search, $tahun);

        $data = array_map(fn ($p) => $this->mapProyek($p), $items);

        json(['data' => $data, 'message' => 'OK']);
    }

    /**
     * GET /api/v1/pembangunan/{id}
     */
    public function detail($id = 0): void
    {
        $id     = (int) $id;
        $proyek = $this->pembangunan_model->get_pembangunan($id);

        if (! $proyek) {
            json(['data' => null, 'message' => 'Proyek pembangunan tidak ditemukan'], 404);
            return;
        }

        $dokumentasi = $this->pembangunan_dokumentasi_model->get_dokumentasi($id);
        $fotoList    = array_map(function ($doc) {
            return [
                'id'         => (int) $doc['id'],
                'gambar_url' => ! empty($doc['gambar']) ? base_url('desa/upload/pembangunan/' . $doc['gambar']) : null,
                'persentase' => (int) ($doc['persentase'] ?? 0),
                'keterangan' => $doc['keterangan'] ?? '',
                'tgl_upload' => isset($doc['tgl_upload']) ? date('c', strtotime($doc['tgl_upload'])) : null,
            ];
        }, $dokumentasi);

        $mapped                     = $this->mapProyek($proyek);
        $mapped['foto_dokumentasi'] = $fotoList;

        json(['data' => $mapped, 'message' => 'OK']);
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

        $coverUrl = ! empty($p['foto']) ? base_url('desa/upload/pembangunan/' . $p['foto']) : null;

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
