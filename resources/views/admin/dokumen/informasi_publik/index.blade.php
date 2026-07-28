@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@section('title')
    <h1>
        {{ $kat_nama }}
    </h1>
@endsection

@section('breadcrumb')
    <li class="active">{{ $kat_nama }}</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')
    @include('admin.components.impor_ringkasan')
    <div class="row">
        <form id="mainform" name="mainform" method="post">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-info">
                        <div class="box-header with-border">
                            @if (can('u'))
                                <a href="{{ ci_route('dokumen.form') }}" class="btn btn-social btn-success btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block" title="Tambah Menu Baru">
                                    <i class="fa fa-plus"></i>Tambah
                                </a>
                                <a href="#modal-impor-dokumen" data-toggle="modal" data-target="#modal-impor-dokumen" class="btn btn-social bg-navy btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor</a>
                            @endif
                            @if (can('h'))
                                <a href="#confirm-delete" title="Hapus Data" onclick="deleteAllBox('mainform', '{{ ci_route('dokumen.delete') }}')" class="btn btn-social btn-danger btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block hapus-terpilih"><i
                                        class='fa fa-trash-o'
                                    ></i> Hapus</a>
                            @endif
                            <a
                                href="{{ ci_route('dokumen.dialog_cetak.cetak') }}"
                                class="btn btn-social bg-purple btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"
                                title="Cetak Dokumen"
                                data-remote="false"
                                data-toggle="modal"
                                data-target="#modalBox"
                                data-title="Cetak Laporan"
                            >
                                <i class="fa fa-print"></i>Cetak
                            </a>
                            <a
                                href="{{ ci_route('dokumen.dialog_cetak.unduh') }}"
                                class="btn btn-social bg-navy btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"
                                title="Unduh Dokumen"
                                data-remote="false"
                                data-toggle="modal"
                                data-target="#modalBox"
                                data-title="Unduh Laporan"
                            >
                                <i class="fa fa-download"></i>Unduh
                            </a>

                            <a
                                href="{{ ci_route('dokumen.ekspor') }}"
                                class="btn btn-social bg-blue btn-sm btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"
                                title="Ekspor Data"
                                data-remote="false"
                                data-toggle="modal"
                                data-target="#modalBox"
                                data-title="Ekspor Data Informasi Publik"
                            >
                                <i class="fa fa-download"></i>Ekspor
                            </a>
                        </div>
                        <div class="box-body">
                            <div class="row mepet">
                                <div class="col-sm-2">
                                    <select id="status" class="form-control input-sm select2">
                                        <option value="">Pilih Status</option>
                                        @foreach ($status as $key => $item)
                                            <option value="{{ $key }}">{{ $item }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <hr class="batas">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped dataTable table-hover" id="tabeldata">
                                    <thead class="bg-gray disabled color-palette">
                                        <tr>
                                            <th><input type="checkbox" id="checkall" /></th>
                                            <th>No</th>
                                            <th>Aksi</th>
                                            <th>Judul</th>
                                            <th>Kategori Info Publik</th>
                                            <th>Tahun</th>
                                            <th nowrap>Aktif</th>
                                            <th nowrap>Dimuat Pada </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('admin.layouts.components.konfirmasi_hapus')

    @if (can('u'))
        @include('admin.components.modal_impor', [
            'modalId' => 'modal-impor-dokumen',
            'judul' => 'Impor Dokumen Informasi Publik',
            'formAction' => ci_route('dokumen.proses_impor'),
            'formatImpor' => ci_route('dokumen.format_impor'),
            'petunjuk' => [
                'Kolom: <b>nama, tahun, kategori_info_publik, url</b> (urutan tidak boleh diubah).',
                'Impor ini hanya untuk dokumen berupa <b>tautan/URL</b> (mis. tautan Google Drive) — dokumen berupa berkas unggahan (PDF/gambar) tidak bisa dibawa lewat Excel, tambahkan manual lewat form.',
                'Kolom <b>kategori_info_publik</b> diisi salah satu: Informasi Berkala, Informasi Serta-merta, Informasi Setiap Saat, Informasi Dikecualikan.',
                'Kolom <b>url</b> wajib berupa tautan yang valid (diawali http:// atau https://).',
                'Baris dengan <b>nama</b> dan <b>tahun</b> yang sama seperti data yang sudah ada akan dilewati (dianggap duplikat).',
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
                    url: "{{ ci_route('dokumen.datatables') }}",
                    data: function(req) {
                        req.status = $('#status').val();
                    }
                },
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
                        data: 'nama',
                        name: 'nama',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'infoPublic',
                        name: 'infoPublic',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'tahun',
                        name: 'tahun',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'aktif',
                        name: 'aktif',
                        searchable: false,
                        orderable: true
                    },
                    {
                        data: 'dimuat',
                        name: 'tgl_upload',
                        searchable: false,
                        orderable: true
                    },
                ],
                aaSorting: [],
                order: [
                    [7, 'desc']
                ],
            });

            if (hapus == 0) {
                TableData.column(0).visible(false);
            }

            if (ubah == 0) {
                TableData.column(2).visible(false);
            }

            $('#status').change(function() {
                TableData.draw()
            })

        });
    </script>
@endpush
