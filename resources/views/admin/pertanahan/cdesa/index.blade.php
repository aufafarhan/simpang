@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1>
        Daftar C-Desa
    </h1>
@endsection

@section('breadcrumb')
    <li class="active">Daftar C-Desa</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')
    @include('admin.components.impor_ringkasan')
    <div class="box box-info">
        <div class="box-header with-border">
            <x-tambah-button :url="'cdesa/form'" />
            @if (can('u'))
                <a href="#modal-impor-cdesa" data-toggle="modal" data-target="#modal-impor-cdesa" class="btn btn-social bg-navy btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor C-Desa</a>
                <a href="#modal-impor-mutasi-cdesa" data-toggle="modal" data-target="#modal-impor-mutasi-cdesa" class="btn btn-social bg-olive btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor Mutasi Persil</a>
            @endif
            <x-hapus-button confirmDelete="true" selectData="true" :url="'cdesa/delete_all'" />
            @php
                $listCetakUnduh = [
                    [
                        'url' => "cdesa/dialog/cetak",
                        'judul' => 'Cetak',
                        'icon' => 'fa fa-print',
                        'modal' => true,
                    ],
                    [
                        'url' => "cdesa/dialog/unduh",
                        'judul' => 'Unduh',
                        'icon' => 'fa fa-download',
                        'modal' => true,
                    ]
                ];
            @endphp

            <x-split-button
                judul="Cetak/Unduh"
                :list="$listCetakUnduh"
                :icon="'fa fa-arrow-circle-down'"
                :type="'bg-purple'"
                :target="true"
            />
        </div>
        <div class="box-body">
            {!! form_open(null, 'id="mainform" name="mainform"') !!}
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="tabeldata">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="checkall" /></th>
                            <th class="padat">NO</th>
                            <th class="padat">AKSI</th>
                            <th>NO. CDESA</th>
                            <th>NAMA DI C-DESA</th>
                            <th>NAMA PEMILIK</th>
                            <th>NIK</th>
                            <th>JUMLAH PERSIL</th>
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
            'modalId' => 'modal-impor-cdesa',
            'judul' => 'Impor Data C-Desa',
            'formAction' => ci_route('cdesa.proses_impor'),
            'formatImpor' => ci_route('cdesa.format_impor'),
            'petunjuk' => [
                'Kolom: <b>nomor, nama_kepemilikan, jenis_pemilik, nik_pemilik, nama_pemilik_luar, alamat_pemilik_luar</b> (urutan tidak boleh diubah).',
                'Kolom <b>jenis_pemilik</b> diisi 1 untuk Warga Desa atau 2 untuk Warga Luar Desa.',
                'Jika jenis_pemilik = 1, kolom <b>nik_pemilik</b> wajib diisi NIK yang sudah terdaftar sebagai penduduk desa.',
                'Jika jenis_pemilik = 2, kolom <b>nama_pemilik_luar</b> dan <b>alamat_pemilik_luar</b> wajib diisi.',
                'Baris dengan <b>nomor</b> yang sama dengan data yang sudah ada akan dilewati (dianggap duplikat).',
            ],
        ])
        @include('admin.components.modal_impor', [
            'modalId' => 'modal-impor-mutasi-cdesa',
            'judul' => 'Impor Data Mutasi Persil',
            'formAction' => ci_route('cdesa.proses_impor_mutasi'),
            'formatImpor' => ci_route('cdesa.format_impor_mutasi'),
            'petunjuk' => [
                'Kolom: <b>nomor_cdesa_tujuan, no_persil, nomor_urut_bidang, no_bidang_persil, no_objek_pajak, tanggal_mutasi, jenis_mutasi, luas, nomor_cdesa_asal, keterangan</b> (urutan tidak boleh diubah).',
                'Kolom <b>nomor_cdesa_tujuan</b> harus sesuai nomor C-Desa yang sudah terdaftar; <b>no_persil</b> + <b>nomor_urut_bidang</b> harus sesuai persil yang sudah terdaftar.',
                'Impor ini khusus mutasi biasa (perpindahan/pembagian persil), TIDAK untuk data pemilik awal persil (itu otomatis dibuat lewat impor Data Persil).',
                'Kolom <b>jenis_mutasi</b> diisi sesuai nama pada Referensi Sebab Mutasi.',
                'Kolom <b>nomor_cdesa_asal</b> opsional, diisi jika mutasi berasal dari C-Desa lain.',
                'Baris dengan persil, C-Desa tujuan, jenis mutasi, dan tanggal yang sama seperti baris lain pada berkas yang sama akan dilewati (dianggap duplikat).',
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
                ajax: "{{ ci_route('cdesa.datatables') }}",
                columns: [{
                        data: 'ceklist',
                        class: 'padat',
                        searchable: false,
                        orderable: false
                    },
                    {
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
                        data: 'nomor',
                        name: 'nomor',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'nama_kepemilikan',
                        name: 'nama_kepemilikan',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'nama_pemilik',
                        name: 'nama_pemilik',
                        searchable: true,
                        orderable: false
                    },
                    {
                        name: 'nik_pemilik',
                        data: 'nik_pemilik',
                        searchable: true,
                        orderable: false,
                        render: function(item, data, row) {
                            return row.jenis_pemilik == 1 ? `<a href='{{ ci_route('penduduk.detail') }}/${row.id_pemilik}'>${item}</a>` : item
                        },
                    },
                    {
                        data: 'jumlah',
                        name: 'jumlah',
                        searchable: false,
                        orderable: false,
                        class: 'padat'
                    },
                ],
                order: [
                    [3, 'asc']
                ]
            });

            if (hapus == 0) {
                TableData.column(0).visible(false);
                $('.akses-hapus').remove();
            }

            if (ubah == 0) {
                TableData.column(2).visible(false);
            }
        });
    </script>
@endpush
