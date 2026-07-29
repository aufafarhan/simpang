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

defined('BASEPATH') || exit('No direct script access allowed');

use App\Enums\Dtks\DtksEnum;
use App\Enums\Dtks\Regsosek2022kEnum;
use App\Enums\StatusEnum;
use App\Models\Config;
use App\Models\Dtks as ModelDtks;
use App\Models\DtksAnggota;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rtm;
use App\Models\Wilayah;
use App\Services\DTKSRegsosEk2022k;
use App\Traits\ImporExcel;
use Illuminate\Support\Facades\DB;

// TODO : jika ada perubahan versi DTKS terbaru, selain merubah data yg ada
// silahkan buat kode untuk menghapus file pdf versi DTKS sebelumnya.
// cek kode DTKSRegsosEk2022k::generateCetakPdf()

class Dtks extends Admin_Controller
{
    use ImporExcel;

    public $modul_ini     = 'satu-data';
    public $sub_modul_ini = 'dtks';

    // Impor Excel mencakup Bagian I-III (Keterangan Tempat, Petugas, Perumahan) dan Bagian V-VI
    // (Bantuan Sosial & Aset Rumah Tangga, Catatan) — semuanya level-rumah-tangga (field nempel
    // langsung ke $dtks, satu baris Excel = satu rumah tangga). Bagian IV SENGAJA TIDAK dicakup
    // karena levelnya per-anggota keluarga (field nempel ke DtksAnggota, baris anggota dibuat
    // otomatis dari data Penduduk, dan banyak field-nya auto-terisi dari data Penduduk yang sudah
    // ada) — beda struktur & jauh lebih berisiko menimpa data yang sudah benar. Bagian VII (upload
    // foto) juga tidak dicakup karena berkas fisik tidak bisa dibawa lewat Excel.
    private array $kolomImpor = [
        'no_kk',
        // Bagian I - Keterangan Tempat
        'kode_sls_non_sls', 'kode_sub_sls', 'nama_sls_non_sls', 'no_urut_bangunan_tinggal',
        'no_urut_keluarga_verif', 'status_keluarga', 'kode_landmark_wilkerstat', 'kd_kk',
        // Bagian II - Keterangan Petugas
        'tanggal_pendataan', 'nama_ppl', 'kode_ppl', 'tanggal_pemeriksaan', 'nama_pml', 'kode_pml',
        'nama_responden', 'no_hp_responden', 'kd_hasil_pendataan_keluarga',
        // Bagian III - Keterangan Perumahan
        'kd_stat_bangunan_tinggal', 'kd_sertiv_lahan_milik', 'luas_lantai', 'kd_jenis_lantai_terluas',
        'kd_jenis_dinding', 'kd_jenis_atap', 'kd_sumber_air_minum', 'kd_jarak_sumber_air_ke_tpl',
        'kd_sumber_penerangan_utama', 'kd_daya_terpasang', 'kd_daya_terpasang2', 'kd_daya_terpasang3',
        'kd_bahan_bakar_memasak', 'kd_fasilitas_tempat_bab', 'kd_jenis_kloset', 'kd_pembuangan_akhir_tinja',
        // Bagian V - Bantuan Sosial (501a-g: dapat/bulan/tahun)
        '501a_dapat', '501a_bulan', '501a_tahun',
        '501b_dapat', '501b_bulan', '501b_tahun',
        '501c_dapat', '501c_bulan', '501c_tahun',
        '501d_dapat', '501d_bulan', '501d_tahun',
        '501e_dapat', '501e_bulan', '501e_tahun',
        '501f_dapat', '501f_bulan', '501f_tahun',
        '501g_dapat', '501g_bulan', '501g_tahun',
        // Bagian V - Kepemilikan Aset
        '502a', '502b', '502c', '502d', '502e', '502f', '502g', '502h', '502i', '502k', '502l', '502m', '502n',
        // Bagian V - Kepemilikan Ternak
        '504a', '504b', '504c', '504d', '504e',
        // Bagian V - Lahan, Internet, Rekening
        '503a', '503b', '505', '506',
        // Bagian VI - Catatan
        'catatan',
    ];

    // Peta kode kuisioner (501x/502x/504x) ke nama kolom pada model Dtks,
    // mereplikasi persis pemetaan pada DTKSRegsosEk2022k::saveBagian5().
    private const BAGIAN5_BANSOS = [
        '501a' => 'bss_bnpt',
        '501b' => 'pkh',
        '501c' => 'blt_dana_desa',
        '501d' => 'subsidi_listrik',
        '501e' => 'bantuan_pemda',
        '501f' => 'subsidi_pupuk',
        '501g' => 'subsidi_lpg',
    ];

