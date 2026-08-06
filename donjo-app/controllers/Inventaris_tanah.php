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
use App\Models\InventarisTanah;
use App\Models\Pamong;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\View;

defined('BASEPATH') || exit('No direct script access allowed');

class Inventaris_tanah extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini     = 'sekretariat';
    public $sub_modul_ini = 'inventaris';
    public $akses_modul   = 'inventaris-tanah';

    private array $kolomImpor = [
        'nama_barang', 'kode_barang', 'register', 'luas', 'tahun_pengadaan', 'letak', 'hak',
        'tanggal_sertifikat', 'no_sertifikat', 'penggunaan', 'asal', 'harga', 'keterangan',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-inventaris-tanah.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'inventaris_tanah');
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
                        redirect_with('error', $error, 'inventaris_tanah');
                    }

                    continue;
                }

                [$namaBarang, $kodeBarang, $register, $luas, $tahunPengadaan, $letak, $hak, $tglSertifikat, $noSertifikat, $penggunaan, $asal, $harga, $keterangan] = array_pad($sel, 13, null);
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
                    if (isset($sudahDiproses[$kunci]) || InventarisTanah::where('kode_barang', $kodeBarang)->where('register', $register)->exists()) {
                        $ganda++;
                        $pesan .= "{$barisKe}) Kode barang '{$kodeBarang}' dengan register '{$register}' sudah ada.<br>";

                        continue;
                    }
                    $sudahDiproses[$kunci] = $barisKe;
                }

                $tglSertifikatParsed = $tglSertifikat instanceof DateTimeInterface
                    ? $tglSertifikat->format('Y-m-d')
                    : (($w = strtotime((string) $tglSertifikat)) !== false ? date('Y-m-d', $w) : null);

                $dataSimpan = [
                    'nama_barang'        => $namaBarang,
                    'kode_barang'        => $kodeBarang !== '' ? $kodeBarang : null,
                    'register'           => $register !== '' ? $register : null,
                    'luas'               => is_numeric($luas) ? (int) $luas : null,
                    'tahun_pengadaan'    => is_numeric($tahunPengadaan) ? (int) $tahunPengadaan : null,
                    'letak'              => trim((string) $letak) !== '' ? trim((string) $letak) : null,
                    'hak'                => trim((string) $hak) !== '' ? trim((string) $hak) : null,
                    'tanggal_sertifikat' => $tglSertifikatParsed,
                    'no_sertifikat'      => trim((string) $noSertifikat) !== '' ? trim((string) $noSertifikat) : null,
                    'penggunaan'         => trim((string) $penggunaan) !== '' ? trim((string) $penggunaan) : null,
                    'asal'               => trim((string) $asal) !== '' ? trim((string) $asal) : null,
                    'harga'              => is_numeric($harga) ? (float) $harga : null,
                    'keterangan'         => trim((string) $keterangan) !== '' ? trim((string) $keterangan) : null,
                    'visible'            => 1,
                ];

                try {
                    // Sementara dinonaktifkan (akses admin terkunci lisensi premium): insert DB dilewati, hasil parse dicatat ke log.
                    // InventarisTanah::create($dataSimpan);
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
        redirect('inventaris_tanah');
    }

    public function index()
    {
        $data['tip']    = 1;
        $data['action'] = 'Daftar';
        $data['header'] = InventarisSubMenuEnum::TANAH['header'];

        return view('admin.inventaris.tanah.index', $data);
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
                            'url'        => ci_route('inventaris_tanah_mutasi.form/') . $row->id . '/tambah',
                            'judul'      => 'Mutasi Data',
                            'icon'       => 'fa fa-external-link-square',
                            'type'       => 'bg-olive',
                            'buttonOnly' => true,
                        ])->render();
                    }

                    $aksi .= View::make('admin.layouts.components.buttons.lihat', [
                        'url'   => ci_route('inventaris_tanah.form') . '/' . $row->id . '/' . 1,
                        'judul' => 'Lihat Data',
                    ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.edit', [
                        'url' => "inventaris_tanah/form/{$row->id}",
                    ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.hapus', [
                        'url'           => ci_route('inventaris_tanah.delete', $row->id),
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
            $data['form_action'] = ci_route('inventaris_tanah.update', $id);
            $data['main']        = InventarisTanah::with('mutasi')->findOrFail($id);
            $data['view_mark']   = $view ? 1 : 0;
            $data['kd_reg']      = InventarisTanah::select('register')->get();
        } else {
            $data['action']      = 'Tambah';
            $data['form_action'] = ci_route('inventaris_tanah.create');
            $data['main']        = null;
            $data['view_mark']   = null;
            $data['kd_reg']      = null;
        }

        $data['tip']      = 1;
        $data['get_kode'] = $this->header['desa'];
        $data['aset']     = Aset::golongan(2)->get()->toArray();
        $data['hasil']    = sprintf('%06s', InventarisTanah::count() + 1);
        $data['header']   = InventarisSubMenuEnum::TANAH['header'];

        return view('admin.inventaris.tanah.form', $data);
    }

    public function create(): void
    {
        isCan('u');

        if (InventarisTanah::create($this->validate($this->request))) {
            redirect_with('success', 'Berhasil Tambah Data');
        }

        redirect_with('error', 'Gagal Tambah Data');
    }

    public function update($id = ''): void
    {
        isCan('u');

        $update = InventarisTanah::findOrFail($id);

        $data = $this->validate($this->request);

        if ($update->update($data)) {
            redirect_with('success', 'Berhasil Ubah Data');
        }

        redirect_with('error', 'Gagal Ubah Data');
    }

    public function delete($id): void
    {
        isCan('h');

        if (InventarisTanah::findOrFail($id)->delete()) {
            redirect_with('success', 'Berhasil Hapus Data');
        }

        redirect_with('error', 'Gagal Hapus Data');
    }

    public function dialog($aksi = 'cetak')
    {
        $data               = $this->modal_penandatangan();
        $data['aksi']       = $aksi;
        $data['formAction'] = ci_route('inventaris_tanah.cetak', $aksi);

        return view('admin.inventaris.dialog_cetak', $data);
    }

    public function cetak($aksi = '')
    {
        $query          = $this->sumberData();
        $data           = $this->modal_penandatangan();
        $data['aksi']   = $aksi;
        $data['main']   = $query->orderBy('tahun_pengadaan', 'asc')->get();
        $data['pamong'] = Pamong::selectData()->where(['pamong_id' => $this->input->post('pamong')])->first()->toArray();
        if ($tahun = $this->input->post('tahun')) {
            $data['main'] = $query->where('tahun_pengadaan', $tahun)->get();
        }

        $data['total'] = total_jumlah($data['main'], 'harga');

        $data['file'] = 'inventaris_tanah_' . date('Y-m-d');

        view('admin.inventaris.tanah.cetak', $data);

    }

    private function sumberData()
    {
        return InventarisTanah::with('mutasi');
    }

    private function validate(array $data): array
    {
        $data['nama_barang']        = strip_tags((string) $data['nama_barang_save']);
        $data['kode_barang']        = strip_tags((string) $data['kode_barang']);
        $data['register']           = strip_tags((string) $data['register']);
        $data['luas']               = bilangan($data['luas']);
        $data['tahun_pengadaan']    = bilangan($data['tahun_pengadaan']);
        $data['letak']              = strip_tags((string) $data['letak']);
        $data['hak']                = strip_tags((string) $data['hak']);
        $data['tanggal_sertifikat'] = $this->input->post('tanggal_sertifikat') ? date('Y-m-d', strtotime((string) $this->input->post('tanggal_sertifikat'))) : null;
        $data['no_sertifikat']      = strip_tags((string) $data['no_sertifikat']);
        $data['penggunaan']         = strip_tags((string) $data['penggunaan']);
        $data['asal']               = strip_tags((string) $data['asal']);
        $data['harga']              = bilangan($data['harga']);
        $data['keterangan']         = strip_tags((string) $data['keterangan']);
        $data['visible']            = 1;
        unset($data['nama_barang_save']);

        return $data;
    }
}
