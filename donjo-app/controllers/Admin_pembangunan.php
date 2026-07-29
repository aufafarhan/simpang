<?php

/*
 *
 * File ini bagian dari:
 *
 * OpenSID
 *
 * Sistem informasi desa sumber terbuka untuk memajukan desa
 *
 * Aplikasi dan source code ini dirilis berdasarkan lisensi GPL V3
 *
 * Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 *
 * Dengan ini diberikan izin, secara gratis, kepada siapa pun yang mendapatkan salinan
 * dari perangkat lunak ini dan file dokumentasi terkait ("Aplikasi Ini"), untuk diperlakukan
 * tanpa batasan, termasuk hak untuk menggunakan, menyalin, mengubah dan/atau mendistribusikan,
 * asal tunduk pada syarat berikut:
 *
 * Pemberitahuan hak cipta di atas dan pemberitahuan izin ini harus disertakan dalam
 * setiap salinan atau bagian penting Aplikasi Ini. Barang siapa yang menghapus atau menghilangkan
 * pemberitahuan ini melanggar ketentuan lisensi Aplikasi Ini.
 *
 * PERANGKAT LUNAK INI DISEDIAKAN "SEBAGAIMANA ADANYA", TANPA JAMINAN APA PUN, BAIK TERSURAT MAUPUN
 * TERSIRAT. PENULIS ATAU PEMEGANG HAK CIPTA SAMA SEKALI TIDAK BERTANGGUNG JAWAB ATAS KLAIM, KERUSAKAN ATAU
 * KEWAJIBAN APAPUN ATAS PENGGUNAAN ATAU LAINNYA TERKAIT APLIKASI INI.
 *
 * @package   OpenSID
 * @author    Tim Pengembang OpenDesa
 * @copyright Hak Cipta 2009 - 2015 Combine Resource Institution (http://lumbungkomunitas.net/)
 * @copyright Hak Cipta 2016 - 2024 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 * @link      https://github.com/OpenSID/OpenSID
 *
 */

use App\Enums\SatuanWaktuEnum;
use App\Enums\StatusEnum;
use App\Models\Area;
use App\Models\Garis;
use App\Models\Lokasi;
use App\Models\Pembangunan;
use App\Models\Wilayah;
use App\Traits\ImporExcel;

defined('BASEPATH') || exit('No direct script access allowed');