    // Catatan: 502d sengaja diisikan ke DUA kolom (kd_pemanas_air & kd_telepon_rumah) dan 502j
    // tidak dipetakan ke kolom manapun — ini meniru persis (termasuk kejanggalannya) logic asli
    // DTKSRegsosEk2022k::saveBagian5(), bukan kesalahan penulisan di sini.
    private const BAGIAN5_ASET = [
        '502a' => ['tabung_gas_5_5_kg'],
        '502b' => ['lemari_es'],
        '502c' => ['ac'],
        '502d' => ['pemanas_air', 'telepon_rumah'],
        '502e' => ['televisi'],
        '502f' => ['perhiasan_10_gr_emas'],
        '502g' => ['komputer_laptop'],
        '502h' => ['sepeda_motor'],
        '502i' => ['sepeda'],
        '502k' => ['mobil'],
        '502l' => ['perahu'],
        '502m' => ['kapal_perahu_motor'],
        '502n' => ['smartphone'],
    ];

    private const BAGIAN5_TERNAK = [
        '504a' => 'sapi',
        '504b' => 'kerbau',
        '504c' => 'kuda',
        '504d' => 'babi',
        '504e' => 'kambing_domba',
    ];

    public function __construct()
    {
        parent::__construct();
        isCan('b');
    }

    public function formatImpor(): void
    {
        isCan('u');
        $this->unduhTemplateImpor($this->kolomImpor, 'format-impor-dtks-bagian-1-3-5-6.xlsx');
    }

