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
use App\Models\InventarisJalan;
use App\Models\MutasiInventarisJalan;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\View;

defined('BASEPATH') || exit('No direct script access allowed');

class Inventaris_jalan extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini     = 'sekretariat';
    public $sub_modul_ini = 'inventaris';
    public $akses_modul   = 'inventaris-jalan';

    private array $kolomImpor = [
        'nama_barang', 'kode_barang', 'register', 'kondisi', 'kontruksi', 'panjang', 'lebar', 'luas',
        'letak', 'no_dokument', 'tanggal_dokument', 'status_tanah', 'kode_tanah', 'asal', 'harga', 'keterangan',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-inventaris-jalan.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'inventaris_jalan');
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
                        redirect_with('error', $error, 'inventaris_jalan');
                    }

                    continue;
                }

                [$namaBarang, $kodeBarang, $register, $kondisi, $kontruksi, $panjang, $lebar, $luas, $letak, $noDokumen, $tglDokumen, $statusTanah, $kodeTanah, $asal, $harga, $keterangan] = array_pad($sel, 16, null);
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
                    if (isset($sudahDiproses[$kunci]) || InventarisJalan::where('kode_barang', $kodeBarang)->where('register', $register)->exists()) {
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
                    'nama_barang'      => $namaBarang,
                    'kode_barang'      => $kodeBarang !== '' ? $kodeBarang : null,
                    'register'         => $register !== '' ? $register : null,
                    'kondisi'          => trim((string) $kondisi) !== '' ? trim((string) $kondisi) : null,
                    'kontruksi'        => trim((string) $kontruksi) !== '' ? trim((string) $kontruksi) : null,
                    'panjang'          => is_numeric($panjang) ? (float) $panjang : null,
                    'lebar'            => is_numeric($lebar) ? (float) $lebar : null,
                    'luas'             => is_numeric($luas) ? (float) $luas : null,
                    'letak'            => trim((string) $letak) !== '' ? trim((string) $letak) : null,
                    'no_dokument'      => trim((string) $noDokumen) !== '' ? trim((string) $noDokumen) : null,
                    'tanggal_dokument' => $tglDokumenParsed,
                    'status_tanah'     => trim((string) $statusTanah) !== '' ? trim((string) $statusTanah) : null,
                    'kode_tanah'       => trim((string) $kodeTanah) !== '' ? trim((string) $kodeTanah) : null,
                    'asal'             => trim((string) $asal) !== '' ? trim((string) $asal) : null,
                    'harga'            => is_numeric($harga) ? (float) $harga : null,
                    'keterangan'       => trim((string) $keterangan) !== '' ? trim((string) $keterangan) : null,
                    'visible'          => 1,
                ];

                try {
                    InventarisJalan::create($dataSimpan);
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
        redirect('inventaris_jalan');
    }

    public function index(): void
    {
        $data['tip']    = 1;
        $data['action'] = 'Daftar';
        $data['header'] = InventarisSubMenuEnum::JALAN['header'];

        view('admin.inventaris.jalan.index', $data);
    }

    public function datatables()
    {
        if ($this->input->is_ajax_request()) {
            $data = InventarisJalan::with('mutasi');

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';

                    if (can('u') && ! $row->mutasi) {
                        $aksi .= View::make('admin.layouts.components.buttons.btn', [
                            'url'        => ci_route('inventaris_jalan_mutasi.form/') . $row->id . '/tambah',
                            'judul'      => 'Mutasi Data',
                            'icon'       => 'fa fa-external-link-square',
                            'type'       => 'bg-olive',
                            'buttonOnly' => true,
                        ])->render();
                    }

                    $aksi .= View::make('admin.layouts.components.buttons.lihat', [
                        'url'   => ci_route('inventaris_jalan.form') . '/' . $row->id . '/' . 1,
                        'judul' => 'Lihat Data',
                    ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.edit', [
                        'url' => "inventaris_jalan/form/{$row->id}",
                    ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.hapus', [
                        'url'           => ci_route('inventaris_jalan.delete', $row->id),
                        'confirmDelete' => true,
                    ])->render();

                    return $aksi;
                })
                ->editColumn('kode_barang_register', static fn ($row): string => $row->kode_barang . '<br>' . $row->register)
                ->editColumn('tanggal_dokument', static fn ($row): string => date('d M Y', strtotime($row->tanggal_dokument)))
                ->editColumn('harga', static fn ($row): string => number_format($row->harga, 0, '.', '.'))
                ->rawColumns(['aksi', 'kode_barang_register'])
                ->make();
        }

        return show_404();
    }

    public function form($id = '', $view = false): void
    {
        isCan('u');

        if ($id) {
            $data['action']      = $view ? 'Rincian' : 'Ubah';
            $data['form_action'] = ci_route('inventaris_jalan.update', $id);
            $data['main']        = InventarisJalan::findOrFail($id);
            $data['view_mark']   = $view ? 1 : 0;
        } else {
            $data['action']      = 'Tambah';
            $data['form_action'] = ci_route('inventaris_jalan.create');
            $data['main']        = null;
            $data['view_mark']   = null;
        }

        $data['tip']      = 1;
        $data['aset']     = Aset::golongan(5)->get()->toArray();
        $data['get_kode'] = $this->header['desa'];
        $count_reg        = InventarisJalan::reg();

        $reg            = $count_reg + 1;
        $data['hasil']  = sprintf('%06s', $reg);
        $data['header'] = InventarisSubMenuEnum::JALAN['header'];

        view('admin.inventaris.jalan.form', $data);
    }

    public function create(): void
    {
        isCan('u');

        if (InventarisJalan::create($this->validate($this->request))) {
            redirect_with('success', 'Berhasil Tambah Data');
        }
        redirect_with('error', 'Gagal Tambah Data');

    }

    public function update($id): void
    {
        isCan('u');
        if (InventarisJalan::find($id)->update($this->validate($this->request))) {
            redirect_with('success', 'Berhasil Ubah Data');
        }

        redirect_with('error', 'Gagal Ubah Data');
    }

    public function delete($id): void
    {
        isCan('h');

        // cek jika inventaris sudah di mutasi
        if (InventarisJalan::with('mutasi')->find($id)->mutasi) {
            // Set kolom id_inventaris_jalan menjadi null untuk baris terkait di tabel mutasi_inventaris_jalan
            MutasiInventarisJalan::where('id_inventaris_jalan', $id)->update(['id_inventaris_jalan' => null]);
        }
        if (InventarisJalan::with('mutasi')->find($id)->delete()) {
            redirect_with('success', 'Berhasil Hapus Data');
        }
        redirect_with('error', 'Gagal Hapus Data');
    }

    public function validate($data)
    {
        return [
            'nama_barang'      => explode('_', $this->input->post('nama_barang'))[0],
            'kode_barang'      => $this->input->post('kode_barang'),
            'register'         => $this->input->post('register'),
            'kondisi'          => $this->input->post('kondisi'),
            'kontruksi'        => $this->input->post('kontruksi'),
            'panjang'          => $this->input->post('panjang'),
            'lebar'            => $this->input->post('lebar'),
            'luas'             => $this->input->post('luas'),
            'letak'            => $this->input->post('alamat'),
            'no_dokument'      => $this->input->post('no_bangunan'),
            'tanggal_dokument' => date('Y-m-d', strtotime((string) $this->input->post('tanggal_bangunan'))),
            'status_tanah'     => $this->input->post('status_tanah'),
            'kode_tanah'       => $this->input->post('kode_tanah'),
            'asal'             => $this->input->post('asal'),
            'harga'            => $this->input->post('harga'),
            'keterangan'       => $this->input->post('keterangan'),
            'visible'          => 1,
        ];
    }

    public function dialog($aksi = 'cetak')
    {
        $data               = $this->modal_penandatangan();
        $data['aksi']       = $aksi;
        $data['formAction'] = ci_route('inventaris_jalan.cetak', $aksi);

        return view('admin.inventaris.dialog_cetak', $data);
    }

    public function cetak($aksi = '')
    {
        $data          = $this->modal_penandatangan();
        $data['aksi']  = $aksi;
        $data['tahun'] = $this->input->post('tahun');

        $data['letak_ttd'] = ['1', '2', '12'];
        $data['file']      = 'Jalan_Irigasi_Jaringan_';

        $data['total'] = (int) (InventarisJalan::aktif()->cetak($data['tahun'])->get()->sum('harga'));
        $data['print'] = InventarisJalan::aktif()->cetak($data['tahun'])->get();

        return view('admin.inventaris.jalan.cetak', $data);
    }
}
