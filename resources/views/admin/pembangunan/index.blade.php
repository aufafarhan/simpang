@include('admin.layouts.components.asset_datatables')
@extends('admin.layouts.index')

@section('title')
    <h1>
        Pembangunan
    </h1>
@endsection

@section('breadcrumb')
    <li class="active">Pembangunan</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')
    @include('admin.components.impor_ringkasan')

    <div class="box box-info">
        <div class="box-header with-border">
            <x-tambah-button :url="'admin_pembangunan/form'" />
            @if (can('u'))
                <a href="#modal-impor-pembangunan" data-toggle="modal" data-target="#modal-impor-pembangunan" class="btn btn-social bg-navy btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor</a>
            @endif
        </div>
        <div class="box-body">
            <div class="row mepet">
                <div class="col-sm-2">
                    <select id="tahun" class="form-control input-sm select2">
                        <option value="">Pilih Tahun</option>
                        @foreach ($tahun as $item)
                            <option>{{ $item->tahun_anggaran }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <hr class="batas">
            {!! form_open(null, 'id="mainform" name="mainform"') !!}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabeldata">
                    <thead>
                        <tr>
                            <th class="padat">NO</th>
                            <th class="padat">AKSI</th>
                            <th>NAMA KEGIATAN</th>
                            <th>SUMBER DANA</th>
                            <th>PAGU ANGGARAN</th>
                            <th>PERSENTASE</th>
                            <th>VOLUME</th>
                            <th>TAHUN</th>
                            <th>PELAKSANA</th>
                            <th>LOKASI</th>
                            <th class="padat">GAMBAR</th>
                        </tr>
                    </thead>
                </table>
            </div>
            </form>
        </div>
    </div>

    @include('admin.layouts.components.konfirmasi_hapus')

    @if (can('u'))
        @include('admin.components.modal_impor', [
            'modalId' => 'modal-impor-pembangunan',
            'judul' => 'Impor Data Pembangunan',
            'formAction' => ci_route('admin_pembangunan.proses_impor'),
            'formatImpor' => ci_route('admin_pembangunan.format_impor'),
            'petunjuk' => [
                'Kolom: <b>judul, sumber_dana, volume, waktu, satuan_waktu, tahun_anggaran, pelaksana_kegiatan, lokasi, anggaran, sumber_biaya_pemerintah, sumber_biaya_provinsi, sumber_biaya_kab_kota, sumber_biaya_swadaya, manfaat, sifat_proyek, keterangan</b> (urutan tidak boleh diubah).',
                'Kolom <b>judul</b> dan <b>tahun_anggaran</b> wajib diisi.',
                'Kolom <b>sumber_dana</b> diisi teks sesuai Referensi Sumber Dana, <b>satuan_waktu</b> diisi Hari/Minggu/Bulan/Tahun.',
                'Kolom <b>sifat_proyek</b> diisi BARU atau LANJUTAN.',
                'Impor ini membuat proyek baru berstatus "Rencana" — belum ada dokumentasi progres. Untuk menambah dokumentasi progres (menaikkan status ke Kegiatan/Hasil), gunakan impor di halaman Rincian Dokumentasi proyek terkait.',
                'Kolom foto/gambar tidak tersedia lewat impor Excel — tambahkan manual lewat form jika perlu.',
            ],
        ])
    @endif
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var TableData = $('#tabeldata').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ ci_route('admin_pembangunan.datatables') }}",
                    data: function(req) {
                        req.tahun = $('#tahun').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        class: 'padat',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'aksi',
                        class: 'aksi',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'judul',
                        name: 'judul',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'sumber_dana',
                        name: 'sumber_dana',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'anggaran',
                        name: 'anggaran',
                        render: $.fn.dataTable.render.number('.', ',', 0, 'Rp '),
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'persentase',
                        name: 'persentase',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'volume',
                        name: 'volume',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'tahun_anggaran',
                        name: 'tahun_anggaran',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'pelaksana_kegiatan',
                        name: 'pelaksana_kegiatan',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'alamat',
                        name: 'wilayah.dusun',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'foto',
                        name: 'foto',
                        searchable: false,
                        orderable: false
                    },
                ],
                order: [
                    [7, 'desc']
                ]
            });

            $('#tahun').change(function() {
                TableData.draw()
            })
        });
    </script>
@endpush
