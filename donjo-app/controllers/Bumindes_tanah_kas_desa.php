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
 * Hak Cipta 2016 - 2025 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
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
 * @copyright Hak Cipta 2016 - 2025 Perkumpulan Desa Digital Terbuka (https://opendesa.id)
 * @license   http://www.gnu.org/licenses/gpl.html GPL V3
 * @link      https://github.com/OpenSID/OpenSID
 *
 */

use App\Enums\AsalTanahKasEnum;
use App\Enums\JenisPeraturan;
use App\Enums\PeruntukanTanahKasEnum;
use App\Models\RefDokumen;
use App\Models\RefPersilKelas;
use App\Models\TanahKasDesa;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\View;

defined('BASEPATH') || exit('No direct script access allowed');

class Bumindes_tanah_kas_desa extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini       = 'buku-administrasi-desa';
    public $sub_modul_ini   = 'administrasi-umum';
    public $aliasController = 'bumindes_tanah_kas_desa';

    private array $kolomImpor = [
        'letter_c_persil', 'nama_pemilik_asal', 'kelas', 'luas',
        'asli_milik_desa', 'pemerintah', 'provinsi', 'kabupaten_kota', 'lain_lain',
        'sawah', 'tegal', 'kebun', 'tambak_kolam', 'tanah_kering_darat',
        'ada_patok', 'tidak_ada_patok', 'ada_papan_nama', 'tidak_ada_papan_nama',
        'lokasi', 'peruntukan', 'mutasi', 'keterangan',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-bumindes-tanah-kas-desa.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'bumindes_tanah_kas_desa');
        }

        $petaKelas = RefPersilKelas::pluck('kode', 'id')->all();

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
                        redirect_with('error', $error, 'bumindes_tanah_kas_desa');
                    }

                    continue;
                }

                [
                    $letterCPersil, $namaPemilikAsal, $kelasTeks, $luas,
                    $asliMilikDesa, $pemerintah, $provinsi, $kabupatenKota, $lainLain,
                    $sawah, $tegal, $kebun, $tambakKolam, $tanahKeringDarat,
                    $adaPatok, $tidakAdaPatok, $adaPapanNama, $tidakAdaPapanNama,
                    $lokasi, $peruntukan, $mutasi, $keterangan,
                ] = array_pad($sel, 22, null);

                $letterCPersil   = preg_replace('/[^0-9]/', '', (string) $letterCPersil);
                $namaPemilikAsal = trim((string) $namaPemilikAsal);
                $kelasTeks       = trim((string) $kelasTeks);

                if ($letterCPersil === '' || $namaPemilikAsal === '') {
                    $gagal++;
                    $pesan .= "{$barisKe}) Kolom letter_c_persil dan nama_pemilik_asal wajib diisi.<br>";

                    continue;
                }

                if (isset($sudahDiproses[$letterCPersil]) || TanahKasDesa::CheckLetterC($letterCPersil)) {
                    $ganda++;
                    $pesan .= "{$barisKe}) Letter C / Persil '{$letterCPersil}' sudah digunakan.<br>";

                    continue;
                }
                $sudahDiproses[$letterCPersil] = $barisKe;

                $kelasId = $kelasTeks !== ''
                    ? ((ctype_digit($kelasTeks) && array_key_exists((int) $kelasTeks, $petaKelas)) ? (int) $kelasTeks : $this->resolveKodeReferensi($petaKelas, $kelasTeks))
                    : null;

                $angka = static fn ($v) => is_numeric($v) ? (int) $v : null;

                $dataSimpan = [
                    'nama_pemilik_asal'    => $namaPemilikAsal,
                    'letter_c'             => (int) $letterCPersil,
                    'kelas'                => $kelasId,
                    'luas'                 => $angka($luas),
                    'asli_milik_desa'      => $angka($asliMilikDesa),
                    'pemerintah'           => $angka($pemerintah),
                    'provinsi'             => $angka($provinsi),
                    'kabupaten_kota'       => $angka($kabupatenKota),
                    'lain_lain'            => $angka($lainLain),
                    'sawah'                => $angka($sawah),
                    'tegal'                => $angka($tegal),
                    'kebun'                => $angka($kebun),
                    'tambak_kolam'         => $angka($tambakKolam),
                    'tanah_kering_darat'   => $angka($tanahKeringDarat),
                    'ada_patok'            => $angka($adaPatok),
                    'tidak_ada_patok'      => $angka($tidakAdaPatok),
                    'ada_papan_nama'       => $angka($adaPapanNama),
                    'tidak_ada_papan_nama' => $angka($tidakAdaPapanNama),
                    'lokasi'               => trim((string) $lokasi) !== '' ? trim((string) $lokasi) : null,
                    'peruntukan'           => trim((string) $peruntukan) !== '' ? trim((string) $peruntukan) : null,
                    'mutasi'               => trim((string) $mutasi) !== '' ? trim((string) $mutasi) : null,
                    'keterangan'           => trim((string) $keterangan) !== '' ? trim((string) $keterangan) : null,
                    'visible'              => 1,
                ];

                try {
                    TanahKasDesa::create($dataSimpan);
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
        redirect('bumindes_tanah_kas_desa');
    }

    public function index(): void
    {
        $data['submenu']         = RefDokumen::get();
        $data['jenis_peraturan'] = JenisPeraturan::all();

        $data['main_content'] = 'admin.dokumen.tanah_kas_desa.table';
        $data['subtitle']     = 'Buku Tanah Kas ' . ucwords((string) setting('sebutan_desa'));
        $data['selected_nav'] = 'tanah_kas';

        view('admin.bumindes.umum.main', $data);
    }

    public function form($id = ''): void
    {
        isCan('u');
        if ($id) {
            $view_data = TanahKasDesa::findOrFail($id);
            $data      = [
                'main'            => $view_data,
                'main_content'    => 'admin.dokumen.tanah_kas_desa.form',
                'persil'          => RefPersilKelas::orderBy('kode')->get(),
                'list_asal_tanah' => AsalTanahKasEnum::labels(),
                'list_peruntukan' => PeruntukanTanahKasEnum::labels(),
                'subtitle'        => 'Buku Tanah Kas Desa',
                'selected_nav'    => 'tanah_kas',
                'view_mark'       => 2,
                'asal_tanah'      => $view_data->nama_pemilik_asal,
                'form_action'     => site_url("bumindes_tanah_kas_desa/update_tanah_kas_desa/{$id}"),
            ];
        } else {
            $data = [
                'main'            => null,
                'main_content'    => 'admin.dokumen.tanah_kas_desa.form',
                'persil'          => RefPersilKelas::orderBy('kode')->get(),
                'list_asal_tanah' => AsalTanahKasEnum::labels(),
                'list_peruntukan' => PeruntukanTanahKasEnum::labels(),
                'subtitle'        => 'Buku Tanah Kas Desa',
                'selected_nav'    => 'tanah_kas',
                'view_mark'       => 0,
                'form_action'     => site_url('bumindes_tanah_kas_desa/add_tanah_kas_desa'),
            ];
        }

        view('admin.bumindes.umum.main', $data);
    }

    public function datatables()
    {
        if ($this->input->is_ajax_request()) {
        return datatables()->of($this->sumberData())
            ->addIndexColumn()
            ->addColumn('aksi', static function ($row): string {
                $aksi = '';
                $aksi .= View::make('admin.layouts.components.buttons.lihat', [
                    'url'   => route('bumindes_tanah_kas_desa.view_tanah_kas_desa', ['id' => $row->id]),
                    'judul' => 'Lihat Data',
                ])->render();

                $aksi .= View::make('admin.layouts.components.buttons.edit', [
                    'url' => "bumindes_tanah_kas_desa/form/{$row->id}",
                ])->render();

                $aksi .= View::make('admin.layouts.components.buttons.hapus', [
                    'url'           => route('bumindes_tanah_kas_desa.delete_tanah_kas_desa', ['id' => $row->id]),
                    'confirmDelete' => true,
                ])->render();

                return $aksi;
            })
            ->editColumn('kode', static fn ($row) => $row->ref_persil_kelas->kode)
            ->rawColumns(['aksi', 'kode'])
            ->make();
        }

        return show_404();
    }

    public function view_tanah_kas_desa($id): void
    {
        $view_data = TanahKasDesa::findOrFail($id);
        $data      = [
            'main'            => $view_data,
            'main_content'    => 'admin.dokumen.tanah_kas_desa.form',
            'persil'          => RefPersilKelas::orderBy('kode')->get(),
            'list_asal_tanah' => AsalTanahKasEnum::labels(),
            'list_peruntukan' => PeruntukanTanahKasEnum::labels(),
            'subtitle'        => 'Buku Tanah Kas Desa',
            'selected_nav'    => 'tanah_kas',
            'view_mark'       => 1,
            'asal_tanah'      => $view_data->nama_pemilik_asal,
        ];

        view('admin.bumindes.umum.main', $data);
    }

    public function add_tanah_kas_desa(): void
    {
        isCan('u');
        $data           = $this->input->post();
        $error_validasi = $this->validasi_data($data);

        if ($error_validasi !== []) {
            foreach ($error_validasi as $error) {
                $this->session->error_msg .= ': ' . $error . '\n';
            }
            $this->session->post    = $this->input->post();
            $this->session->success = -1;

            redirect_with('error', $this->session->error_msg, site_url('bumindes_tanah_kas_desa/form'));
        }

        if (TanahKasDesa::create($data)) {
            redirect_with('success', 'Berhasil Tambah Data');
        }
        redirect_with('error', 'Gagal Tambah Data');
    }

    public function update_tanah_kas_desa($id): void
    {
        isCan('u');
        $data           = $this->input->post();
        $error_validasi = $this->validasi_data($data, $id);

        if ($error_validasi !== []) {
            foreach ($error_validasi as $error) {
                $this->session->error_msg .= ': ' . $error . '\n';
            }
            $this->session->post    = $this->input->post();
            $this->session->success = -1;

            redirect_with('error', $this->session->error_msg, site_url('bumindes_tanah_kas_desa/form/' . $id));
        }

        if (TanahKasDesa::find($id)->update($data)) {
            redirect_with('success', 'Berhasil Ubah Data');
        }
        redirect_with('error', 'Gagal Ubah Data');
    }

    public function delete_tanah_kas_desa($id): void
    {
        isCan('h');
        $tanahkas = TanahKasDesa::where('id', $id);
        if ($tanahkas->delete()) {
            redirect_with('success', 'Berhasil Hapus Data');
        }
        redirect_with('error', 'Gagal Hapus Data');

        redirect('bumindes_tanah_kas_desa');
    }

    public function dialog_cetak($aksi = 'cetak')
    {
        $data['aksi']       = $aksi;
        $data['formAction'] = ci_route('bumindes_tanah_kas_desa.cetak', $aksi);

        return view('admin.bumindes.umum.dialog', $data);
    }

    public function cetak($aksi = '')
    {
        $query = datatables($this->sumberData());

        $data              = $this->modal_penandatangan();
        $data['aksi']      = $aksi;
        $data['main']      = $query->prepareQuery()->results();
        $data['isi']       = 'admin.dokumen.tanah_kas_desa.cetak';
        $data['letak_ttd'] = ['1', '1', '23'];
        $data['bulan']     = date('m');
        $data['tahun']     = date('Y');
        $data['tgl_cetak'] = $this->request['tgl_cetak'];

        return view('admin.layouts.components.format_cetak', $data);
    }

    private function validasi_data(array &$data, $id = 0): array
    {
        $valid = [];

        // add
        if ($id == 0) {
            $check_letterc_persil = TanahKasDesa::CheckLetterC($data['letter_c_persil']);
            if ($check_letterc_persil) {
                $valid[] = "Letter C / Persil {$data['letter_c_persil']} sudah digunakan";
            }
        } else {
            // update
            $check_old_letterc_persil = TanahKasDesa::CheckOldLetterC($id, $data['letter_c_persil']);
            if (! $check_old_letterc_persil) {
                $check_letterc_persil = TanahKasDesa::CheckLetterC($data['letter_c_persil']);
                if ($check_letterc_persil) {
                    $valid[] = "Letter C / Persil {$data['letter_c_persil']} sudah digunakan";
                }
            }
        }

        $data['nama_pemilik_asal']    = strip_tags((string) $data['pemilik_asal']);
        $data['letter_c']             = bilangan($data['letter_c_persil']);
        $data['kelas']                = strip_tags((string) $data['kelas']);
        $data['luas']                 = bilangan($data['luas']);
        $data['asli_milik_desa']      = bilangan($data['asli_milik_desa']);
        $data['pemerintah']           = bilangan($data['pemerintah']);
        $data['provinsi']             = bilangan($data['provinsi']);
        $data['kabupaten_kota']       = bilangan($data['kabupaten_kota']);
        $data['lain_lain']            = bilangan($data['lain_lain']);
        $data['sawah']                = bilangan($data['sawah']);
        $data['tegal']                = bilangan($data['tegal']);
        $data['kebun']                = bilangan($data['kebun']);
        $data['tambak_kolam']         = bilangan($data['tambak_kolam']);
        $data['tanah_kering_darat']   = bilangan($data['tanah_kering_darat']);
        $data['ada_patok']            = bilangan($data['ada_patok']);
        $data['tidak_ada_patok']      = bilangan($data['tidak_ada_patok']);
        $data['ada_papan_nama']       = bilangan($data['ada_papan_nama']);
        $data['tidak_ada_papan_nama'] = bilangan($data['tidak_ada_papan_nama']);
        $data['lokasi']               = strip_tags((string) $data['lokasi']);
        $data['peruntukan']           = strip_tags((string) $data['peruntukan']);
        $data['mutasi']               = strip_tags((string) $data['mutasi']);
        $data['keterangan']           = strip_tags((string) $data['keterangan']);
        $data['tanggal_perolehan']    = rev_tgl($data['tanggal_perolehan']);
        $data['visible']              = 1;

        if ($valid !== []) {
            $this->session->validation_error = true;
        }

        return $valid;
    }

    private function sumberData()
    {
        return TanahKasDesa::visible();
    }
}
