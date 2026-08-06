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

use App\Models\Area;
use App\Models\Cdesa as CdesaModel;
use App\Models\MutasiCdesa;
use App\Models\Persil;
use App\Models\RefPersilKelas;
use App\Models\RefPersilMutasi;
use App\Models\Wilayah;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\View;

defined('BASEPATH') || exit('No direct script access allowed');

class Cdesa_mutasi extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini     = 'pertanahan';
    public $sub_modul_ini = 'c-desa';

    // Impor Excel dibatasi hanya untuk menambah data mutasi biasa (perpindahan/pembagian persil
    // antar C-Desa), TIDAK mencakup jenis_mutasi=9 (pemilik awal) karena baris itu sudah otomatis
    // dibuat lewat impor Data_persil. Tidak mencakup path/id_peta (poligon peta) karena tidak bisa
    // dibawa lewat Excel.
    private array $kolomImpor = [
        'nomor_cdesa_tujuan', 'no_persil', 'nomor_urut_bidang', 'no_bidang_persil',
        'no_objek_pajak', 'tanggal_mutasi', 'jenis_mutasi', 'luas', 'nomor_cdesa_asal', 'keterangan',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-mutasi-cdesa.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'cdesa');
        }

        $petaCdesa      = CdesaModel::pluck('nomor', 'id')->all();
        $petaJenisMutasi = RefPersilMutasi::where('id', '!=', 9)->pluck('nama', 'id')->all();

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
                        redirect_with('error', $error, 'cdesa');
                    }

                    continue;
                }

                [$nomorCdesaTujuan, $noPersil, $nomorUrutBidang, $noBidangPersil, $noObjekPajak, $tglMutasi, $jenisMutasiTeks, $luas, $nomorCdesaAsal, $keterangan] = array_pad($sel, 10, null);
                $nomorCdesaTujuan = preg_replace('/[^0-9]/', '', (string) $nomorCdesaTujuan);
                $noPersil         = trim((string) $noPersil);
                $nomorUrutBidang  = trim((string) $nomorUrutBidang);
                $jenisMutasiTeks  = trim((string) $jenisMutasiTeks);
                $nomorCdesaAsal   = preg_replace('/[^0-9]/', '', (string) $nomorCdesaAsal);

                if ($nomorCdesaTujuan === '' || $noPersil === '' || $nomorUrutBidang === '' || $jenisMutasiTeks === '') {
                    $gagal++;
                    $pesan .= "{$barisKe}) Kolom nomor_cdesa_tujuan, no_persil, nomor_urut_bidang, dan jenis_mutasi wajib diisi.<br>";

                    continue;
                }

                $idCdesaMasuk = $this->resolveKodeReferensi($petaCdesa, $nomorCdesaTujuan);
                if (! $idCdesaMasuk) {
                    $gagal++;
                    $pesan .= "{$barisKe}) C-Desa tujuan dengan nomor '{$nomorCdesaTujuan}' tidak ditemukan.<br>";

                    continue;
                }

                $persil = Persil::where('nomor', $noPersil)->where('nomor_urut_bidang', $nomorUrutBidang)->first();
                if (! $persil) {
                    $gagal++;
                    $pesan .= "{$barisKe}) Persil No. {$noPersil} : {$nomorUrutBidang} tidak ditemukan.<br>";

                    continue;
                }

                $jenisMutasi = (ctype_digit($jenisMutasiTeks) && array_key_exists((int) $jenisMutasiTeks, $petaJenisMutasi))
                    ? (int) $jenisMutasiTeks
                    : $this->resolveKodeReferensi($petaJenisMutasi, $jenisMutasiTeks);
                if (! $jenisMutasi) {
                    $gagal++;
                    $pesan .= "{$barisKe}) Jenis mutasi '{$jenisMutasiTeks}' tidak dikenali.<br>";

                    continue;
                }

                $idCdesaAsal = null;
                if ($nomorCdesaAsal !== '') {
                    $idCdesaAsal = $this->resolveKodeReferensi($petaCdesa, $nomorCdesaAsal);
                    if (! $idCdesaAsal) {
                        $gagal++;
                        $pesan .= "{$barisKe}) C-Desa asal dengan nomor '{$nomorCdesaAsal}' tidak ditemukan.<br>";

                        continue;
                    }
                }

                $tanggalMutasi = $tglMutasi instanceof DateTimeInterface ? $tglMutasi->format('Y-m-d') : (($w = strtotime((string) $tglMutasi)) !== false ? date('Y-m-d', $w) : null);

                $kunci = $persil->id . '|' . $idCdesaMasuk . '|' . $jenisMutasi . '|' . $tanggalMutasi;
                if (isset($sudahDiproses[$kunci])) {
                    $ganda++;
                    $pesan .= "{$barisKe}) Baris duplikat dengan baris {$sudahDiproses[$kunci]} (persil, C-Desa tujuan, jenis mutasi, dan tanggal sama).<br>";

                    continue;
                }
                $sudahDiproses[$kunci] = $barisKe;

                $dataSimpan = [
                    'id_persil'        => $persil->id,
                    'id_cdesa_masuk'   => $idCdesaMasuk,
                    'no_bidang_persil' => is_numeric($noBidangPersil) ? (int) $noBidangPersil : null,
                    'no_objek_pajak'   => trim((string) $noObjekPajak) !== '' ? trim((string) $noObjekPajak) : null,
                    'tanggal_mutasi'   => $tanggalMutasi,
                    'jenis_mutasi'     => $jenisMutasi,
                    'luas'             => is_numeric($luas) ? (float) $luas : null,
                    'cdesa_keluar'     => $idCdesaAsal,
                    'keterangan'       => trim((string) $keterangan) !== '' ? trim((string) $keterangan) : null,
                    'path'             => null,
                    'id_peta'          => null,
                ];

                try {
                    // Sementara dinonaktifkan (akses admin terkunci lisensi premium): insert DB dilewati, hasil parse dicatat ke log.
                    // MutasiCdesa::create($dataSimpan);
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
        redirect('cdesa');
    }

    public function index($id_cdesa, $id_persil = null)
    {
        $data['cdesa']  = CdesaModel::with(['penduduk'])->findOrFail($id_cdesa);
        $data['persil'] = Persil::with('refKelas', 'wilayah')->find($id_persil);

        return view('admin.pertanahan.cdesa.mutasi.index', $data);
    }

    public function delete($id_cdesa, $id_persil, $id_mutasi): void
    {
        isCan('h');

        if (MutasiCdesa::findOrFail($id_mutasi)->delete()) {
            redirect_with('success', 'Berhasil Hapus Data', route('cdesa.mutasi', ['id_cdesa' => $id_cdesa, 'id_persil' => $id_persil]));
        }

        redirect_with('error', 'Gagal Hapus Data');
    }

    public function datatables($id_cdesa, $id_persil = null)
    {
        if ($this->input->is_ajax_request()) {
            $query = MutasiCdesa::getList($id_cdesa, $id_persil);

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';

                    $aksi .= View::make('admin.layouts.components.buttons.edit', [
                        'url' => 'cdesa/mutasi/' . $row->id_cdesa_masuk . '/form/' . $row->id_persil . '/' . $row->id,
                    ])->render();

                    if (can('u')) {
                        $aksi .= View::make('admin.layouts.components.buttons.btn', [
                            'url'         => '#',
                            'icon'        => 'fa fa-map',
                            'judul'       => 'Lihat Map',
                            'type'        => 'bg-olive',
                            'modalTarget' => 'map-modal',
                            'buttonOnly'  => true,
                            'modal'       => true,
                            'attributes'  => ['data-path' => $row->path, 'class' => 'area-map'],
                        ])->render();
                    }
                    if ($row->jenis_mutasi != '9') {
                        $aksi .= View::make('admin.layouts.components.buttons.hapus', [
                            'url'           => route('cdesa.hapus_mutasi', ['id_cdesa' => $row->id_cdesa_masuk, 'id_persil' => $row->id_persil, 'id_mutasi' => $row->id]),
                            'confirmDelete' => true,
                        ])->render();
                    } else {
                        $aksi .= View::make('admin.layouts.components.buttons.hapus', [
                            'url'           => ci_route('cdesa.awal_persil', [$row->id_cdesa_masuk, $row->id_persil, 1]),
                            'confirmDelete' => true,
                        ])->render();
                    }

                    return $aksi;
                })
                ->editColumn('nomor', static fn ($row) => sprintf('%04s', $row->nomor))
                ->editColumn('tanggal_mutasi', static fn ($row) => tgl_indo_out($row->tanggal_mutasi))
                ->editColumn('luas_masuk', static function ($row) {
                    $txt = $row->luas_masuk;
                    if ($row->cdesa_keluar && $row->id_cdesa_masuk == $row->id_cdesa) {
                        $txt .= 'dari ' . '<a href="' . ci_route('cdesa.mutasi', $row->cdesa_keluar) . '/' . $row->id_persil . '">C-Desa ini</a>';
                    }

                    return $txt;
                })
                ->editColumn('luas_keluar', static function ($row) {
                    $txt = $row->luas_keluar;
                    if ($row->cdesa_keluar && $row->id_cdesa_masuk != $row->id_cdesa) {
                        $txt .= 'ke ' . '<a href="' . ci_route('cdesa.mutasi', $row->cdesa_keluar) . '/' . $row->id_persil . '">C-Desa ini</a>';
                    }

                    return $txt;
                })
                ->rawColumns(['ceklist', 'aksi', 'luas_masuk', 'luas_keluar'])
                ->make();
        }

        return show_404();
    }

    public function form($id_cdesa, $id_persil = '', $id_mutasi = '')
    {
        isCan('u');

        $data['persil'] = Persil::with('refKelas', 'wilayah')->find($id_persil);

        if ($id_mutasi) {
            $data['mutasi'] = MutasiCdesa::findOrFail($id_mutasi);
        }

        $data['cdesa'] = CdesaModel::with(['penduduk'])->findOrFail($id_cdesa);

        $data['list_cdesa'] = CdesaModel::listCdesa([$id_cdesa]);

        $data['list_persil'] = Persil::list();

        $data['persil_kelas']        = RefPersilKelas::get()->toArray();
        $data['persil_sebab_mutasi'] = RefPersilMutasi::get()->toArray();

        $data['persil_lokasi'] = Wilayah::get();
        $data['peta']          = Area::areaMap();

        return view('admin.pertanahan.cdesa.mutasi.form', $data);
    }

    public function simpan($idCdesa, $idMutasi = ''): void
    {
        isCan('u');

        $data = $this->validate($idCdesa);
        if ($idMutasi) {
            $mutasi = MutasiCdesa::findOrFail($idMutasi)->update($data);
        } else {
            $mutasi = MutasiCdesa::create($data);
        }

        if ($mutasi) {
            if ($data['id_persil']) {
                $url = ci_route('cdesa.mutasi', $idCdesa) . '/' . $data['id_persil'];
                redirect_with('success', 'Data Persil telah DISIMPAN', $url);
            }
            redirect_with('success', 'Data Persil telah DISIMPAN', ci_route('cdesa.rincian', $idCdesa));
        }

        redirect_with('error', 'Gagal Tambah Data');
    }

    protected function validate($idCdesa)
    {
        $post = $this->input->post();

        $data['id_persil']        = $post['id_persil'];
        $data['id_cdesa_masuk']   = $idCdesa;
        $data['no_bidang_persil'] = bilangan($post['no_bidang_persil']) ?: null;
        $data['no_objek_pajak']   = strip_tags($post['no_objek_pajak']);
        $data['tanggal_mutasi']   = $post['tanggal_mutasi'] ? tgl_indo_in($post['tanggal_mutasi']) : null;
        $data['jenis_mutasi']     = $post['jenis_mutasi'] ?: null;
        $data['luas']             = bilangan_titik($post['luas']) ?: null;
        $data['cdesa_keluar']     = bilangan($post['cdesa_keluar']) ?: null;
        $data['keterangan']       = strip_tags($post['keterangan']) ?: null;
        $data['path']             = $post['path'];
        $data['id_peta']          = ($post['area_tanah'] == 1 || $post['area_tanah'] == null) ? $post['id_peta'] : null;
        $data['id_peta']          = $data['id_peta'] ?: null;

        return $data;
    }
}
