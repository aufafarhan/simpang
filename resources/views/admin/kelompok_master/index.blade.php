@include('admin.layouts.components.asset_datatables')

@extends('admin.layouts.index')

@php $tipe = str_replace('_master', '', $ci->controller); @endphp

@section('title')
    <h1>
        Kategori {{ ucfirst($tipe) }}
    </h1>
@endsection

@section('breadcrumb')
    <li><a href="<?= site_url($tipe) ?>"> Daftar <?= ucfirst($tipe) ?></a></li>
    <li class="active">Kategori {{ ucfirst($tipe) }}</li>
@endsection

@section('content')
    @include('admin.layouts.components.notifikasi')
    @include('admin.components.impor_ringkasan')
    @include('admin.layouts.components.konfirmasi_hapus')

    <div class="box box-info">
        <div class="box-header with-border">
            <x-tambah-button 
            @if (can('u'))
                <a href="#modal-impor-{{ $ci->controller }}" data-toggle="modal" data-target="#modal-impor-{{ $ci->controller }}" title="Impor" class="btn btn-social bg-navy btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor</a>
            @endif
                :url="$ci->controller . '/form'" 
            />

            <x-hapus-button 
                :url="$ci->controller . '/delete_all'"
                :confirmDelete="true"
                :selectData="true"
            />

            <x-kembali-button 
                :url="$tipe"
                :judul="'Kembali Ke Daftar ' . ucfirst($tipe)"
            />
        </div>
        <div class="box-body">
            {!! form_open(null, 'id="mainform" name="mainform"') !!}
            <div class="table-responsive">
                <table class="table table-bordered table-hover tabel-daftar" id="tabeldata">
                    <thead class="bg-gray">
                        <tr>
                            <th class="padat"><input type="checkbox" id="checkall" /></th>
                            <th class="padat">No</th>
                            <th class="aksi">Aksi</th>
                            <th class="padat">Kategori {{ ucfirst($tipe) }}</th>
                            <th>Deskripsi {{ ucfirst($tipe) }}</th>
                            <th class="padat">Jumlah {{ ucfirst($tipe) }}</th>
                        </tr>
                    </thead>
                </table>
            </div>
            </form>
        </div>
    </div>

    @if (can('u'))
        @include('admin.components.modal_impor', [
            'modalId' => 'modal-impor-' . $ci->controller,
            'judul' => 'Impor Kategori ' . ucfirst($tipe),
            'formAction' => ci_route("{$ci->controller}.proses_impor"),
            'formatImpor' => ci_route("{$ci->controller}.format_impor"),
            'petunjuk' => [
                'Kolom: <b>kelompok, deskripsi</b> (urutan tidak boleh diubah).',
                'Kolom <b>kelompok</b> (nama kategori) wajib diisi.',
                'Baris dengan nama kategori yang sama dengan data yang sudah ada akan dilewati (dianggap duplikat).',
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
                    url: "{{ $ci->controller }}",
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
                        data: 'kelompok',
                        name: 'kelompok',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'deskripsi',
                        name: 'deskripsi',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'jumlah',
                        name: 'jumlah',
                        class: 'padat',
                        searchable: false,
                        orderable: false
                    },
                ],
                order: [
                    [3, 'asc']
                ],
                pageLength: 25,
                createdRow: function(row, data, dataIndex) {
                    if (data.jenis == 0 || data.jenis == 1) {
                        $(row).addClass('select-row');
                    }
                }
            });

            if (hapus == 0) {
                TableData.column(0).visible(false);
            }

            if (ubah == 0) {
                TableData.column(2).visible(false);
                TableData.column(7).visible(false);
            }
        });
    </script>
@endpush
