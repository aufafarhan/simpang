@include('admin.components.impor_ringkasan')
<div class="box box-info">
    <div class="box-header with-border">
        <x-tambah-button :url="'bumindes_tanah_kas_desa/form/'" />
        @if (can('u'))
            <a href="#modal-impor-bumindes-tanah-kas-desa" data-toggle="modal" data-target="#modal-impor-bumindes-tanah-kas-desa" class="btn btn-social bg-navy btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block"><i class="fa fa-upload"></i> Impor</a>
        @endif
       
        @php
            $listCetakUnduh = [
                [
                    'url' => 'bumindes_tanah_kas_desa/dialog_cetak/cetak',
                    'judul' => 'Cetak',
                    'icon' => 'fa fa-print',
                    'modal' => true,
                ],
                [
                    'url' => 'bumindes_tanah_kas_desa/dialog_cetak/unduh',
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
        <div class="row">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <table id="tabeldata" class="table table-bordered dataTable table-hover">
                                <thead class="bg-gray">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th width="120" class="text-center">Aksi</th>
                                        <th class="text-center">Asal Tanah</th>
                                        <th width="100" class="text-center">Nomor Sertifikat Buku Letter C / <br> Persil</th>
                                        <th class="text-center">Kelas</th>
                                        <th class="text-center">Luas Total (M<sup>2</sup>)</th>
                                        <th class="text-center">Tanggal Perolehan</th>
                                        <th class="text-center">Lokasi</th>
                                        <th class="text-center">Mutasi</th>
                                        <th class="text-center">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if (can('u'))
    @include('admin.components.modal_impor', [
        'modalId' => 'modal-impor-bumindes-tanah-kas-desa',
        'judul' => 'Impor Data Buku Tanah Kas Desa',
        'formAction' => ci_route('bumindes_tanah_kas_desa.proses_impor'),
        'formatImpor' => ci_route('bumindes_tanah_kas_desa.format_impor'),
        'petunjuk' => [
            'Kolom: <b>letter_c_persil, nama_pemilik_asal, kelas, luas, asli_milik_desa, pemerintah, provinsi, kabupaten_kota, lain_lain, sawah, tegal, kebun, tambak_kolam, tanah_kering_darat, ada_patok, tidak_ada_patok, ada_papan_nama, tidak_ada_papan_nama, lokasi, peruntukan, mutasi, keterangan</b> (urutan tidak boleh diubah).',
            'Kolom <b>letter_c_persil</b> dan <b>nama_pemilik_asal</b> wajib diisi.',
            'Kolom <b>kelas</b> diisi kode kelas tanah (mis. sesuai kode pada Ref. Kelas Persil).',
            'Baris dengan letter_c_persil yang sama dengan data yang sudah ada akan dilewati (dianggap duplikat).',
        ],
    ])
@endif

@push('scripts')
    <script>
        $(document).ready(function() {
            var TableData = $('#tabeldata').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('bumindes_tanah_kas_desa.datatables') }}",
                    data: function(req) {

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
                        data: 'asal_tanah_kas_label',
                        name: 'asal_tanah_kas_label',
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row) {
                            return data?.toUpperCase();
                        }
                    },
                    {
                        data: 'letter_c',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'kode',
                        name: 'ref_persil_kelas.kode',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'luas',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'tanggal_perolehan',
                        searchable: true,
                        orderable: true
                    },
                    {
                        data: 'lokasi',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'mutasi',
                        searchable: true,
                        orderable: false
                    },
                    {
                        data: 'keterangan',
                        searchable: true,
                        orderable: false
                    },
                ],
                order: [
                    // [1, 'asc']
                ],
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
