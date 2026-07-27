<?php

/*
 * API v1 publik — produk Lapak UMKM.
 * Bagian dari OpenSID (GPL-3.0). Read-only dan tanpa data identitas warga.
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Lapak extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('lapak_model');

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);
            exit;
        }
    }

    /** GET /api/v1/lapak — hanya produk, kategori, foto, dan kontak usaha. */
    public function index(): void
    {
        $produk = $this->lapak_model->get_produk('', 1)
            ->order_by('pr.updated_at', 'desc')
            ->get()
            ->result();

        $data = array_map(function ($item): array {
            $foto = json_decode($item->foto ?? '[]', true);
            $fotoPertama = is_array($foto) ? ($foto[0] ?? null) : null;

            return [
                'id'        => (int) $item->id,
                'nama'      => (string) ($item->nama ?? ''),
                'kategori'  => (string) ($item->kategori ?? 'Lainnya'),
                'harga'     => (float) ($item->harga ?? 0),
                'satuan'    => $item->satuan ?: null,
                'deskripsi' => (string) ($item->deskripsi ?? ''),
                'foto_url'  => $fotoPertama ? base_url(LOKASI_PRODUK . rawurlencode($fotoPertama)) : null,
                'pelapak'   => (string) ($item->pelapak ?? 'Pelapak'),
                'telepon'   => $item->telepon ?: null,
            ];
        }, $produk);

        json(['data' => $data, 'message' => 'OK']);
    }
}
