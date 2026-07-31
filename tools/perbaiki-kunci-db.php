<?php

/**
 * Perbaiki PRIMARY KEY + AUTO_INCREMENT pada tabel OpenSID.
 *
 * LATAR BELAKANG
 * Database ini di-impor dari dump yang kehilangan definisi kunci, sehingga hampir
 * semua tabel tidak punya PRIMARY KEY maupun AUTO_INCREMENT. Akibatnya setiap baris
 * BARU (artikel, penduduk, komentar, pengaduan, ...) tersimpan dengan id = 0.
 *
 * APA YANG DILAKUKAN
 * Untuk setiap tabel yang punya kolom `id` tapi belum punya PK/AUTO_INCREMENT:
 *     ALTER TABLE `x` MODIFY `id` <tipe asli> NOT NULL AUTO_INCREMENT,
 *                     ADD PRIMARY KEY (`id`), AUTO_INCREMENT = <MAX(id)+1>;
 * Tipe kolom asli dipertahankan (int / bigint / unsigned, dst).
 *
 * PENGAMAN
 *  - Default MODE UJI COBA: hanya menampilkan SQL, tidak mengubah apa pun.
 *  - Mode terapkan WAJIB membuat backup .sql lebih dulu; kalau backup gagal, batal.
 *  - Tabel dengan id duplikat / id 0 / id NULL DILEWATI (ALTER pasti gagal).
 *  - Tabel tanpa kolom `id` tidak disentuh.
 *  - Kegagalan per tabel dicatat, proses lanjut, ringkasan di akhir.
 *
 * PEMAKAIAN
 *   php tools/perbaiki-kunci-db.php                → uji coba (aman, default)
 *   php tools/perbaiki-kunci-db.php --backup-saja  → hanya buat backup
 *   php tools/perbaiki-kunci-db.php --jalankan     → backup lalu terapkan perubahan
 *
 * CATATAN: DDL MySQL tidak bisa di-rollback. Backup adalah jaring pengaman satu-satunya.
 */

require __DIR__ . '/lib-db.php';

$argumen    = $argv ?? [];
$jalankan   = in_array('--jalankan', $argumen, true);
$backupSaja = in_array('--backup-saja', $argumen, true);

$db = koneksiDariEnv();

/** Cari mysqldump di lokasi umum (Laragon/XAMPP) atau PATH. */
function cariMysqldump(): ?string
{
    $kandidat = glob('C:/laragon/bin/mysql/*/bin/mysqldump.exe') ?: [];
    $kandidat = array_merge($kandidat, [
        'C:/xampp/mysql/bin/mysqldump.exe',
        '/usr/bin/mysqldump',
        '/usr/local/bin/mysqldump',
    ]);

    foreach ($kandidat as $k) {
        if (is_file($k)) {
            return $k;
        }
    }

    return null;
}

/** Backup seluruh database ke berkas .sql. Mengembalikan path, atau null bila gagal. */
function buatBackup(): ?string
{
    $dump = cariMysqldump();
    if ($dump === null) {
        fwrite(STDERR, "GAGAL: mysqldump tidak ditemukan. Backup manual dulu sebelum melanjutkan.\n");

        return null;
    }

    $dir = dirname(__DIR__) . '/backup_db';
    if (! is_dir($dir) && ! mkdir($dir, 0775, true)) {
        fwrite(STDERR, "GAGAL: tidak bisa membuat folder {$dir}\n");

        return null;
    }

    $berkas = $dir . '/' . DB_NAME . '_' . date('Ymd_His') . '.sql';

    $perintah = sprintf(
        '"%s" --host=%s --port=%d --user=%s %s --single-transaction --routines --events --result-file=%s %s',
        $dump,
        escapeshellarg(DB_HOST),
        DB_PORT,
        escapeshellarg(DB_USER),
        DB_PASS !== '' ? '--password=' . escapeshellarg(DB_PASS) : '',
        escapeshellarg($berkas),
        escapeshellarg(DB_NAME)
    );

    echo "  Menjalankan mysqldump...\n";
    exec($perintah . ' 2>&1', $keluaran, $kode);

    if ($kode !== 0 || ! is_file($berkas) || filesize($berkas) < 1024) {
        fwrite(STDERR, "GAGAL membuat backup:\n  " . implode("\n  ", $keluaran) . "\n");

        return null;
    }

    printf("  Backup dibuat: %s (%.2f MB)\n", $berkas, filesize($berkas) / 1048576);

    return $berkas;
}

// ---------------------------------------------------------------- kumpulkan data

// Ambil TABEL ASLI saja — VIEW sengaja dikecualikan karena tidak bisa
// (dan tidak perlu) diberi PRIMARY KEY / AUTO_INCREMENT.
$tabel = [];
$stmt  = $db->prepare(
    "SELECT TABLE_NAME FROM information_schema.TABLES
     WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME"
);
$nama = DB_NAME;
$stmt->bind_param('s', $nama);
$stmt->execute();
$res = $stmt->get_result();
while ($x = $res->fetch_assoc()) {
    $tabel[] = $x['TABLE_NAME'];
}
$stmt->close();

$target    = [];   // tabel => ['sql' => ..., 'baris' => ..., 'mulai' => ...]
$dilewati  = [];   // tabel => alasan

