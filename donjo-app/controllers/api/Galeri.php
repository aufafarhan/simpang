<?php

/*
 * API v1 publik — Galeri Foto & Album.
 * Bagian dari OpenSID (GPL-3.0). Read-only.
 * Rute:
 *   GET /api/v1/galeri          -> index()
 *   GET /api/v1/galeri/{id}     -> detail()
 */

defined('BASEPATH') || exit('No direct script access allowed');

class Galeri extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        // Pengganti First_gallery_m yang dihapus di OpenSID 2607.
        $this->load->helper('api_v1');

        if (strtolower($this->input->method()) === 'options') {
            $this->cors();
            $this->output->set_status_header(204);
            exit;
        }
    }

    /**
     * GET /api/v1/galeri?page=1
     */
    public function index(): void
    {
        $page   = max(1, (int) $this->input->get('page'));
        $paging = galeri_paging_api($page);
        $items  = galeri_list_api(0, $paging->offset, $paging->per_page);

        $data = array_map(fn ($album) => $this->mapAlbum($album), $items);

        $meta = [
            'page'        => (int) $paging->page,
            'per_page'    => (int) $paging->per_page,
            'total'       => (int) $paging->num_rows,
            'total_pages' => (int) $paging->num_page,
        ];

        json(['data' => $data, 'meta' => $meta, 'message' => 'OK']);
    }

    /**
     * GET /api/v1/galeri/{id}?page=1
     */
    public function detail($id = 0): void
    {
        $id     = (int) $id;
        $parent = galeri_album_api($id);

        if (! $parent || (int) $parent['enabled'] !== 1) {
            json(['data' => null, 'message' => 'Album tidak ditemukan'], 404);
            return;
        }

        $page   = max(1, (int) $this->input->get('page'));
        $paging = galeri_paging_api($page, $id);
        $items  = galeri_list_api($id, $paging->offset, $paging->per_page);

        $fotos = array_map(fn ($f) => $this->mapFoto($f), $items);

        $album         = $this->mapAlbum($parent);
        $album['foto'] = $fotos;

        $meta = [
            'page'        => (int) $paging->page,
            'per_page'    => (int) $paging->per_page,
            'total'       => (int) $paging->num_rows,
            'total_pages' => (int) $paging->num_page,
        ];

        json(['data' => $album, 'meta' => $meta, 'message' => 'OK']);
    }

    private function mapAlbum(array $a): array
    {
        $id = (int) $a['id'];

        $count = $this->db
            ->where('parrent', $id)
            ->where('enabled', 1)
            ->count_all_results('gambar_gallery');

        $gambarUrl = ! empty($a['gambar']) ? AmbilGaleri($a['gambar'], 'sedang') : null;

        return [
            'id'          => $id,
            'nama'        => $a['nama'] ?? '',
            'gambar_url'  => $gambarUrl,
            'jumlah_foto' => $count,
            'tgl_upload'  => isset($a['tgl_upload']) ? date('c', strtotime($a['tgl_upload'])) : null,
            'slug'        => (string) $id,
        ];
    }

    private function mapFoto(array $f): array
    {
        $gambarUrl = ! empty($f['gambar']) ? AmbilGaleri($f['gambar'], 'sedang') : null;
        $jenisStr  = ((int) ($f['jenis'] ?? 1) === 2) ? 'video' : 'foto';

        return [
            'id'         => (int) $f['id'],
            'nama'       => $f['nama'] ?? '',
            'gambar_url' => $gambarUrl,
            'jenis'      => $jenisStr,
            'link'       => $f['link'] ?? null,
            'tgl_upload' => isset($f['tgl_upload']) ? date('c', strtotime($f['tgl_upload'])) : null,
        ];
    }
}
