@extends('admin.layouts.index')

@section('title')
    <h1>
        Impor Data Keluarga
    </h1>
@endsection

@section('breadcrumb')
    <li class="active">Impor Data Keluarga</li>
@endsection

@section('content')

    @include('admin.layouts.components.notifikasi')
    @include('admin.components.impor_ringkasan')

    <div class="box box-info">
        <div class="box-header with-border">
            <a href="{{ ci_route('keluarga') }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block" title="Kembali Ke Data Keluarga"><i class="fa fa-arrow-circle-o-left"></i> Kembali Ke Data Keluarga</a>
        </div>
        <div class="box-body">
            {!! form_open($form_action, 'class="form-horizontal" id="impor" enctype="multipart/form-data"') !!}
            <div class="row">
                <div class="col-sm-12">
                    <p><b>Fitur ini dipakai untuk menambah Kartu Keluarga (KK) baru beserta kepala keluarganya secara masal.</b></p>
                    <p>Nomor KK yang sudah terdaftar akan dilewati (tidak menimpa data KK yang sudah ada). Kolom dan aturan pengisian sama persis dengan format impor Data Penduduk.</p>
                    <ol>
                        <li>Pastikan format data yang akan diimpor sudah sesuai dengan aturan impor data.</li>
                        <li>Simpan (Save) berkas Excel sebagai .xlsx.</li>
                        <li>Pastikan format excel ber-ekstensi .xlsx (format Excel versi 2007 ke atas).</li>
                        <li>
                            Data yang dibutuhkan untuk impor mengikuti urutan format dan aturan data pada tautan di bawah ini:
                            <div class="timeline-footer col-sm-12">
                                <a href="{{ $formatImpor }}" class="btn btn-social btn-info btn-sm visible-xs-block visible-sm-inline-block visible-md-inline-block visible-lg-inline-block margin"><i class="fa fa-download"></i> Aturan dan Contoh Format Data</a>
                            </div>
                        </li>
                    </ol>
                    <p>Batas maksimal pengunggahan berkas <strong>{{ max_upload() }} MB.</strong></p>
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <td style="padding-top:20px;padding-bottom:10px;">
                                    <div class="form-group">
                                        <label for="file_keluarga" class="col-md-2 col-lg-3 control-label">Pilih File .xlsx:</label>
                                        <div class="col-sm-12 col-md-5 col-lg-5">
                                            <div class="input-group input-group-sm">
                                                <input type="text" class="form-control" id="file_path_keluarga" name="userfile">
                                                <input type="file" class="hidden" id="file_keluarga" name="userfile"
                                                    accept="application/octet-stream, application/vnd.ms-excel, application/x-csv, text/x-csv, text/csv, application/csv, application/excel, application/vnd.msexcel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel.sheet.macroenabled.12"
                                                />
                                                <span class="input-group-btn">
                                                    <button type="button" class="btn btn-info" id="file_browser_keluarga"><i class="fa fa-search"></i> Browse</button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-5 col-lg-4">
                                            <a href="#" class="btn btn-block btn-success btn-sm" id="btn-impor-keluarga"><i class="fa fa-spin fa-refresh"></i> Impor Data Keluarga</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            {!! form_close() !!}

            @include('admin.penduduk.proses')
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $('document').ready(function() {
            $('#file_browser_keluarga').click(function(e) {
                e.preventDefault();
                $('#file_keluarga').click();
            });

            $('#file_keluarga').change(function() {
                $('#file_path_keluarga').val($(this).val());
            });

            $('#file_path_keluarga').click(function() {
                $('#file_browser_keluarga').click();
            });

            $('#btn-impor-keluarga').click(function(event) {
                event.preventDefault;
                Swal.fire({
                    title: 'Peringatan',
                    icon: 'warning',
                    html: '<b>Fitur impor ini hanya menambah KK baru beserta kepala keluarganya. Nomor KK yang sudah ada akan dilewati.</b>',
                    showCancelButton: true,
                    confirmButtonText: 'Lanjutkan',
                    denyButtonText: `Batal`,
                }).then((result) => {
                    if (result.isConfirmed) {
                        refreshFormCsrf();
                        document.getElementById('impor').submit();
                        $('#loading').modal('show');
                    }
                })
            });
        });
    </script>
@endpush