foreach ($tabel as $t) {
    // Harus punya kolom `id`
    $kol = $db->query("SHOW COLUMNS FROM `{$t}` WHERE Field = 'id'");
    if (! $kol || $kol->num_rows === 0) {
        continue; // tanpa kolom id — bukan urusan skrip ini
    }
    $infoKolom = $kol->fetch_assoc();

    // Lewati bila sudah punya PK dan AUTO_INCREMENT
    $pk = $db->query("SHOW KEYS FROM `{$t}` WHERE Key_name = 'PRIMARY'");
    $ai = $db->query("SHOW COLUMNS FROM `{$t}` WHERE Extra LIKE '%auto_increment%'");
    if ($pk && $pk->num_rows > 0 && $ai && $ai->num_rows > 0) {
        continue;
    }

    // Penghalang: id duplikat
    $d = $db->query("SELECT id FROM `{$t}` GROUP BY id HAVING COUNT(*) > 1 LIMIT 1");
    if ($d && $d->num_rows > 0) {
        $dilewati[$t] = 'ada id duplikat';
        continue;
    }

    // Penghalang: id 0 / NULL
    $q = $db->query("SELECT COUNT(*) c FROM `{$t}` WHERE id = 0 OR id IS NULL");
    if ($q && (int) $q->fetch_assoc()['c'] > 0) {
        $dilewati[$t] = 'ada baris ber-id 0/NULL';
        continue;
    }

    $qJml = $db->query("SELECT COUNT(*) c FROM `{$t}`");
    if (! $qJml) {
        $dilewati[$t] = 'tabel tidak terbaca';
        continue;
    }
    $baris = (int) $qJml->fetch_assoc()['c'];

    $qMax  = $db->query("SELECT COALESCE(MAX(id),0) m FROM `{$t}`");
    $maks  = $qMax ? (int) $qMax->fetch_assoc()['m'] : 0;
    $mulai = $maks + 1;

    // Pertahankan tipe kolom asli (int/bigint/unsigned/…)
    $tipe = $infoKolom['Type'] ?: 'int';

    $target[$t] = [
        'sql' => sprintf(
            'ALTER TABLE `%s` MODIFY `id` %s NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`), AUTO_INCREMENT = %d',
            $t,
            $tipe,
            $mulai
        ),
        'baris' => $baris,
        'mulai' => $mulai,
    ];
}

// ---------------------------------------------------------------- tampilkan

echo "==========================================================\n";
echo " PERBAIKAN PRIMARY KEY + AUTO_INCREMENT — " . DB_NAME . "\n";
echo "==========================================================\n\n";
printf("Tabel yang akan diperbaiki : %d\n", count($target));
printf("Tabel dilewati (bermasalah): %d\n\n", count($dilewati));

if ($dilewati) {
    echo "-- DILEWATI (perlu dibereskan manual) --\n";
    foreach ($dilewati as $t => $alasan) {
        echo "   ! {$t}: {$alasan}\n";
    }
    echo "\n";
}

if (! $target) {
    echo "Tidak ada yang perlu diperbaiki. Selesai.\n";
    exit(0);
}

// ---------------------------------------------------------------- backup saja

if ($backupSaja) {
    echo "== MEMBUAT BACKUP ==\n";
    exit(buatBackup() === null ? 1 : 0);
}

// ---------------------------------------------------------------- uji coba

if (! $jalankan) {
    echo "== MODE UJI COBA (tidak ada perubahan) ==\n\n";
    $n = 0;
    foreach ($target as $t => $i) {
        if ($n++ < 10) {
            echo "  {$i['sql']};\n";
        }
    }
    if (count($target) > 10) {
        printf("  ... dan %d perintah lain yang serupa\n", count($target) - 10);
    }
    echo "\nJalankan dengan --jalankan untuk menerapkan (backup dibuat otomatis lebih dulu).\n";
    exit(0);
}

// ---------------------------------------------------------------- terapkan

echo "== LANGKAH 1/2: BACKUP DATABASE ==\n";
$berkasBackup = buatBackup();
if ($berkasBackup === null) {
    fwrite(STDERR, "\nDIBATALKAN: perubahan TIDAK dijalankan karena backup gagal.\n");
    exit(1);
}

echo "\n== LANGKAH 2/2: MENERAPKAN PERUBAHAN ==\n";

$berhasil = 0;
$gagal    = [];

// Matikan pemeriksaan foreign key selama perubahan struktur
$db->query('SET FOREIGN_KEY_CHECKS = 0');

foreach ($target as $t => $i) {
    if ($db->query($i['sql'])) {
        $berhasil++;
        printf("  [OK]    %-42s (mulai dari id %d)\n", $t, $i['mulai']);
    } else {
        $gagal[$t] = $db->error;
        printf("  [GAGAL] %-42s %s\n", $t, $db->error);
    }
}

$db->query('SET FOREIGN_KEY_CHECKS = 1');

echo "\n==================== RINGKASAN ====================\n";
printf("Berhasil : %d tabel\n", $berhasil);
printf("Gagal    : %d tabel\n", count($gagal));
printf("Backup   : %s\n", $berkasBackup);

if ($gagal) {
    echo "\nTabel yang gagal:\n";
    foreach ($gagal as $t => $e) {
        echo "   - {$t}: {$e}\n";
    }
    echo "\nPulihkan bila perlu:\n";
    echo "   mysql -u " . DB_USER . " -p " . DB_NAME . " < \"{$berkasBackup}\"\n";
}

echo "\nSelesai. Verifikasi dengan: php tools/periksa-kunci-db.php\n";
