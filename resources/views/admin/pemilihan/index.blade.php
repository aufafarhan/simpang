@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1>
        Data {{ $module_name }}
    </h1>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ ci_route('dpt') }}"> DPT</a></li>
    <li class="active">Data {{ $module_name }}</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')
    @include('admin.components.impor_ringkasan')

    <div class="box box-info">
        <div class="box-header with-border">
            @include('admin.layouts.components.buttons.tambah', ['url' => 'pemilihan/form'])
            @include('admin.layouts.components.buttons.hapus', [
                'url' => "pemilihan/delete_all",
                'confirmDelete' => true,
                'selectData' => true,
            ])
            @include('admin.layouts.components.tombol_kembali', ['url' => ci_route('dpt'), 'label' => 'DPT'])

@if (can('u'))
    <a href="#modal-impor-pemilihan" data-toggle="modal" data-target="#modal-impor-pemilihan" class="btn btn-social bg-navy btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor</a>
@endif
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
                            <th>JUDUL</th>
                            <th class="padat">TANGGAL</th>
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
            'modalId' => 'modal-impor-pemilihan',
            'judul' => 'Impor Data Pemilihan',
            'formAction' => ci_route('pemilihan.proses_impor'),
            'formatImpor' => ci_route('pemilihan.format_impor'),
            'petunjuk' => [
                'Kolom: <b>judul, tanggal, status, keterangan</b> (urutan tidak boleh diubah).',
                'Kolom <b>tanggal</b> diisi format tanggal apa saja yang bisa dibaca (mis. 2026-08-17 atau 17-08-2026).',
                'Kolom <b>status</b> diisi 1/Aktif untuk pemilihan yang aktif, selain itu dianggap tidak aktif.',
                'Baris dengan judul dan tanggal yang sama seperti baris sebelumnya akan dilewati (dianggap duplikat).',
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
                ajax: "{{ ci_route('pemilihan.datatables') }}",
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
                        data: 'judul',
                        name: 'judul',
                    },
                    {
                        data: 'tanggal',
                        name: 'tanggal',
                        class: 'padat',
                    },
                ],
                order: [
                    [4, 'asc']
                ]
            });

            if (hapus == 0) {
                TableData.column(0).visible(false);
            }

            if (ubah == 0) {
                TableData.column(2).visible(false);
            }
        });
    </script>
@endpush
