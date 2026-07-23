// Data contoh (mock) supaya website bisa dilihat sebelum API backend OpenSID selesai dibangun.
// Aktif jika NEXT_PUBLIC_USE_MOCK=true ATAU jika fetch ke API gagal (lihat lib/api.ts).

import type {
  ArtikelDetail,
  ArtikelRingkas,
  Komentar,
  ProfilDesa,
  StatistikPenduduk,
} from "./types";

export const mockProfil: ProfilDesa = {
  nama_desa: "Nagari Simpang",
  sebutan_desa: "nagari",
  kepala_desa: "(Wali Nagari)",
  alamat: "Kantor Wali Nagari Simpang",
  kode_pos: "-",
  kecamatan: "-",
  kabupaten: "-",
  provinsi: "Sumatera Barat",
  email: "kkn.nagarisimpang2026@gmail.com",
  telepon: "-",
  logo_url: null,
  favicon_url: null,
  tema: { warna_utama: "#14396b", judul_web: "Website Resmi Nagari Simpang" },
  sosial_media: [{ nama: "facebook", url: "#" }],
  menu: [
    { judul: "Beranda", url: "/", urut: 1 },
    { judul: "Profil", url: "/profil", urut: 2 },
    { judul: "Statistik", url: "/statistik", urut: 3 },
    { judul: "Buku Tamu", url: "/buku-tamu", urut: 4 },
  ],
};

function contohArtikel(id: number, judul: string): ArtikelRingkas {
  const slug = judul
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/(^-|-$)/g, "");
  return {
    id,
    judul,
    slug,
    url: `/artikel/2026/07/23/${slug}`,
    ringkasan:
      "Ini contoh ringkasan artikel. Konten asli akan diambil dari API OpenSID setelah endpoint /api/v1/artikel dibuat.",
    gambar_url: null,
    kategori: { id: 1, nama: "Berita Desa" },
    tanggal: "2026-07-23T10:00:00+07:00",
    penulis: "Admin",
    dilihat: 10 * id,
    headline: id === 1,
  };
}

export const mockArtikelList: ArtikelRingkas[] = [
  contohArtikel(1, "Selamat Datang di Website Baru Nagari Simpang"),
  contohArtikel(2, "Musyawarah Nagari Bahas Program Pembangunan"),
  contohArtikel(3, "Kegiatan Posyandu Rutin Bulanan"),
  contohArtikel(4, "Gotong Royong Bersih Lingkungan"),
];

export const mockArtikelDetail: ArtikelDetail = {
  ...contohArtikel(1, "Selamat Datang di Website Baru Nagari Simpang"),
  isi: "<p>Ini adalah konten contoh. Setelah endpoint <code>/api/v1/artikel/{thn}/{bln}/{hr}/{slug}</code> dibuat di OpenSID, isi artikel asli akan tampil di sini.</p>",
  agenda: null,
  dokumen: [],
  seo: {
    title: "Selamat Datang — Nagari Simpang",
    description: "Website resmi Nagari Simpang.",
    og_image: null,
  },
};

export const mockKomentar: Komentar[] = [
  {
    id: 1,
    nama: "Warga",
    isi: "Website-nya bagus dan modern!",
    tanggal: "2026-07-23T11:00:00+07:00",
    children: [
      {
        id: 2,
        nama: "Admin Nagari",
        isi: "Terima kasih atas apresiasinya.",
        tanggal: "2026-07-23T12:00:00+07:00",
        children: [],
      },
    ],
  },
];

export const mockStatistik: StatistikPenduduk = {
  judul: "Penduduk Menurut Jenis Kelamin",
  total: 5321,
  kategori: [
    { label: "Laki-laki", jumlah: 2680, persen: 50.4 },
    { label: "Perempuan", jumlah: 2641, persen: 49.6 },
  ],
};