    public function prosesImpor(): void
    {
        isCan('u');

        try {
            $reader = $this->bukaReaderExcel();
        } catch (Exception $e) {
            redirect_with('error', $e->getMessage(), 'dtks');
        }

        $sukses   = 0;
        $gagal    = 0;
        $ganda    = 0;
        $pesan    = '';
        $barisKe  = 0;
        $diproses = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $barisKe++;
                $sel = $this->nilaiBaris($row);

                if ($barisKe === 1) {
                    if ($error = $this->validasiHeaderExcel($sel, $this->kolomImpor)) {
                        $reader->close();
                        redirect_with('error', $error, 'dtks');
                    }

                    continue;
                }

                $sel  = array_pad($sel, count($this->kolomImpor), null);
                $data = array_map(static function ($v) {
                    if ($v instanceof DateTimeInterface) {
                        return $v->format('Y-m-d');
                    }

                    return is_string($v) ? trim($v) : $v;
                }, array_combine($this->kolomImpor, $sel));

                $noKk = preg_replace('/[^0-9]/', '', (string) $data['no_kk']);
                if ($noKk === '') {
                    $gagal++;
                    $pesan .= "{$barisKe}) Kolom no_kk wajib diisi.<br>";

                    continue;
                }

                $rtm = Rtm::where('no_kk', $noKk)->first();
                if (! $rtm) {
                    $gagal++;
                    $pesan .= "{$barisKe}) RTM dengan No KK '{$noKk}' tidak ditemukan. Pastikan KK tersebut sudah terdaftar sebagai RTM terlebih dahulu.<br>";

                    continue;
                }

                if (isset($diproses[$rtm->id])) {
                    $ganda++;
                    $pesan .= "{$barisKe}) No KK '{$noKk}' sudah diproses pada baris {$diproses[$rtm->id]}.<br>";

                    continue;
                }
                $diproses[$rtm->id] = $barisKe;

                $errorValidasi = $this->validasiKodeDtks($data) . ' ' . $this->validasiKodeDtks56($data);
                $errorValidasi = trim($errorValidasi);
                if ($errorValidasi !== '') {
                    $gagal++;
                    $pesan .= "{$barisKe}) {$errorValidasi}<br>";

                    continue;
                }

                try {
                    // Sementara dinonaktifkan (akses admin terkunci lisensi premium): insert/update DB dilewati, hasil parse dicatat ke log.
                    // DB::beginTransaction();
                    // $dtks = ModelDtks::where(['id_rtm' => $rtm->id, 'versi_kuisioner' => DtksEnum::VERSION_CODE])->first();
                    // if (! $dtks) {
                    //     $dtks = ModelDtks::create([
                    //         'versi_kuisioner' => DtksEnum::VERSION_CODE,
                    //         'id_rtm'          => $rtm->id,
                    //         'is_draft'        => StatusEnum::YA,
                    //     ]);
                    //     (new DTKSRegsosEk2022k())->syncronizeWithOpenSid($dtks);
                    // }
                    //
                    // $this->isiBagian123($dtks, $data);
                    // $this->isiBagian56($dtks, $data);
                    // $dtks->save();
                    // DB::commit();
                    log_message('debug', json_encode(array_merge(['no_kk' => $noKk, 'id_rtm' => $rtm->id], $data)));
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
        redirect('dtks');
    }

    /**
     * Isi field Bagian I (Keterangan Tempat), II (Keterangan Petugas), III (Keterangan Perumahan)
     * pada $dtks, mereplikasi field & aturan kondisional persis seperti
     * DTKSRegsosEk2022k::saveBagian1()/saveBagian2()/saveBagian3().
     */
    private function isiBagian123(ModelDtks $dtks, array $data): void
    {
        $kosongKeNull = static fn ($v) => ($v === '' || $v === null) ? null : $v;

        // Bagian I - Keterangan Tempat
        $dtks->kode_sls_non_sls         = $kosongKeNull($data['kode_sls_non_sls']);
        $dtks->kode_sub_sls             = $kosongKeNull($data['kode_sub_sls']);
        $dtks->nama_sls_non_sls         = $kosongKeNull($data['nama_sls_non_sls']);
        $dtks->no_urut_bangunan_tinggal = $kosongKeNull($data['no_urut_bangunan_tinggal']);
        $dtks->no_urut_keluarga_verif   = $kosongKeNull($data['no_urut_keluarga_verif']);
        $dtks->status_keluarga          = $kosongKeNull($data['status_keluarga']);
        $dtks->kode_landmark_wilkerstat = $kosongKeNull($data['kode_landmark_wilkerstat']);
        $dtks->kd_kk                    = $kosongKeNull($data['kd_kk']);

        // Bagian II - Keterangan Petugas
        $dtks->tanggal_pendataan           = $kosongKeNull($data['tanggal_pendataan']);
        $dtks->nama_ppl                    = $kosongKeNull($data['nama_ppl']);
        $dtks->kode_ppl                    = $kosongKeNull($data['kode_ppl']);
        $dtks->tanggal_pemeriksaan         = $kosongKeNull($data['tanggal_pemeriksaan']);
        $dtks->nama_pml                    = $kosongKeNull($data['nama_pml']);
        $dtks->kode_pml                    = $kosongKeNull($data['kode_pml']);
        $dtks->nama_responden              = $kosongKeNull($data['nama_responden']);
        $dtks->no_hp_responden             = $kosongKeNull($data['no_hp_responden']);
        $dtks->kd_hasil_pendataan_keluarga = $kosongKeNull($data['kd_hasil_pendataan_keluarga']);

        // Bagian III - Keterangan Perumahan (dengan field kondisional seperti pada form asli)
        $dtks->kd_stat_bangunan_tinggal = $kosongKeNull($data['kd_stat_bangunan_tinggal']);
        $dtks->kd_sertiv_lahan_milik    = $dtks->kd_stat_bangunan_tinggal == '1' ? $kosongKeNull($data['kd_sertiv_lahan_milik']) : null;
        $dtks->luas_lantai              = is_numeric($data['luas_lantai']) ? (int) $data['luas_lantai'] : null;
        $dtks->kd_jenis_lantai_terluas  = $kosongKeNull($data['kd_jenis_lantai_terluas']);
        $dtks->kd_jenis_dinding         = $kosongKeNull($data['kd_jenis_dinding']);
        $dtks->kd_jenis_atap            = $kosongKeNull($data['kd_jenis_atap']);
        $dtks->kd_sumber_air_minum      = $kosongKeNull($data['kd_sumber_air_minum']);

        $dtks->kd_jarak_sumber_air_ke_tpl = in_array($dtks->kd_sumber_air_minum, ['4', '5', '6', '7', '8'], true)
            ? $kosongKeNull($data['kd_jarak_sumber_air_ke_tpl'])
            : null;

        $dtks->kd_sumber_penerangan_utama = $kosongKeNull($data['kd_sumber_penerangan_utama']);
        $dtks->kd_daya_terpasang          = $dtks->kd_sumber_penerangan_utama == '1' ? $kosongKeNull($data['kd_daya_terpasang']) : null;
        $dtks->kd_daya_terpasang2         = $dtks->kd_sumber_penerangan_utama == '1' ? $kosongKeNull($data['kd_daya_terpasang2']) : null;
        $dtks->kd_daya_terpasang3         = $dtks->kd_sumber_penerangan_utama == '1' ? $kosongKeNull($data['kd_daya_terpasang3']) : null;
        $dtks->kd_bahan_bakar_memasak     = $kosongKeNull($data['kd_bahan_bakar_memasak']);
        $dtks->kd_fasilitas_tempat_bab    = $kosongKeNull($data['kd_fasilitas_tempat_bab']);

        $dtks->kd_jenis_kloset = in_array($dtks->kd_fasilitas_tempat_bab, ['1', '2', '3'], true)
            ? $kosongKeNull($data['kd_jenis_kloset'])
            : null;

        $dtks->kd_pembuangan_akhir_tinja = $kosongKeNull($data['kd_pembuangan_akhir_tinja']);
    }

    /**
     * Validasi kode pilihan (dropdown) terhadap daftar resmi Regsosek2022kEnum,
     * mereplikasi pengecekan array_key_exists() yang dipakai saveBagian1()/2()/3().
     */
    private function validasiKodeDtks(array $data): string
    {
        $pilihan1 = Regsosek2022kEnum::pilihanBagian1();
        $pilihan2 = Regsosek2022kEnum::pilihanBagian2();
        $pilihan3 = Regsosek2022kEnum::pilihanBagian3();

        $cekKode = static function (string $kode, $nilai, array $daftarPilihan): ?string {
            $nilai = trim((string) $nilai);
            if ($nilai === '') {
                return null;
            }

            return array_key_exists($nilai, $daftarPilihan[$kode] ?? []) ? null : "Kolom {$kode}: kode '{$nilai}' tidak dikenali.";
        };

        $pesan = array_filter([
            $cekKode('115', $data['kd_kk'], $pilihan1),
            $cekKode('205', $data['kd_hasil_pendataan_keluarga'], $pilihan2),
            $cekKode('301a', $data['kd_stat_bangunan_tinggal'], $pilihan3),
            $cekKode('301b', $data['kd_sertiv_lahan_milik'], $pilihan3),
            $cekKode('303', $data['kd_jenis_lantai_terluas'], $pilihan3),
            $cekKode('304', $data['kd_jenis_dinding'], $pilihan3),
            $cekKode('305', $data['kd_jenis_atap'], $pilihan3),
            $cekKode('306a', $data['kd_sumber_air_minum'], $pilihan3),
            $cekKode('306b', $data['kd_jarak_sumber_air_ke_tpl'], $pilihan3),
            $cekKode('307a', $data['kd_sumber_penerangan_utama'], $pilihan3),
            $cekKode('307b1', $data['kd_daya_terpasang'], $pilihan3),
            $cekKode('307b2', $data['kd_daya_terpasang2'], $pilihan3),
            $cekKode('307b3', $data['kd_daya_terpasang3'], $pilihan3),
            $cekKode('308', $data['kd_bahan_bakar_memasak'], $pilihan3),
            $cekKode('309a', $data['kd_fasilitas_tempat_bab'], $pilihan3),
            $cekKode('309b', $data['kd_jenis_kloset'], $pilihan3),
            $cekKode('310', $data['kd_pembuangan_akhir_tinja'], $pilihan3),
        ]);

        return implode(' ', $pesan);
    }

    /**
     * Isi field Bagian V (Bantuan Sosial & Aset Rumah Tangga) dan VI (Catatan) pada $dtks,
     * mereplikasi field & pemetaan persis seperti DTKSRegsosEk2022k::saveBagian5()/saveBagian6().
     * Tidak ada gating kondisional di sini karena saveBagian5() asli juga tidak menggating
     * bulan/tahun terhadap nilai *_dapat.
     */
    private function isiBagian56(ModelDtks $dtks, array $data): void
    {
        $kosongKeNull = static fn ($v) => ($v === '' || $v === null) ? null : $v;

        foreach (self::BAGIAN5_BANSOS as $kode => $nama) {
            $dtks->{"kd_{$nama}"}     = $kosongKeNull($data["{$kode}_dapat"]);
            $dtks->{"bulan_{$nama}"}  = $kosongKeNull($data["{$kode}_bulan"]);
            $dtks->{"tahun_{$nama}"}  = $kosongKeNull($data["{$kode}_tahun"]);
        }

        foreach (self::BAGIAN5_ASET as $kode => $daftarNama) {
            foreach ($daftarNama as $nama) {
                $dtks->{"kd_{$nama}"} = $kosongKeNull($data[$kode]);
            }
        }

        foreach (self::BAGIAN5_TERNAK as $kode => $nama) {
            $dtks->{"jumlah_{$nama}"} = is_numeric($data[$kode]) ? (int) $data[$kode] : 0;
        }

        $dtks->kd_lahan               = $kosongKeNull($data['503a']);
        $dtks->kd_rumah_ditempat_lain = $kosongKeNull($data['503b']);
        $dtks->kd_internet_sebulan    = $kosongKeNull($data['505']);
        $dtks->kd_rek_aktif           = $kosongKeNull($data['506']);

        // Bagian VI - Catatan
        $dtks->catatan = $kosongKeNull($data['catatan']);
    }

    /**
     * Validasi kode pilihan Bagian V terhadap Regsosek2022kEnum::YA_TIDAK / pilihanBagian5(),
     * mereplikasi pengecekan array_key_exists() yang dipakai saveBagian5().
     */
    private function validasiKodeDtks56(array $data): string
    {
        $yaTidak   = Regsosek2022kEnum::YA_TIDAK;
        $bulanList = bulan();
        $pilihan5  = Regsosek2022kEnum::pilihanBagian5();

        $cekYaTidak = static function (string $kode, $nilai) use ($yaTidak): ?string {
            $nilai = trim((string) $nilai);
            if ($nilai === '') {
                return null;
            }

            return array_key_exists($nilai, $yaTidak) ? null : "Kolom {$kode}: nilai '{$nilai}' harus 1 (Ya) atau 2 (Tidak).";
        };
        $cekBulan = static function (string $kode, $nilai) use ($bulanList): ?string {
            $nilai = trim((string) $nilai);
            if ($nilai === '') {
                return null;
            }
            $angka = is_numeric($nilai) ? (int) $nilai : array_search(strtolower($nilai), array_map('strtolower', $bulanList), true);

            return ($angka !== false && array_key_exists((int) $angka, $bulanList)) ? null : "Kolom {$kode}: bulan '{$nilai}' tidak dikenali.";
        };
        $cekTahun = static function (string $kode, $nilai): ?string {
            $nilai = trim((string) $nilai);
            if ($nilai === '') {
                return null;
            }

            return validate_date($nilai, 'Y') ? null : "Kolom {$kode}: tahun '{$nilai}' tidak valid.";
        };
        $cekPilihan = static function (string $kode, $nilai, array $daftarPilihan) use ($cekYaTidak): ?string {
            $nilai = trim((string) $nilai);
            if ($nilai === '') {
                return null;
            }

            return array_key_exists($nilai, $daftarPilihan) ? null : "Kolom {$kode}: kode '{$nilai}' tidak dikenali.";
        };

        $pesan = [];
        foreach (self::BAGIAN5_BANSOS as $kode => $nama) {
            $pesan[] = $cekYaTidak("{$kode}_dapat", $data["{$kode}_dapat"]);
            $pesan[] = $cekBulan("{$kode}_bulan", $data["{$kode}_bulan"]);
            $pesan[] = $cekTahun("{$kode}_tahun", $data["{$kode}_tahun"]);
        }
        foreach (array_keys(self::BAGIAN5_ASET) as $kode) {
            $pesan[] = $cekYaTidak($kode, $data[$kode]);
        }
        $pesan[] = $cekYaTidak('503a', $data['503a']);
        $pesan[] = $cekYaTidak('503b', $data['503b']);
        $pesan[] = $cekPilihan('505', $data['505'], $pilihan5['505']);
        $pesan[] = $cekPilihan('506', $data['506'], $pilihan5['506']);

        return implode(' ', array_filter($pesan));
    }

    /**
     * proses singkronisasi jumlah anggota dtks dengan anggota keluarga yg berubah
     *
     * @param mixed $rtm
     */
    protected function syncDtksRtm($rtm)
    {
        $semua_anggota = Penduduk::without([
            'jenisKelamin',
            'agama',
            'pendidikan',
            'pendidikanKK',
            'pekerjaan',
            'wargaNegara',
            'golonganDarah',
            'cacat',
            'statusKawin',
            'pendudukStatus',
            'wilayah',
        ])
            ->select('id', 'nama', 'id_rtm', 'rtm_level', 'id_kk', 'kk_level')
            ->whereIn('id_rtm', $rtm->pluck('no_kk'))
            ->get();
        $semua_dtks = ModelDtks::select('id', 'id_rtm', 'id_keluarga', 'versi_kuisioner')
            ->withCount('dtksAnggota')
            ->whereIn('id_rtm', $rtm->pluck('id'))
            ->get();

        foreach ($rtm as $item) {
            $dtks_rtm = $semua_dtks->where('id_rtm', $item->id);

            if ($dtks_rtm->count() != 0) {
                $jumlah_dtks_anggota = $dtks_rtm->reduce(static fn ($carry, $item) => $carry + $item->dtks_anggota_count);
                $jumlah_anggota_rt   = $semua_anggota->where('id_rtm', $item->no_kk)->count();

                if ($jumlah_anggota_rt != $jumlah_dtks_anggota) {
                    foreach ($dtks_rtm as $dtks) {
                        if ($dtks->versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
                            return (new DTKSRegsosEk2022k())->generateDefaultDtks($dtks);
                        }
                    }
                }
            }
        }
    }

    public function index()
    {
        $rtm = Rtm::with([
            'kepalaKeluarga' => static function ($builder): void {
                $builder->select('id', 'nama', 'nik');
                $builder->without([
                    'jenisKelamin',
                    'agama',
                    'pendidikan',
                    'pendidikanKK',
                    'pekerjaan',
                    'wargaNegara',
                    'golonganDarah',
                    'cacat',
                    'statusKawin',
                    'pendudukStatus',
                    'wilayah',
                ]);
            },
        ])->where('terdaftar_dtks', 1)->get();

        $this->syncDtksRtm($rtm);

        $data['rtm'] = $rtm->filter(static fn ($value) => ! in_array($value->id, ModelDtks::pluck('id_rtm')->toArray()));

        return view('admin.dtks.index', $data);
    }

    public function datatables()
    {
        if ($this->input->is_ajax_request()) {
            $rtm      = (new Rtm())->getTable();
            $keluarga = (new Keluarga())->getTable();
            $penduduk = (new Penduduk())->getTable();
            $wilayah  = (new Wilayah())->getTable();
            //  =
            $join = DB::table('dtks')
                ->select(
                    'dtks.id',
                    'dtks.id_rtm',
                    'dtks.id_keluarga',
                    'is_draft',
                    'versi_kuisioner',
                    'dtks.updated_at',
                    'nama_petugas_pencacahan',
                    'nama_responden',
                    'nama_ppl'
                )
                ->addSelect('krt.nik as nik_krt', 'krt.nama as nama_krt', 'kk.nik as nik_kk', 'kk.nama as nama_kk')
                ->addSelect('wil_krt.dusun as dusun_krt', 'wil_krt.rt as rt_krt', 'wil_krt.rw as rw_krt', 'wil_kk.dusun as dusun_kk', 'wil_kk.rt as rt_kk', 'wil_kk.rw as rw_kk')
                ->addSelect(DB::raw("(SELECT COUNT(DISTINCT(a.id_kk)) FROM {$penduduk} AS a WHERE rtm.no_kk = a.id_rtm ) as `keluarga_count`"))
                ->addSelect(DB::raw('(SELECT COUNT(*) FROM dtks_anggota WHERE dtks.id = dtks_anggota.id_dtks) as `anggota_count`'))
                ->join($rtm . ' AS rtm', 'rtm.id', '=', 'dtks.id_rtm')
                ->join($keluarga . ' AS keluarga', 'keluarga.id', '=', 'dtks.id_keluarga')
                ->join($penduduk . ' AS krt', 'rtm.nik_kepala', '=', 'krt.id')
                ->join($penduduk . ' AS kk', 'keluarga.nik_kepala', '=', 'kk.id')
                ->join($wilayah . ' AS wil_krt', 'krt.id_cluster', '=', 'wil_krt.id')
                ->join($wilayah . ' AS wil_kk', 'kk.id_cluster', '=', 'wil_kk.id')
                ->where('dtks.config_id', identitas('id'));

            $case_sql = static function (&$query, $keyword, array $fields = [DtksEnum::REGSOS_EK2022_K => ''], string $operator = 'LIKE') {
                $sql     = '(versi_kuisioner = ' . DtksEnum::REGSOS_EK2022_K . ' AND ' . $fields[DtksEnum::REGSOS_EK2022_K] . ' ' . $operator . ' ?)';
                $binding = ["%{$keyword}%"];

                return $query->whereRaw($sql, $binding);
            };
            $add_column = static function (&$row, array $fields = [DtksEnum::REGSOS_EK2022_K => '']) {
                if ($row->versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
                    return $row->{$fields[DtksEnum::REGSOS_EK2022_K]};
                }
            };

            return datatables()->of($join)
                ->addColumn('ceklist', static function ($row) {
                    if (can('h')) {
                        return '<input type="checkbox" name="id_cb[]" value="' . $row->id . '"/>';
                    }
                })
                ->addIndexColumn()
                ->addColumn('aksi', static function ($row): string {
                    $aksi = '';
                    // $aksi .= '<a href=" '. ci_route("dtks.detail.{$row->id}") . '" class="btn bg-purple btn-flat btn-sm" title="Rincian Data"><i class="fa fa-list-ol"></i></a>';
                    if (can('u')) {
                        $aksi .= '&nbsp;<a href="' . ci_route("dtks.form.{$row->id}") . '" class="btn btn-warning btn-sm"  title="Lihat & Ubah Data"><i class="fa fa-edit"></i></a> ';
                        $aksi .= '&nbsp;<a href="#" data-id="' . $row->id . '" class="btn-hapus btn btn-danger btn-sm" data-remote="false" data-toggle="modal" data-target="#modal-confirm-delete-dtks" title="Hapus Data"><i class="fa fa-trash"></i></a> ';
                    }

                    return $aksi;
                })
                ->addColumn('dusun', static fn ($row) => $add_column($row, [DtksEnum::REGSOS_EK2022_K => 'dusun_krt']))
                ->filterColumn('dusun', static fn ($query, $keyword) => $case_sql($query, $keyword, [DtksEnum::REGSOS_EK2022_K => 'wil_krt.dusun']))
                ->addColumn('rt', static fn ($row) => $add_column($row, [DtksEnum::REGSOS_EK2022_K => 'rt_krt']))
                ->filterColumn('rt', static fn ($query, $keyword) => $case_sql($query, $keyword, [DtksEnum::REGSOS_EK2022_K => 'wil_krt.rt']))
                ->addColumn('rw', static fn ($row) => $add_column($row, [DtksEnum::REGSOS_EK2022_K => 'rw_krt']))
                ->filterColumn('rw', static fn ($query, $keyword) => $case_sql($query, $keyword, [DtksEnum::REGSOS_EK2022_K => 'wil_krt.rw']))
                ->addColumn('petugas', static fn ($row) => $add_column($row, [DtksEnum::REGSOS_EK2022_K => 'nama_ppl']))
                ->addColumn('responden', static fn ($row) => $row->nama_responden)
                ->addColumn('versi_kuisioner', static fn ($row): string => DtksEnum::VERSION_LIST[$row->versi_kuisioner])
                ->filterColumn('versi_kuisioner', static function ($query, $keyword): void {
                })
                ->rawColumns(['ceklist', 'aksi'])
                ->toJson();
        }

        return show_404();
    }

    public function listAnggota($id_dtks)
    {
        $this->syncDtksRtm(Rtm::where('terdaftar_dtks', 1)->get());
        $data['anggota'] = DtksAnggota::with([
            'penduduk' => static function ($builder): void {
                $builder->select('id', 'nama', 'nik');
                $builder->without([
                    'jenisKelamin',
                    'agama',
                    'pendidikan',
                    'pendidikanKK',
                    'pekerjaan',
                    'wargaNegara',
                    'golonganDarah',
                    'cacat',
                    'statusKawin',
                    'pendudukStatus',
                    'wilayah',
                ]);
            },
        ])
            ->select('id', 'id_dtks', 'id_penduduk')
            ->where('id_dtks', $id_dtks)
            ->get();

        return view('admin.dtks.list_anggota', $data);
    }

    public function loadRecentInfo()
    {
        try {
            return (new DTKSRegsosEk2022k())->info();
        } catch (Throwable $th) {
            echo 'File info tidak ditemukan';
        }
    }

    public function loadRecentImpor()
    {
        try {
            return (new DTKSRegsosEk2022k())->impor();
        } catch (Throwable $th) {
            echo 'File info tidak ditemukan';
        }
    }

    public function ekspor()
    {
        $versi_kuisioner = $this->input->get('versi');
        if ($versi_kuisioner == DtksEnum::REGSOS_EK2021_RT) {
            redirect_with('error', 'Proses versi tidak ditemukan', ci_route('dtks'));
        } elseif ($versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
            return (new DTKSRegsosEk2022k())->ekspor();
        } else {
            redirect_with('error', 'Versi tidak ditemukan', ci_route('dtks'));
        }
    }

    public function cetak2($id = null)
    {
        $ids = $this->request['id'] ?? [];

        $dtks = ModelDtks::whereIn('id', $ids)
            ->orWhere('id', $id)
            ->get();

        if ($dtks->count() == 0) {
            if ($this->input->is_ajax_request()) {
                return json(['message' => 'Data terpilih tidak ditemukan'], 404);
            }
            redirect_with('error', 'Data terpilih tidak ditemukan', $_SERVER['HTTP_REFERER']);
        } elseif ($dtks->count() == 1) {
            // lempar ke halaman baru tanpa ajax, (dilakukan oleh js)
            if ($this->input->is_ajax_request()) {
                return json(['message' => 'Mengunduh 1 data', 'href' => ci_route('dtks/cetak2/' . $dtks->first()->id)], 200);
            }
        }

        if ($dtks->count() == 1) {
            $versi_kuisioner = $dtks->first()->versi_kuisioner;
            if ($versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
                return (new DTKSRegsosEk2022k())->cetakPreviewSingle($dtks->first());
            }
        } else {
            $dtks = $dtks->groupBy('versi_kuisioner');

            // create each zip versi
            $list_path = [];

            foreach ($dtks as $versi_kuisioner => $item) {
                if ($versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
                    $paths = (new DTKSRegsosEk2022k())->cetakZip($item);
                    $list_path += $paths;
                }
            }
            // simpan
            $list_path_to_zip = collect($list_path);
            $list_path        = collect($list_path)->transform(static fn ($item, $key): array => ['id' => $item['id'], 'status_file' => $item['status_file']]);

            $proses_belum_selesai = $list_path->where('status_file', 0);

            if ($proses_belum_selesai->count() > 0) {
                return json(['message' => 'Proses Data', 'list' => $list_path], 200);
            }
            if ($this->input->is_ajax_request()) {
                return json(['message' => 'Data Siap Diunduh', 'list' => $list_path], 200);
            }

            if ($list_path_to_zip->count() != 0) {
                $this->load->library('zip');

                foreach ($list_path_to_zip as $item) {
                    $this->zip->read_file($item['file']);
                }
                $this->zip->download('berkas_dtks_regsosek_terpilih_' . date('d-m-Y') . '.zip');
            }
        }
    }

    public function new($id_rtm = 'A'): void
    {
        $id_rtm = ($id_rtm == 'A') ? bilangan($this->request['id_rtm']) : bilangan($id_rtm);

        if ($id_rtm == null) {
            redirect_with('error', 'RTM tidak ditemukan');
        }

        $dtks = ModelDtks::where([
            'id_rtm'          => $id_rtm,
            'versi_kuisioner' => DtksEnum::VERSION_CODE,
            // 'is_draft' => StatusEnum::YA, // belum terpakai karena yg dibutuhkan hanya 1 data per rtm
        ])->first();

        if (! $dtks) {
            DB::beginTransaction();
            $dtks = ModelDtks::create([
                'versi_kuisioner' => DtksEnum::VERSION_CODE,
                'id_rtm'          => $id_rtm,
                'is_draft'        => StatusEnum::YA,
            ]);
            $this->synchroniseDTKSWithOpenSid($dtks);
            DB::commit();
        }

        redirect("{$this->controller}/form/{$dtks->id}");
    }

    public function latest($id_rtm): void
    {
        $dtks = ModelDtks::where(['id_rtm' => $id_rtm])
            ->orderBy('created_at', 'ASC')
            ->first();

        if (! $dtks) {
            session_error(' : Belum ada data');
            redirect_with('error', 'Belum ada data', $_SERVER['HTTP_REFERER']);
        }
        redirect("{$this->controller}/form/{$dtks->id}");
    }

    public function form($id)
    {
        $dtks = ModelDtks::where(['id' => $id])->first();

        if (! $dtks) {
            return json(['message' => 'Formulir Tidak ditemukan'], 404);
        }

        if ($dtks->versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
            return (new DTKSRegsosEk2022k())->form($dtks);
        }
    }

    protected function synchroniseDTKSWithOpenSid(ModelDtks $dtks)
    {
        $config = Config::first();

        if (! $config) {
            session_error(' : Konfigurasi tidak ditemukan');
            redirect_with('error', 'Konfigurasi tidak ditemukan', ci_route('dtks'));
        }

        if ($dtks->versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
            $dtks = (new DTKSRegsosEk2022k())->syncronizeWithOpenSid($dtks);
        }
    }

    /**
     * savePengaturan
     *
     * @param mixed $versi_dtks
     */
    public function savePengaturan($versi_dtks)
    {
        if ($this->input->is_ajax_request()) {
            if ($versi_dtks == DtksEnum::REGSOS_EK2022_K) {
                $respon = (new DTKSRegsosEk2022k())->save($this->request);

                return json($respon['content'], $respon['header_code']);
            }

            return json(['message' => 'Tidak melakukan apapun'], 200);
        }
        if ($versi_dtks == DtksEnum::REGSOS_EK2022_K) {
            $respon = (new DTKSRegsosEk2022k())->save($this->request);

            return json($respon['content'], $respon['header_code']);
        }

        session_error(' : Tidak melakukan apapun');
        redirect_with('error', 'Tidak melakukan apapun', $_SERVER['HTTP_REFERER']);
    }

    /**
     * Save
     *
     * @param dtks_id $id
     */
    public function save($id)
    {
        $dtks = ModelDtks::with('dtksAnggota')
            ->where(['id' => $id])
            ->first();

        if ($this->input->is_ajax_request()) {
            if (! $dtks) {
                return json(['message' => 'Formulir Tidak ditemukan'], 404);
            }

            if ($dtks->versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
                $respon = (new DTKSRegsosEk2022k())->save($this->request, $dtks);

                return json($respon['content'], $respon['header_code']);
            }

            return json(['message' => 'Tidak melakukan apapun'], 200);
        }
        if (! $dtks) {
            session_error(' : Formulir tidak ditemukan');
            redirect_with('error', 'Formulir Tidak ditemukan', $_SERVER['HTTP_REFERER']);
        }

        if ($dtks->versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
            $respon = (new DTKSRegsosEk2022k())->save($this->request, $dtks);

            return json($respon['content'], $respon['header_code']);
        }

        session_error(' : Tidak melakukan apapun');
        redirect_with('error', 'Tidak melakukan apapun', $_SERVER['HTTP_REFERER']);
    }

    /**
     * Delete
     *
     * @param dtks_id $id
     */
    public function delete($id)
    {
        isCan('h');

        ModelDtks::find($id)->delete();

        return json(['message' => 'Berhasil'], 200);
    }

    /**
     * Remove some data
     *
     * @param dtks_id $id
     */
    public function remove($id)
    {
        $dtks = ModelDtks::find($id);

        if (! $dtks) {
            return json(['message' => 'Formulir Tidak ditemukan'], 404);
        }

        if ($dtks->versi_kuisioner == DtksEnum::REGSOS_EK2022_K) {
            $respon = (new DTKSRegsosEk2022k())->remove($dtks, $this->request);

            return json($respon['content'], $respon['header_code']);
        }

        return json(['message' => 'Tidak melakukan apapun'], 200);
    }
}
