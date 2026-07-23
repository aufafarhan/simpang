<?php
// ----------------------------------------------------------------------------
// Konfigurasi aplikasi dalam berkas ini merupakan setting konfigurasi tambahan
// SID. Letakkan setting konfigurasi ini di desa/config/config.php.
// ----------------------------------------------------------------------------

// Uncomment jika situs ini untuk demo. Pada demo, user admin tidak bisa dihapus
// dan username/password tidak bisa diubah

// $config['demo_mode'] = true;

// Setting ini untuk menentukan user yang dipercaya. User dengan id di setting ini
// dapat membuat artikel berisi video yang aktif ditampilkan di Web.
// Misalnya, ganti dengan id = 1 jika ingin membuat pengguna admin sebagai pengguna terpecaya.
$config['user_admin'] = 1;
$config['DeNava'] = '4F40BE06E7AB';
$config['chats'] = true; // Munculkan icon chat di halaman website. Jika ingin sesuaikan jam tampil, edit file: partials/home/chats.php dibaris 45 sampai 51
$config['kode_kota'] = '0306'; // Kode Kota Jadwal Sholat di https://www.ariandi.net/kode
$config['random_doa'] = true; // Tampilkan Random Do'a pada halaman website
$config['style'] = 'gogreen'; // Tersedia 3 pilihan warna, ubah dengan menulis tourism, gogreen, atau classic
$config['hide_banner_laporan'] = true; // Sembunyikan Info Perkembangan Penduduk di halaman website

// config email
$config['protocol']       = 'smtp';  // mail	mail, sendmail, or smtp	The mail sending protocol.
$config['smtp_host']      = '';      // SMTP Server Address.
$config['smtp_user']      = '';      // SMTP Username.
$config['smtp_pass']      = '';      // SMTP Password.
$config['smtp_port']      = '';      // SMTP Port."