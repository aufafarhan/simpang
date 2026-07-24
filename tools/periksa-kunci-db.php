<?php

/**
 * Analisis kesehatan kunci tabel database OpenSID.
 *
 * Hanya MEMBACA — tidak mengubah apa pun. Gunakan untuk melihat tabel mana yang
 * kehilangan PRIMARY KEY / AUTO_INCREMENT, dan apakah ada penghalang perbaikan
 * (id duplikat / id 0 / id NULL).
 *
 * Jalankan:  php tools/periksa-kunci-db.php
 */

require __DIR__ . '/lib-db.php';

$db = koneksiDariEnv();

$tabel = [];
$r     = $db->query('SHOW TABLES');
while ($x = $r->fetch_array()) {
    $tabel[] = $x[0];
}

$perluPerbaikan = [];
$sudahAman      = 0;
$tanpaKolomId   = [];
$duplikat       = [];
$idNolNull      = [];
$bermasalah     = [];

foreach ($tabel as $t) {
    $kolom = $db->query("SHOW COLUMNS FROM `{$t}` WHERE Field = 'id'");
    if (! $kolom || $kolom->num_rows === 0) {
        $tanpaKolomId[] = $t;
        continue;
    }

    $punyaPk = false;
    $k       = $db->query("SHOW KEYS FROM `{$t}` WHERE Key_name = 'PRIMARY'");
    if ($k && $k->num_rows > 0) {
        $punyaPk = true;
    }

    $punyaAi = false;
    $a       = $db->query("SHOW COLUMNS FROM `{$t}` WHERE Extra LIKE '%auto_increment%'");
    if ($a && $a->num_rows > 0) {
        $punyaAi = true;
    }

    if ($punyaPk && $punyaAi) {
        $sudahAman++;
        continue;
    }

    $qJumlah = $db->query("SELECT COUNT(*) c FROM `{$t}`");
    if (! $qJumlah) {
        $bermasalah[$t] = $db->error;
        continue;
    }
    $jumlah = (int) $qJumlah->fetch_assoc()['c'];

    $qMaks = $db->query("SELECT COALESCE(MAX(id),0) m FROM `{$t}`");
    $maks  = $qMaks ? (int) $qMaks->fetch_assoc()['m'] : 0;

    // Penghalang 1: id duplikat
    $d = $db->query("SELECT id, COUNT(*) c FROM `{$t}` GROUP BY id HAVING c > 1 LIMIT 3");
    if ($d && $d->num_rows > 0) {
        $rows = [];
        while ($y = $d->fetch_assoc()) {
            $rows[] = "id={$y['id']} ({$y['c']}x)";
        }
        $duplikat[$t] = implode(', ', $rows);
    }

    // Penghalang 2: id 0 / NULL
    $qNol = $db->query("SELECT COUNT(*) c FROM `{$t}` WHERE id = 0 OR id IS NULL");
    $n    = $qNol ? (int) $qNol->fetch_assoc()['c'] : 0;
    if ($n > 0) {
        $idNolNull[$t] = $n;
    }

    $perluPerbaikan[$t] = ['baris' => $jumlah, 'maks_id' => $maks, 'pk' => $punyaPk, 'ai' => $punyaAi];
}

echo "=====================================================\n";
echo " ANALISIS KUNCI DATABASE — " . DB_NAME . "\n";
echo "=====================================================\n\n";
printf("Total tabel                : %d\n", count($tabel));
printf("Sudah punya PK + AUTO_INC  : %d\n", $sudahAman);
printf("Tanpa kolom `id`(dilewati) : %d\n", count($tanpaKolomId));
printf("PERLU DIPERBAIKI           : %d\n\n", count($perluPerbaikan));

if ($tanpaKolomId) {
    echo "-- Tabel tanpa kolom `id` (tidak disentuh skrip perbaikan) --\n";
    echo '   ' . implode(', ', array_slice($tanpaKolomId, 0, 15));
    echo count($tanpaKolomId) > 15 ? ", ... (+" . (count($tanpaKolomId) - 15) . " lainnya)\n\n" : "\n\n";
}

echo "=========== PENGHALANG PERBAIKAN ===========\n\n";

if ($duplikat) {
    echo "[X] " . count($duplikat) . " tabel punya id DUPLIKAT — ALTER akan GAGAL,\n";
    echo "    harus dibereskan lebih dulu:\n";
    foreach ($duplikat as $t => $v) {
        echo "      - {$t}: {$v}\n";
    }
    echo "\n";
} else {
    echo "[OK] Tidak ada id duplikat.\n\n";
}

if ($idNolNull) {
    echo "[!] " . count($idNolNull) . " tabel punya baris ber-id 0/NULL\n";
    echo "    (skrip perbaikan akan memberi nomor baru otomatis):\n";
    foreach ($idNolNull as $t => $v) {
        echo "      - {$t}: {$v} baris\n";
    }
    echo "\n";
} else {
    echo "[OK] Tidak ada id 0/NULL.\n\n";
}

echo "=========== DAFTAR TABEL YANG AKAN DIPERBAIKI ===========\n\n";
printf("%-40s %8s %10s\n", 'TABEL', 'BARIS', 'MAX(id)');
echo str_repeat('-', 60) . "\n";
foreach ($perluPerbaikan as $t => $i) {
    printf("%-40s %8d %10d\n", $t, $i['baris'], $i['maks_id']);
}

echo "\nSelesai. Tidak ada perubahan dilakukan.\n";
