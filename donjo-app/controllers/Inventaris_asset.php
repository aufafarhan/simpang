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
use App\Models\InventarisAsset;
use App\Models\MutasiInventarisAsset;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\View;

defined('BASEPATH') || exit('No direct script access allowed');

class Inventaris_asset extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini     = 'sekretariat';
    public $sub_modul_ini = 'inventaris';
    public $akses_modul   = 'inventaris-asset';

    // Kolom kategori-spesifik (judul_buku, jenis_hewan, dst.) mengikuti field pada validate() —
    // umumnya hanya sebagian yang terisi per baris tergantung "jenis" barangnya, sisanya boleh kosong.
    private array $kolomImpor = [
        'nama_barang', 'kode_barang', 'register', 'jenis', 'judul_buku', 'spesifikasi_buku',
        'asal_daerah', 'pencipta', 'bahan', 'jenis_hewan', 'ukuran_hewan', 'jenis_tumbuhan',
        'ukuran_tumbuhan', 'jumlah', 'tahun_pengadaan', 'asal', 'harga', 'keterangan',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-inventaris-asset.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'inventaris_asset');
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
                        redirect_with('error', $error, 'inventaris_asset');
                    }

                    continue;
                }

                [$namaBarang, $kodeBarang, $register, $jenis, $judulBuku, $spesifikasiBuku, $asalDaerah, $pencipta, $bahan, $jenisHewan, $ukuranHewan, $jenisTumbuhan, $ukuranTumbuhan, $jumlah, $tahunPengadaan, $asal, $harga, $keterangan] = array_pad($sel, 18, null);
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
                    if (isset($sudahDiproses[$kunci]) || InventarisAsset::where('kode_barang', $kodeBarang)->where('register', $register)->exists()) {
                        $ganda++;
                        $pesan .= "{$barisKe}) Kode barang '{$kodeBarang}' dengan register '{$register}' sudah ada.<br>";

                        continue;
                    }
                    $sudahDiproses[$kunci] = $barisKe;
                }

                $dataSimpan = [
                    'nama_barang'      => $namaBarang,
                    'kode_barang'      => $kodeBarang !== '' ? $kodeBarang : null,
                    'register'         => $register !== '' ? $register : null,
                    'jenis'            => trim((string) $jenis) !== '' ? trim((string) $jenis) : null,
                    'judul_buku'       => trim((string) $judulBuku) !== '' ? trim((string) $judulBuku) : null,
                    'spesifikasi_buku' => trim((string) $spesifikasiBuku) !== '' ? trim((string) $spesifikasiBuku) : null,
                    'asal_daerah'      => trim((string) $asalDaerah) !== '' ? trim((string) $asalDaerah) : null,
                    'pencipta'         => trim((string) $pencipta) !== '' ? trim((string) $pencipta) : null,
                    'bahan'            => trim((string) $bahan) !== '' ? trim((string) $bahan) : null,
                    'jenis_hewan'      => trim((string) $jenisHewan) !== '' ? trim((string) $jenisHewan) : null,
                    'ukuran_hewan'     => trim((string) $ukuranHewan) !== '' ? trim((string) $ukuranHewan) : null,
                    'jenis_tumbuhan'   => trim((string) $jenisTumbuhan) !== '' ? trim((string) $jenisTumbuhan) : null,
                    'ukuran_tumbuhan'  => trim((string) $ukuranTumbuhan) !== '' ? trim((string) $ukuranTumbuhan) : null,
                    'jumlah'           => is_numeric($jumlah) ? (int) $jumlah : null,
                    'tahun_pengadaan'  => trim((string) $tahunPengadaan) !== '' ? trim((string) $tahunPengadaan) : null,
                    'asal'             => trim((string) $asal) !== '' ? trim((string) $asal) : null,
                    'harga'            => is_numeric($harga) ? (float) $harga : null,
                    'keterangan'       => trim((string) $keterangan) !== '' ? trim((string) $keterangan) : null,
                    'visible'          => 1,
                ];

                try {
                    InventarisAsset::create($dataSimpan);
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
        redirect('inventaris_asset');
    }

    public function index(): void
    {
        $data['tip']    = 1;
        $data['action'] = 'Daftar';
        $data['header'] = InventarisSubMenuEnum::ASET['header'];

        view('admin.inventaris.asset.index', $data);
    }

    public function datatables()
    {
        if ($this->input->is_ajax_request()) {
            $data = InventarisAsset::query()->with('mutasi');

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';

                    if (can('u') && ! $row->mutasi) {
                        $aksi .= View::make('admin.layouts.components.buttons.btn', [
                            'url'        => ci_route('inventaris_asset_mutasi.form/') . $row->id,
                            'judul'      => 'Mutasi Data',
                            'icon'       => 'fa fa-external-link-square',
                            'type'       => 'bg-olive',
                            'buttonOnly' => true,
                        ])->render();
                    }

                    $aksi .= View::make('admin.layouts.components.buttons.lihat', [
                        'url'   => site_url('inventaris_asset/form/' . $row->id . '/1'),
                        'judul' => 'Lihat Data',
                    ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.edit', [
                        'url' => "inventaris_asset/form/{$row->id}",
                    ])->render();

                    $aksi .= View::make('admin.layouts.components.buttons.hapus', [
                        'url'           => site_url('inventaris_asset/delete/' . $row->id),
                        'confirmDelete' => true,
                    ])->render();

                    return $aksi;
                })
                ->editColumn('kode_barang_register', static fn ($row): string => $row->kode_barang . '<br>' . $row->register)
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
            $data['form_action'] = ci_route('inventaris_asset.update', $id);
            $data['main']        = InventarisAsset::findOrFail($id);
            $data['view_mark']   = $view ? 1 : 0;
        } else {
            $data['action']      = 'Tambah';
            $data['form_action'] = ci_route('inventaris_asset.create');
            $data['main']        = null;
            $data['view_mark']   = null;
        }

        $data['tip']      = 1;
        $data['aset']     = Aset::golongan(6)->get()->toArray();
        $data['get_kode'] = $this->header['desa'];
        $count_reg        = InventarisAsset::reg();

        $reg            = $count_reg + 1;
        $data['hasil']  = sprintf('%06s', $reg);
        $data['kd_reg'] = InventarisAsset::ListKdRegister();
        $data['header'] = InventarisSubMenuEnum::ASET['header'];

        view('admin.inventaris.asset.form', $data);
    }

    public function create(): void
    {
        isCan('u');

        if (InventarisAsset::create($this->validate($this->request))) {
            redirect_with('success', 'Berhasil Tambah Data');
        }
        redirect_with('error', 'Gagal Tambah Data');

    }

    public function update($id): void
    {
        isCan('u');
        if (InventarisAsset::find($id)->update($this->validate($this->request))) {
            redirect_with('success', 'Berhasil Ubah Data');
        }

        redirect_with('error', 'Gagal Ubah Data');
    }

    public function delete($id): void
    {
        isCan('h');

        // cek jika inventaris sudah di mutasi
        if (InventarisAsset::with('mutasi')->find($id)->mutasi) {
            // Set kolom id_inventaris_jalan menjadi null untuk baris terkait di tabel mutasi_inventaris_jalan
            MutasiInventarisAsset::where('id_inventaris_jalan', $id)->update(['id_inventaris_jalan' => null]);
        }
        if (InventarisAsset::with('mutasi')->find($id)->delete()) {
            redirect_with('success', 'Berhasil Hapus Data');
        }
        redirect_with('error', 'Gagal Hapus Data');
    }

    public function validate($data)
    {
        $nama_barang = explode('_', $this->input->post('nama_barang'))[0];

        return [
            // nama barang perlu diambil nama nya saja tanpa kode barang etc
            // next : cek bagian edit dan detail, setelah itu cek bagian cetak
            'nama_barang'      => $nama_barang,
            'kode_barang'      => $this->input->post('kode_barang'),
            'register'         => $this->input->post('register'),
            'jenis'            => $this->input->post('jenis_asset'),
            'judul_buku'       => $this->input->post('judul'),
            'spesifikasi_buku' => $this->input->post('spesifikasi'),
            'asal_daerah'      => $this->input->post('asal_kesenian'),
            'pencipta'         => $this->input->post('pencipta_kesenian'),
            'bahan'            => $this->input->post('bahan_kesenian'),
            'jenis_hewan'      => $this->input->post('jenis_hewan'),
            'ukuran_hewan'     => $this->input->post('ukuran_hewan'),
            'jenis_tumbuhan'   => $this->input->post('jenis_tumbuhan'),
            'ukuran_tumbuhan'  => $this->input->post('ukuran_tumbuhan'),
            'jumlah'           => $this->input->post('jumlah'),
            'tahun_pengadaan'  => $this->input->post('tahun_pengadaan'),
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
        $data['formAction'] = ci_route('inventaris_asset.cetak', $aksi);

        return view('admin.inventaris.dialog_cetak', $data);
    }

    public function cetak($aksi = '')
    {
        $data          = $this->modal_penandatangan();
        $data['aksi']  = $aksi;
        $data['tahun'] = $this->input->post('tahun');

        $data['letak_ttd'] = ['1', '2', '12'];
        $data['file']      = 'Asset_Lainnya_';

        $data['total'] = (int) (InventarisAsset::aktif()->cetak($data['tahun'])->get()->sum('harga'));
        $data['print'] = InventarisAsset::aktif()->cetak($data['tahun'])->get();

        return view('admin.inventaris.asset.cetak', $data);
    }
}
