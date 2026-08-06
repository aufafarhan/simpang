@php
    $pesan_impor = session($sessionKey ?? 'pesan_impor');
@endphp
@if ($pesan_impor)
    <div class="box box-info">
        <div class="box-header with-border">
            <h3 class="box-title">Ringkasan Hasil Impor</h3>
        </div>
        <div class="box-body">
            <dl class="dl-horizontal">
                <dt>Total Berhasil</dt>
                <dd>{{ $pesan_impor['sukses'] }}</dd>
                <dt>Total Gagal</dt>
                <dd>{{ $pesan_impor['gagal'] }}</dd>
                <dt>Total Duplikat</dt>
                <dd>{{ $pesan_impor['ganda'] }}</dd>
            </dl>
            @if ($pesan_impor['pesan'])
                <dl class="dl-horizontal">
                    <dt>Rincian Pesan</dt>
                    <dd>{!! $pesan_impor['pesan'] !!}</dd>
                </dl>
            @endif
        </div>
    </div>
@endif
