@extends('admin.layouts.index')
@include('admin.layouts.components.asset_datatables')
@include('admin.layouts.components.jquery_ui')

@section('title')
    <h1>
        {{ $action }} {{ $header }}
    </h1>
@endsection

@section('breadcrumb')
    <li class="active">{{ $action }} {{ $header }}</li>
@endsection

@push('css')
    <style>
        .table .btn {
            margin-right: 2px;
        }
    </style>
@endpush

@section('content')
    @include('admin.layouts.components.notifikasi')
    @include('admin.components.impor_ringkasan')

    <div class="row">
        <div class="col-md-3">
            @include('admin.inventaris.menu')
        </div>
        <div class="col-md-9">
            <div class="box box-info">
                <div class="box-header with-border">
                    <x-tambah-button :url="'inventaris_asset/form'" />
                    @if (can('u'))
                        <a href="#modal-impor-inventaris-asset" data-toggle="modal" data-target="#modal-impor-inventaris-asset" class="btn btn-social bg-navy btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor</a>
                    @endif
                    @php
                        $listCetakUnduh = [
                            [
                                'url' => "inventaris_asset/dialog/cetak",
                                'judul' => 'Cetak',
                                'icon' => 'fa fa-print',
                                'modal' => true,
                            ],
                            [
                                'url' => "inventaris_asset/dialog/unduh",
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
                    <div class="table-responsive">
                        <table id="tabel4" class="table table-bordered dataTable table-hover">
                            <thead class="bg-gray">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th class="text-center">Aksi</th>
                                    <th class="text-center">Nama Barang</th>
                                    <th class="text-center">Kode Barang / Nomor Registrasi</th>
                                    <th class="text-center">Jumlah</th>
                                    <th class="text-center">Tahun Pembelian</th>
                                    <th class="text-center">Asal Usul</th>
                                    <th class="text-center">Harga (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" style="text-align: right;">Total:</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.layouts.components.konfirmasi_hapus')

    @if (can('u'))
        @include('admin.components.modal_impor', [
            'modalId' => 'modal-impor-inventaris-asset',
            'judul' => 'Impor Data Inventaris Asset Lainnya',
            'formAction' => ci_route('inventaris_asset.proses_impor'),
            'formatImpor' => ci_route('inventaris_asset.format_impor'),
            'petunjuk' => [
                'Kolom: <b>nama_barang, kode_barang, register, jenis, judul_buku, spesifikasi_buku, asal_daerah, pencipta, bahan, jenis_hewan, ukuran_hewan, jenis_tumbuhan, ukuran_tumbuhan, jumlah, tahun_pengadaan, asal, harga, keterangan</b> (urutan tidak boleh diubah).',
                'Hanya kolom <b>nama_barang</b> yang wajib diisi. Kolom kategori-spesifik (judul_buku, jenis_hewan, dst.) diisi sesuai jenis barangnya saja, sisanya boleh dikosongkan.',
                'Baris dengan kode_barang dan register yang sama dengan data yang sudah ada akan dilewati (dianggap duplikat).',
            ],
        ])
    @endif
@endsection

@push('scripts')
    {{-- link moment js --}}
    <script src="{{ asset('bootstrap/moment.min.js') }}"></script>
    {{-- link datetimepicker --}}
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            var TableData = $('#tabel4').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ ci_route('inventaris_asset.datatables') }}",
                    data: function(req) {}
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
                        data: 'nama_barang',
                        name: 'nama_barang',
                        searchable: true,
                        orderable: true,
                    },
                    {
                        data: 'kode_barang_register',
                        name: 'kode_barang',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'jumlah',
                        name: 'jumlah',
                        empty: '-',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'tahun_pengadaan',
                        name: 'tahun_pengadaan',
                        empty: '-',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'asal',
                        name: 'asal',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'harga',
                        name: 'harga',
                        searchable: true,
                        orderable: true
                    },
                ],
                aaSorting: [],
                createdRow: function(row, data, dataIndex) {
                    $(row).attr('data-id', data.id)
                },
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    var columnData = api.column(7, {
                        page: 'current'
                    }).data();

                    var total = columnData.reduce(function(a, b) {
                        return a + parseFloat(b.replace(/\./g, ''));
                    }, 0);

                    $(api.column(7).footer()).html(total.toLocaleString('id-ID'));
                }
            });

            $('#status').change(function() {
                TableData.draw();
            })
        });

        $("#form_cetak").click(function(event) {
            var link = '{{ site_url('inventaris_asset/cetak') }}' + '/' + $('#tahun_pdf').val() + '/' + $('#penandatangan_pdf').val();
            window.open(link, '_blank');
        });
        $("#form_download").click(function(event) {
            var link = '{{ site_url('inventaris_asset/download') }}' + '/' + $('#tahun').val() + '/' + $('#penandatangan').val();
            window.open(link, '_blank');
        });
    </script>
@endpush
