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

use App\Models\Pemilihan as PemilihanModel;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

defined('BASEPATH') || exit('No direct script access allowed');

class Pemilihan extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini     = 'kependudukan';
    public $sub_modul_ini = 'calon-pemilih';
    public $akses_modul   = 'calon-pemilih';

    private array $kolomImpor = ['judul', 'tanggal', 'status', 'keterangan'];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
        if (! Schema::hasTable('pemilihan')) {
            session_error('Tabel Pemilihan tidak ditemukan, silakan lakukan migrasi database terlebih dahulu.');
            redirect('dpt');
        }
    }

    protected static function Validate($request = [])
    {
        return [
            'judul'      => nama_terbatas($request['judul']),
            'tanggal'    => date('Y-m-d', strtotime((string) $request['tanggal'])),
            'keterangan' => $request['keterangan'],
            'status'     => $request['status'] ?? 0,
        ];
    }

    public function index()
    {
        return view('admin.pemilihan.index');
    }

    public function format_impor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-pemilihan.xlsx');
    }

    public function proses_impor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'pemilihan');
        }

        $sukses = 0;
        $gagal  = 0;
        $ganda  = 0;
        $pesan  = '';
        $barisKe = 0;
        $sudahDiproses = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $barisKe++;
                $sel = $this->nilaiBaris($row);

                if ($barisKe === 1) {
                    if ($error = $this->validasiHeaderExcel($sel, $this->kolomImpor)) {
                        $reader->close();
                        redirect_with('error', $error, 'pemilihan');
                    }
                    continue;
                }

                [$judul, $tanggal, $status, $keterangan] = array_pad($sel, 4, null);
                $judul = trim((string) $judul);

                if ($judul === '' || empty($tanggal)) {
                    $gagal++;
                    $pesan .= "{$barisKe}) Kolom judul dan tanggal wajib diisi.<br>";

                    continue;
                }

                $waktu = strtotime((string) $tanggal);
                if ($waktu === false) {
                    $gagal++;
                    $pesan .= "{$barisKe}) Format tanggal '{$tanggal}' tidak dikenali.<br>";

                    continue;
                }

                $kunci = strtolower($judul) . '|' . date('Y-m-d', $waktu);
                if (isset($sudahDiproses[$kunci])) {
                    $ganda++;
                    $pesan .= "{$barisKe}) '{$judul}' tanggal " . date('d-m-Y', $waktu) . " sama dengan baris {$sudahDiproses[$kunci]}.<br>";

                    continue;
                }
                $sudahDiproses[$kunci] = $barisKe;

                try {
                    PemilihanModel::create(static::validate([
                        'judul'      => $judul,
                        'tanggal'    => date('Y-m-d', $waktu),
                        'status'     => in_array(strtolower(trim((string) $status)), ['1', 'ya', 'aktif', 'true'], true) ? 1 : 0,
                        'keterangan' => $keterangan,
                    ]));
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
        redirect('pemilihan');
    }

    public function datatables()
    {
        if ($this->input->is_ajax_request()) {
            return datatables()->of(PemilihanModel::query())
                ->addColumn('ceklist', static function ($row) {
                    if (can('h')) {
                        return '<input type="checkbox" name="id_cb[]" value="' . $row->uuid . '"/>';
                    }
                })
                ->addIndexColumn()
                ->addColumn(
                    'aksi',
                    static fn ($row) => View::make('admin.layouts.components.buttons.edit', [
                        'url' => "pemilihan/form/{$row->uuid}",
                    ])->render() .

                    View::make('admin.layouts.components.tombol_aktifkan', [
                        'url'    => site_url("pemilihan/status/{$row->uuid}"),
                        'active' => $row->status,
                    ])->render() .

                    View::make('admin.layouts.components.buttons.hapus', [
                        'url'           => ci_route('pemilihan.delete', $row->uuid),
                        'confirmDelete' => true,
                    ])->render()
                )

                ->editColumn('tanggal', static fn ($row) => tgl_indo2($row->tanggal))
                ->rawColumns(['ceklist', 'aksi'])
                ->make();
        }

        return show_404();
    }

    public function form($id = '')
    {
        isCan('u');

        if ($id) {
            $action      = 'Ubah';
            $form_action = ci_route('pemilihan.update', $id);
            $pemilihan   = PemilihanModel::findOrFail($id);
        } else {
            $action      = 'Tambah';
            $form_action = ci_route('pemilihan.insert');
            $pemilihan   = null;
        }

        return view('admin.pemilihan.form', ['action' => $action, 'form_action' => $form_action, 'pemilihan' => $pemilihan]);
    }

    public function insert(): void
    {
        isCan('u');

        if (PemilihanModel::create(static::validate($this->request))) {
            redirect_with('success', 'Berhasil Tambah Data', 'pemilihan/');
        }
        redirect_with('error', 'Gagal Tambah Data', 'pemilihan/');
    }

    public function update($id = ''): void
    {
        isCan('u');

        $data = PemilihanModel::findOrFail($id);

        if ($data->update(static::validate($this->request))) {
            redirect_with('success', 'Berhasil Ubah Data', 'pemilihan/');
        }
        redirect_with('error', 'Gagal Ubah Data', 'pemilihan/');
    }

    public function status($id = null): void
    {
        isCan('u');

        if (PemilihanModel::gantiStatus($id)) {
            redirect_with('success', 'Berhasil Ubah Data', 'pemilihan/');
        }
        redirect_with('error', 'Gagal Ubah Data', 'pemilihan/');
    }

    public function delete($id): void
    {
        isCan('h');

        $data = PemilihanModel::findOrFail($id);

        if ($data->destroy($id)) {
            redirect_with('success', 'Berhasil Hapus Data', 'pemilihan/');
        }

        redirect_with('error', 'Gagal Hapus Data', 'pemilihan/');
    }

    public function delete_all(): void
    {
        isCan('h');

        if (PemilihanModel::destroy($this->request['id_cb'])) {
            redirect_with('success', 'Berhasil Hapus Data', 'pemilihan/');
        }

        redirect_with('error', 'Gagal Hapus Data', 'pemilihan/');
    }
}