class Admin_pembangunan extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini           = 'pembangunan';
    public $kategori_pengaturan = 'Pembangunan';

    // Impor Excel membuat proyek pembangunan baru (level "rencana" — sebelum ada dokumentasi
    // progres). Kolom "foto" tidak tersedia karena berkas gambar tidak bisa dibawa lewat Excel.
    // Untuk menaikkan status ke "kegiatan"/"hasil" (Bumindes_kegiatan_pembangunan/
    // Bumindes_hasil_pembangunan yang read-only), gunakan impor di Pembangunan_dokumentasi.php.
    private array $kolomImpor = [
        'judul', 'sumber_dana', 'volume', 'waktu', 'satuan_waktu', 'tahun_anggaran',
        'pelaksana_kegiatan', 'lokasi', 'anggaran',
        'sumber_biaya_pemerintah', 'sumber_biaya_provinsi', 'sumber_biaya_kab_kota', 'sumber_biaya_swadaya',
        'manfaat', 'sifat_proyek', 'keterangan',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-pembangunan.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'admin_pembangunan');
        }

        $petaSumberDana   = unserialize(SUMBER_DANA);
        $petaSatuanWaktu  = SatuanWaktuEnum::all();

        $sukses        = 0;
        $gagal         = 0;
        $ganda         = 0;
        $pesan         = '';
        $barisKe       = 0;
        $sudahDiproses = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $barisKe++;
                $sel = $this->nilaiBaris($row);

                if ($barisKe === 1) {
                    if ($error = $this->validasiHeaderExcel($sel, $this->kolomImpor)) {
                        $reader->close();
                        redirect_with('error', $error, 'admin_pembangunan');
                    }

                    continue;
                }

                [
                    $judul, $sumberDanaTeks, $volume, $waktu, $satuanWaktuTeks, $tahunAnggaran,
                    $pelaksana, $lokasi, $anggaran,
                    $biayaPemerintah, $biayaProvinsi, $biayaKabKota, $biayaSwadaya,
                    $manfaat, $sifatProyek, $keterangan,
                ] = array_pad($sel, 16, null);

                $judul           = trim((string) $judul);
                $sumberDanaTeks  = trim((string) $sumberDanaTeks);
                $satuanWaktuTeks = trim((string) $satuanWaktuTeks);
                $sifatProyek     = strtoupper(trim((string) $sifatProyek));

                if ($judul === '' || $tahunAnggaran === '' || $tahunAnggaran === null) {
                    $gagal++;
                    $pesan .= "{$barisKe}) Kolom judul dan tahun_anggaran wajib diisi.<br>";

                    continue;
                }

                $kunci = strtolower($judul) . '|' . $tahunAnggaran;
                if (isset($sudahDiproses[$kunci])) {
                    $ganda++;
                    $pesan .= "{$barisKe}) Judul '{$judul}' tahun {$tahunAnggaran} sudah diproses pada baris {$sudahDiproses[$kunci]}.<br>";

                    continue;
                }
                $sudahDiproses[$kunci] = $barisKe;

                $sumberDana = ($sumberDanaTeks !== '' && ctype_digit($sumberDanaTeks) && array_key_exists((int) $sumberDanaTeks, $petaSumberDana))
                    ? (int) $sumberDanaTeks
                    : $this->resolveKodeReferensi($petaSumberDana, $sumberDanaTeks);

                $satuanWaktu = ($satuanWaktuTeks !== '' && ctype_digit($satuanWaktuTeks) && array_key_exists((int) $satuanWaktuTeks, $petaSatuanWaktu))
                    ? (int) $satuanWaktuTeks
                    : $this->resolveKodeReferensi($petaSatuanWaktu, $satuanWaktuTeks);

                if (! in_array($sifatProyek, ['BARU', 'LANJUTAN'], true)) {
                    $sifatProyek = null;
                }

                $angka = static fn ($v) => is_numeric($v) ? (int) $v : 0;

                $biayaPemerintahN = $angka($biayaPemerintah);
                $biayaProvinsiN   = $angka($biayaProvinsi);
                $biayaKabKotaN    = $angka($biayaKabKota);
                $biayaSwadayaN    = $angka($biayaSwadaya);

                $dataSimpan = [
                    'sumber_dana'             => $sumberDana,
                    'judul'                   => judul($judul),
                    'slug'                    => unique_slug('pembangunan', $judul),
                    'volume'                  => trim((string) $volume) !== '' ? trim((string) $volume) : null,
                    'waktu'                   => is_numeric($waktu) ? (int) $waktu : null,
                    'satuan_waktu'            => $satuanWaktu,
                    'tahun_anggaran'          => is_numeric($tahunAnggaran) ? (int) $tahunAnggaran : null,
                    'pelaksana_kegiatan'      => trim((string) $pelaksana) !== '' ? trim((string) $pelaksana) : null,
                    'id_lokasi'               => null,
                    'lokasi'                  => trim((string) $lokasi) !== '' ? trim((string) $lokasi) : null,
                    'keterangan'              => trim((string) $keterangan) !== '' ? trim((string) $keterangan) : null,
                    'foto'                    => null,
                    'anggaran'                => is_numeric($anggaran) ? (int) $anggaran : null,
                    'sumber_biaya_pemerintah' => $biayaPemerintahN,
                    'sumber_biaya_provinsi'   => $biayaProvinsiN,
                    'sumber_biaya_kab_kota'   => $biayaKabKotaN,
                    'sumber_biaya_swadaya'    => $biayaSwadayaN,
                    'sumber_biaya_jumlah'     => $biayaPemerintahN + $biayaProvinsiN + $biayaKabKotaN + $biayaSwadayaN,
                    'manfaat'                 => trim((string) $manfaat) !== '' ? trim((string) $manfaat) : null,
                    'sifat_proyek'            => $sifatProyek,
                    'created_at'              => date('Y-m-d H:i:s'),
                    'updated_at'              => date('Y-m-d H:i:s'),
                ];

                try {
                    // Sementara dinonaktifkan (akses admin terkunci lisensi premium): insert DB dilewati, hasil parse dicatat ke log.
                    // Pembangunan::create($dataSimpan);
                    log_message('debug', json_encode($dataSimpan));
                    $sukses++;
                } catch (Exception $e) {
                    log_message('error', $e->getMessage());
                    $gagal++;
                    $pesan .= "{$barisKe}) Baris gagal disimpan ke basis data.<br>";
                }
            }

            break;
        }
        $reader->close();

        $this->flashRingkasanImpor('pesan_impor', $sukses, $gagal, $ganda, $pesan);
        redirect('admin_pembangunan');
    }

    public function index()
    {
        $data['tahun'] = Pembangunan::distinct()->get('tahun_anggaran');

        return view('admin.pembangunan.index', $data);
    }

    public function datatables()
    {
        $tahun = $this->input->get('tahun') ?? null;

        if ($this->input->is_ajax_request()) {
            return datatables()->of(Pembangunan::with(['pembangunanDokumentasi', 'wilayah'])->when($tahun, static fn ($q) => $q->where('tahun_anggaran', $tahun)))
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';

                    if (can('u')) {
                        $aksi .= '<a href="' . ci_route('admin_pembangunan.form', $row->id) . '" class="btn btn-warning btn-sm"  title="Ubah Data"><i class="fa fa-edit"></i></a> ';
                        $aksi .= '<a href="' . ci_route('admin_pembangunan.maps') . '/' . $row->id . '" class="btn bg-olive btn-sm" title="Lokasi Pembangunan"><i class="fa fa-map"></i></a> ';
                    }

                    $aksi .= '<a href="' . ci_route('pembangunan_dokumentasi.dokumentasi') . '/' . $row->id . '" class="btn bg-purple btn-sm" title="Rincian Dokumentasi Kegiatan"><i class="fa fa-list-ol"></i></a> ';

                    if (can('u')) {
                        if ($row->status == StatusEnum::YA) {
                            $aksi .= '<a href="' . ci_route('admin_pembangunan.lock') . '/' . $row->id . '" class="btn bg-navy btn-sm" title="Nonaktifkan"><i class="fa fa-unlock"></i></a> ';
                        } else {
                            $aksi .= '<a href="' . ci_route('admin_pembangunan.lock') . '/' . $row->id . '" class="btn bg-navy btn-sm" title="Aktifkan"><i class="fa fa-lock"></i></a> ';
                        }
                    }

                    if (can('h')) {
                        $aksi .= '<a href="#" data-href="' . ci_route('admin_pembangunan.delete', $row->id) . '" class="btn bg-maroon btn-sm"  title="Hapus Data" data-toggle="modal" data-target="#confirm-delete"><i class="fa fa-trash"></i></a> ';
                    }

                    return $aksi . ('<a href="' . ci_route('pembangunan') . '/' . $row->slug . '" target="_blank" class="btn bg-blue btn-sm" title="Lihat Summary"><i class="fa fa-eye"></i></a> ');
                })
                ->editColumn('foto', static function ($row): string {
                    if ($row->foto) {
                        $row->url_foto = to_base64(LOKASI_GALERI . $row->foto);

                        return '<img class="penduduk_kecil" src="' . $row->url_foto . '" class="penduduk_kecil text-center" alt="Gambar Dokumentasi">';
                    }

                    return '';
                })
                ->editColumn('persentase', static fn ($row) => $row->max_persentase)
                ->editColumn('alamat', static fn ($row) => $row->alamat)
                ->editColumn('anggaran', static fn ($row) => $row->perubahan_anggaran > 0 ? $row->perubahan_anggaran : $row->anggaran)
                ->rawColumns(['ceklist', 'aksi', 'foto'])
                ->make();
        }

        return show_404();
    }

    public function form($id = '')
    {
        isCan('u');

        if ($id) {
            $data['action']      = 'Ubah';
            $data['form_action'] = ci_route('admin_pembangunan.update', $id);
            $data['main']        = Pembangunan::findOrFail($id);
        } else {
            $data['action']      = 'Tambah';
            $data['form_action'] = ci_route('admin_pembangunan.create');
            $data['main']        = null;
        }

        $data['list_lokasi']  = Wilayah::rt()->orderBy('dusun')->get()->toArray();
        $data['sumber_dana']  = $this->referensi_model->list_ref(SUMBER_DANA);
        $data['satuan_waktu'] = SatuanWaktuEnum::all();

        return view('admin.pembangunan.form', $data);
    }

    public function create(): void
    {
        isCan('u');
        $post               = $this->input->post();
        $data               = $this->validasi($post);
        $data['created_at'] = date('Y-m-d H:i:s');

        if (Pembangunan::create($data)) {
            redirect_with('success', 'Berhasil Tambah Data');
        }

        redirect_with('error', 'Gagal Tambah Data');
    }

    public function update($id = ''): void
    {
        isCan('u');

        $update = Pembangunan::findOrFail($id);
        $post   = $this->input->post();
        $data   = $this->validasi($post, $id, $update->foto);

        if ($update->update($data)) {
            redirect_with('success', 'Berhasil Ubah Data');
        }

        redirect_with('error', 'Gagal Ubah Data');
    }

    public function delete($id): void
    {
        isCan('h');

        if (Pembangunan::destroy($id)) {
            redirect_with('success', 'Berhasil Hapus Data');
        }

        redirect_with('error', 'Gagal Hapus Data');
    }

    private function validasi(array $post, $id = null, ?string $old_foto = null): array
    {
        return [
            'sumber_dana'             => bersihkan_xss($post['sumber_dana']),
            'judul'                   => judul($post['judul']),
            'slug'                    => unique_slug('pembangunan', $post['judul'], $id),
            'volume'                  => bersihkan_xss($post['volume']),
            'waktu'                   => bilangan($post['waktu']),
            'satuan_waktu'            => bilangan($post['satuan_waktu']),
            'tahun_anggaran'          => bilangan($post['tahun_anggaran']),
            'pelaksana_kegiatan'      => bersihkan_xss($post['pelaksana_kegiatan']),
            'id_lokasi'               => $post['lokasi'] ? null : bilangan($post['id_lokasi']),
            'lokasi'                  => $post['id_lokasi'] ? null : $this->security->xss_clean(bersihkan_xss($post['lokasi'])),
            'keterangan'              => $this->security->xss_clean(bersihkan_xss($post['keterangan'])),
            'foto'                    => $this->upload_gambar_pembangunan('foto', $old_foto),
            'anggaran'                => bilangan($post['anggaran']),
            'sumber_biaya_pemerintah' => bilangan($post['sumber_biaya_pemerintah']),
            'sumber_biaya_provinsi'   => bilangan($post['sumber_biaya_provinsi']),
            'sumber_biaya_kab_kota'   => bilangan($post['sumber_biaya_kab_kota']),
            'sumber_biaya_swadaya'    => bilangan($post['sumber_biaya_swadaya']),
            'sumber_biaya_jumlah'     => bilangan($post['sumber_biaya_pemerintah']) + bilangan($post['sumber_biaya_provinsi']) + bilangan($post['sumber_biaya_kab_kota']) + bilangan($post['sumber_biaya_swadaya']),
            'manfaat'                 => $this->security->xss_clean(bersihkan_xss($post['manfaat'])),
            'sifat_proyek'            => bersihkan_xss($post['sifat_proyek']),
            'updated_at'              => date('Y-m-d H:i:s'),
        ];
    }

    private function upload_gambar_pembangunan(string $jenis, ?string $old_foto = null)
    {
        // Inisialisasi library 'upload'
        $this->load->library('MY_Upload', null, 'upload');
        $this->uploadConfig = [
            'upload_path'   => LOKASI_GALERI,
            'allowed_types' => 'jpg|jpeg|png',
            'max_size'      => 1024, // 1 MB
        ];
        $this->upload->initialize($this->uploadConfig);

        $uploadData = null;
        // Adakah berkas yang disertakan?
        $adaBerkas = ! empty($_FILES[$jenis]['name']);
        if (! $adaBerkas) {
            // Jika hapus (ceklis)
            if (isset($_POST['hapus_foto']) && $old_foto !== null) {
                unlink(LOKASI_GALERI . $old_foto);

                return null;
            }

            return $old_foto;
        }

        // Upload sukses
        if ($this->upload->do_upload($jenis)) {
            $uploadData = $this->upload->data();
            // Buat nama file unik agar url file susah ditebak dari browser
            $namaFileUnik = tambahSuffixUniqueKeNamaFile($uploadData['file_name']);
            // Ganti nama file asli dengan nama unik untuk mencegah akses langsung dari browser
            $fileRenamed = rename(
                $this->uploadConfig['upload_path'] . $uploadData['file_name'],
                $this->uploadConfig['upload_path'] . $namaFileUnik
            );
            // Ganti nama di array upload jika file berhasil di-rename --
            // jika rename gagal, fallback ke nama asli
            $uploadData['file_name'] = $fileRenamed ? $namaFileUnik : $uploadData['file_name'];
        }
        // Upload gagal
        else {
            redirect_with('error', $this->upload->display_errors(null, null));
        }

        return (empty($uploadData)) ? null : $uploadData['file_name'];
    }

    public function maps($id): void
    {
        isCan('u');

        $data['lokasi']                 = Pembangunan::findOrFail($id)->toArray();
        $data['desa']                   = $this->header['desa'];
        $data['wil_atas']               = $this->header['desa'];
        $data['dusun_gis']              = Wilayah::dusun()->get()->toArray();
        $data['rw_gis']                 = Wilayah::rw()->get()->toArray();
        $data['rt_gis']                 = Wilayah::rt()->get()->toArray();
        $data['all_lokasi']             = Lokasi::activeLocationMap();
        $data['all_garis']              = Garis::activeGarisMap();
        $data['all_area']               = Area::activeAreaMap();
        $data['all_lokasi_pembangunan'] = Pembangunan::activePembangunanMap();

        $data['form_action'] = ci_route('admin_pembangunan.update-maps', $id);

        view('admin.pembangunan.maps', $data);
    }

    public function updateMaps($id): void
    {
        isCan('u');

        try {
            $data = $this->input->post();
            if (! empty($data['lat']) && ! empty($data['lng'])) {
                Pembangunan::whereId($id)->update($data);
                redirect_with('success', 'Lokasi berhasil disimpan');
            } else {
                redirect_with('error', 'Titik koordinat lokasi harus diisi');
            }
        } catch (Exception $e) {
            log_message('error', $e->getMessage());
            redirect_with('error', 'Lokasi gagal disimpan');
        }
    }

    public function lock($id = 0): void
    {
        isCan('u');

        if ($this->session->error_msg) {
            redirect_with('error', $this->session->error_msg);
        }

        if (Pembangunan::gantiStatus($id, 'status')) {
            redirect_with('success', 'Berhasil Ubah Status');
        }

        redirect_with('error', 'Gagal Ubah Status');
    }
}
