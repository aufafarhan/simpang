@include('admin.components.impor_ringkasan')

<p class="text-muted">
    Impor ini hanya mencakup <b>Bagian I-III</b> (Keterangan Tempat, Keterangan Petugas, dan Keterangan Perumahan) untuk rumah tangga yang sudah terdaftar sebagai RTM.
    Data anggota keluarga (Bagian IV-V) tetap harus diisi manual lewat formulir.
</p>

<a href="{{ $formatImpor }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-download"></i> Download Template Impor</a>

{!! form_open(ci_route('dtks.prosesImpor'), 'class="form-horizontal" id="form-impor-dtks" enctype="multipart/form-data" style="margin-top:15px;"') !!}
<div class="form-group">
    <label for="file-impor-dtks">Berkas .xlsx:</label>
    <input type="file" class="form-control" id="file-impor-dtks" name="userfile" accept=".xlsx" required>
</div>
<button type="submit" class="btn btn-sm btn-social btn-primary"><i class="fa fa-upload"></i> Impor</button>
{!! form_close() !!}
