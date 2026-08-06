@php
    $modalId = $modalId ?? 'modal-impor';
@endphp
<div class="modal fade" id="{{ $modalId }}">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{ $judul ?? 'Impor Data' }}</h4>
            </div>
            {!! form_open($formAction, 'class="form-horizontal" id="form-' . $modalId . '" enctype="multipart/form-data"') !!}
            <div class="modal-body">
                <div class="form-group">
                    <label>Petunjuk:</label>
                    <ol>
                        @foreach ($petunjuk ?? ['Pastikan format data sudah sesuai dengan Contoh Format Impor.', 'Simpan berkas sebagai .xlsx (Excel 2007 ke atas).'] as $item)
                            <li>{!! $item !!}</li>
                        @endforeach
                    </ol>
                </div>
                <div class="form-group">
                    <label for="file-{{ $modalId }}" class="control-label">Berkas .xlsx untuk diimpor:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="file_path-{{ $modalId }}" readonly>
                        <input type="file" class="hidden" id="file-{{ $modalId }}" name="userfile" accept=".xlsx" required>
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-info btn-flat" id="file_browser-{{ $modalId }}"><i class="fa fa-search"></i> Cari</button>
                        </span>
                    </div>
                    <br>
                    <a href="{{ $formatImpor }}" class="btn btn-social btn-flat bg-purple btn-sm"><i class="fa fa-file-excel-o"></i> Contoh Format Impor</a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-social btn-flat btn-danger btn-sm pull-left" data-dismiss="modal"><i class="fa fa-times"></i> Batal</button>
                <button type="submit" class="btn btn-social btn-flat btn-info btn-sm"><i class="fa fa-check"></i> Impor</button>
            </div>
            {!! form_close() !!}
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#file_browser-{{ $modalId }}').click(function(e) {
                e.preventDefault();
                $('#file-{{ $modalId }}').click();
            });
            $('#file-{{ $modalId }}').change(function() {
                $('#file_path-{{ $modalId }}').val($(this).val());
            });
        });
    </script>
@endpush
