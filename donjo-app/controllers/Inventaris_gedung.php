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

use App\Enums\InventarisSubMenuEnum;
use App\Models\Aset;
use App\Models\InventarisGedung;
use App\Models\Pamong;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\View;

defined('BASEPATH') || exit('No direct script access allowed');

class Inventaris_gedung extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini     = 'sekretariat';
    public $sub_modul_ini = 'inventaris';
    public $akses_modul   = 'inventaris-gedung';

    private array $kolomImpor = [
        'nama_barang', 'kode_barang', 'register', 'kondisi_bangunan', 'kontruksi_bertingkat', 'kontruksi_beton',
        'luas_bangunan', 'letak', 'no_dokument', 'tanggal_dokument', 'status_tanah', 'luas_tanah', 'kode_tanah',
        'asal', 'harga', 'keterangan',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-inventaris-gedung.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'inventaris_gedung');
        }

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
                        redirect_with('error', $error, 'inventaris_gedung');
                    }

                    continue;
                }

                [$namaBarang, $kodeBarang, $register, $kondisiBangunan, $bertingkat, $beton, $luasBangunan, $letak, $noDokumen, $tglDokumen, $statusTanah, $luasTanah, $kodeTanah, $asal, $harga, $keterangan] = array_pad($sel, 16, null);
                $namaBarang = trim((string) $namaBarang);
                $kodeBarang = trim((string) $kodeBarang);
                $register   = trim((string) $register);

                if ($namaBarang === '') {
                    $gagal++;
                    $pesan .= "{$barisKe}) Kolom nama_barang wajib diisi.<br>";

                    continue;
                }

                if ($kodeBarang !== '' && $register !== '') {
                    $kunci = strtolower($kodeBarang) . '|' . strtolower($register);
                    if (isset($sudahDiproses[$kunci]) || InventarisGedung::where('kode_barang', $kodeBarang)->where('register', $register)->exists()) {
                        $ganda++;
                        $pesan .= "{$barisKe}) Kode barang '{$kodeBarang}' dengan register '{$register}' sudah ada.<br>";

                        continue;
                    }
                    $sudahDiproses[$kunci] = $barisKe;
                }

                $tglDokumenParsed = $tglDokumen instanceof DateTimeInterface
                    ? $tglDokumen->format('Y-m-d')
                    : (($w = strtotime((string) $tglDokumen)) !== false ? date('Y-m-d', $w) : null);

                $dataSimpan = [
                    'nama_barang'          => $namaBarang,
                    'kode_barang'          => $kodeBarang !== '' ? $kodeBarang : null,
                    'register'             => $register !== '' ? $register : null,
                    'kondisi_bangunan'     => trim((string) $kondisiBangunan) !== '' ? trim((string) $kondisiBangunan) : null,
                    'kontruksi_bertingkat' => trim((string) $bertingkat) !== '' ? trim((string) $bertingkat) : null,
                    'kontruksi_beton'      => trim((string) $beton) !== '' ? trim((string) $beton) : null,
                    'luas_bangunan'        => trim((string) $luasBangunan) !== '' ? trim((string) $luasBangunan) : null,
                    'letak'                => trim((string) $letak) !== '' ? trim((string) $letak) : null,
                    'no_dokument'          => trim((string) $noDokumen) !== '' ? trim((string) $noDokumen) : null,
                    'tanggal_dokument'     => $tglDokumenParsed,
                    'status_tanah'         => trim((string) $statusTanah) !== '' ? trim((string) $statusTanah) : null,
                    'luas'                 => trim((string) $luasTanah) !== '' ? trim((string) $luasTanah) : null,
                    'kode_tanah'           => trim((string) $kodeTanah) !== '' ? trim((string) $kodeTanah) : null,
                    'asal'                 => trim((string) $asal) !== '' ? trim((string) $asal) : null,
                    'harga'                => is_numeric($harga) ? (float) $harga : null,
                    'keterangan'           => trim((string) $keterangan) !== '' ? trim((string) $keterangan) : null,
                    'visible'              => 1,
                ];

                try {
                    InventarisGedung::create($dataSimpan);
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
        redirect('inventaris_gedung');
    }

    public function index()
    {
        $data['tip']    = 1;
        $data['action'] = 'Daftar';
        $data['header'] = InventarisSubMenuEnum::GEDUNG['header'];

        return view('admin.inventaris.gedung.index', $data);
    }

    public function datatables()
    {
        if ($this->input->is_ajax_request()) {
            return datatables()->of($this->sumberData())
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';

                    if (can('u') && ! $row->mutasi) {
                        $aksi .= View::make('admin.layouts.components.buttons.btn', [
                            'url'        => ci_route('inventaris_gedung_mutasi.form/') . $row->id . '/tambah',
                            'judul'      => 'Mutasi Data',
                            'icon'       => 'fa fa-external-link-square',
                            'type'       => 'bg-olive',
                            'buttonOnly' => true,
                        ])->render();
                    }

                     $aksi .= View::make('admin.layouts.components.buttons.lihat', [
                         'url'   => ci_route('inventaris_gedung.form') . '/' . $row->id . '/' . 1,
                         'judul' => 'Lihat Data',
                     ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.edit', [
                        'url' => "inventaris_gedung/form/{$row->id}",
                    ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.hapus', [
                        'url'           => ci_route('inventaris_gedung.delete', $row->id),
                        'confirmDelete' => true,
                    ])->render();

                    return $aksi;
                })
                ->editColumn('kode_barang_register', static fn ($row): string => $row->kode_barang . '<br>' . $row->register)
                ->editColumn('harga', static fn ($row): string => number_format($row->harga, 0, ',', '.'))
                ->rawColumns(['aksi', 'kode_barang_register'])
                ->make();
        }

        return show_404();
    }

    public function form($id = '', $view = false)
    {
        isCan('u');

        if ($id) {
            $data['action']      = $view ? 'Rincian' : 'Ubah';
            $data['form_action'] = ci_route('inventaris_gedung.update', $id);
            $data['main']        = InventarisGedung::findOrFail($id);
            $data['view_mark']   = $view ? 1 : 0;
            $data['kd_reg']      = InventarisGedung::select('register')->get();
        } else {
            $data['action']      = 'Tambah';
            $data['form_action'] = ci_route('inventaris_gedung.create');
            $data['main']        = null;
            $data['view_mark']   = null;
            $data['kd_reg']      = null;
        }

        $data['tip']      = 1;
        $data['get_kode'] = $this->header['desa'];
        $data['aset']     = Aset::golongan(4)->get()->toArray();
        $data['hasil']    = sprintf('%06s', InventarisGedung::count() + 1);
        $data['header']   = InventarisSubMenuEnum::GEDUNG['header'];

        return view('admin.inventaris.gedung.form', $data);
    }

    public function create(): void
    {
        isCan('u');

        if (InventarisGedung::create($this->validate($this->request))) {
            redirect_with('success', 'Berhasil Tambah Data');
        }

        redirect_with('error', 'Gagal Tambah Data');
    }

    public function update($id = ''): void
    {
        isCan('u');

        $update = InventarisGedung::findOrFail($id);

        $data = $this->validate($this->request);

        if ($update->update($data)) {
            redirect_with('success', 'Berhasil Ubah Data');
        }

        redirect_with('error', 'Gagal Ubah Data');
    }

    public function delete($id): void
    {
        isCan('h');

        if (InventarisGedung::findOrFail($id)->delete()) {
            redirect_with('success', 'Berhasil Hapus Data');
        }

        redirect_with('error', 'Gagal Hapus Data');
    }

    public function dialog($aksi = 'cetak')
    {
        $data               = $this->modal_penandatangan();
        $data['aksi']       = $aksi;
        $data['formAction'] = ci_route('inventaris_gedung.cetak', $aksi);

        return view('admin.inventaris.dialog_cetak', $data);
    }

    public function cetak($aksi = '')
    {
        $query          = $this->sumberData();
        $data           = $this->modal_penandatangan();
        $data['aksi']   = $aksi;
        $data['main']   = $query->orderByRaw('YEAR(tanggal_dokument) asc')->get();
        $data['pamong'] = Pamong::selectData()->where(['pamong_id' => $this->input->post('pamong')])->first()->toArray();
        if ($tahun = $this->input->post('tahun')) {
            $data['main'] = $query->whereYear('tanggal_dokument', $tahun)->get();
        }

        $data['total'] = total_jumlah($data['main'], 'harga');

        $data['file'] = 'inventaris_gedung_' . date('Y-m-d');

        view('admin.inventaris.gedung.cetak', $data);
    }

    private function sumberData()
    {
        return InventarisGedung::with('mutasi');
    }

    private function validate(array $data): array
    {
        $data['nama_barang']          = strip_tags((string) explode('_', $data['nama_barang'])[0]);
        $data['kode_barang']          = strip_tags((string) $data['kode_barang']);
        $data['register']             = strip_tags((string) $data['register']);
        $data['kondisi_bangunan']     = strip_tags((string) $data['kondisi_bangunan']);
        $data['kontruksi_bertingkat'] = strip_tags((string) $data['tingkat']);
        $data['kontruksi_beton']      = strip_tags((string) $data['kontruksi']);
        $data['luas_bangunan']        = strip_tags((string) $data['luas_bangunan']);
        $data['letak']                = strip_tags((string) $data['alamat']);
        $data['no_dokument']          = strip_tags((string) $data['no_bangunan']);
        $data['tanggal_dokument']     = date('Y-m-d', strtotime((string) $this->input->post('tanggal_bangunan')));
        $data['status_tanah']         = strip_tags((string) $data['status_tanah']);
        $data['luas']                 = strip_tags((string) $data['luas_tanah']);
        $data['kode_tanah']           = strip_tags((string) $data['kode_tanah']);
        $data['asal']                 = strip_tags((string) $data['asal']);
        $data['harga']                = bilangan($data['harga']);
        $data['keterangan']           = strip_tags((string) $data['keterangan']);
        $data['visible']              = 1;

        return $data;
    }
}
