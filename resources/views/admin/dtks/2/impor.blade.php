@include('admin.components.impor_ringkasan')

<p class="text-muted">
    Impor ini mencakup <b>Bagian I-III</b> (Keterangan Tempat, Keterangan Petugas, Keterangan Perumahan) dan <b>Bagian V-VI</b> (Bantuan Sosial &amp; Aset Rumah Tangga, Catatan) untuk rumah tangga yang sudah terdaftar sebagai RTM.
    Data anggota keluarga (<b>Bagian IV</b>) dan foto (<b>Bagian VII</b>) tetap harus diisi manual lewat formulir — Bagian IV levelnya per-anggota keluarga (bukan per-rumah tangga) dan sebagian datanya sudah otomatis tersinkron dari data Penduduk.
</p>

<a href="{{ $formatImpor }}" class="btn btn-social btn-info btn-sm"><i class="fa fa-download"></i> Download Template Impor</a>

{!! form_open(ci_route('dtks.prosesImpor'), 'class="form-horizontal" id="form-impor-dtks" enctype="multipart/form-data" style="margin-top:15px;"') !!}
<div class="form-group">
    <label for="file-impor-dtks">Berkas .xlsx:</label>
    <input type="file" class="form-control" id="file-impor-dtks" name="userfile" accept=".xlsx" required>
</div>
<button type="submit" class="btn btn-sm btn-social btn-primary"><i class="fa fa-upload"></i> Impor</button>
{!! form_close() !!}
