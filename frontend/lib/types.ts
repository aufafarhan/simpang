// Tipe data frontend — mengikuti kontrak API OpenSID (/api/v1).
// Lihat docs/TDD-Frontend-Publik-NextJS.md

export interface ApiResponse<T> {
  data: T;
  meta?: PaginationMeta;
  message: string;
}

export interface PaginationMeta {
  page: number;
  per_page: number;
  total: number;
  total_pages: number;
}

export interface MenuItem {
  judul: string;
  url: string;
  urut: number;
  anak?: MenuItem[];
}

export interface SosialMedia {
  nama: string;
  link?: string;
  url?: string;
  icon?: string;
}

export interface PetaKoordinat {
  lat: number | null;
  lng: number | null;
  zoom: number;
}

export interface ProfilDesa {
  nama_desa: string;
  sebutan_desa: string;
  kepala_desa: string;
  alamat: string;
  kode_pos: string | number;
  kecamatan: string;
  kabupaten: string;
  provinsi: string;
  email: string;
  telepon: string | null;
  logo_url: string | null;
  favicon_url: string | null;
  peta?: PetaKoordinat;
  tema: { warna_utama: string; judul_web: string };
  sosial_media: SosialMedia[];
  /** Menu navigasi asli OpenSID (tautan bisa mengarah ke halaman PHP lama). */
  menu_atas?: MenuAtas[];
  /** Menu ringkas halaman yang sudah tersedia di frontend baru. */
  menu: MenuItem[];
}

export interface Kategori {
  id: number;
  nama: string | null;
}

/** Kategori untuk filter halaman berita (/api/v1/kategori). */
export interface KategoriRingkas {
  id: number | null;
  nama: string | null;
  slug: string;
}

export interface ArtikelRingkas {
  id: number;
  judul: string;
  slug: string;
  url: string;
  ringkasan: string;
  /** Ukuran "sedang_" (880px) */
  gambar_url: string | null;
  /** Ukuran "kecil_" (440px) — untuk kartu/daftar */
  thumbnail_url?: string | null;
  kategori: Kategori | null;
  tanggal: string;
  penulis: string;
  dilihat: number;
  headline: boolean;
}

export interface DokumenArtikel {
  id: number;
  nama: string;
  url: string;
}

export interface Agenda {
  tgl_mulai: string | null;
  tgl_selesai: string | null;
  lokasi: string | null;
}

export interface Seo {
  title: string;
  description: string;
  og_image: string | null;
}

export interface ArtikelDetail extends ArtikelRingkas {
  isi: string;
  agenda: Agenda | null;
  dokumen: DokumenArtikel[];
  seo: Seo;
}

export interface Komentar {
  id: number;
  nama: string;
  isi: string;
  tanggal: string;
  children: Komentar[];
}

// ---- Beranda (endpoint agregat /api/v1/beranda) ----

export interface SliderItem {
  judul: string | null;
  url: string | null;
  gambar_url: string | null;
}

export interface MenuAtas {
  id: string;
  nama: string;
  link: string;
  submenu?: MenuAtas[];
}

export interface JamKerjaHari {
  id: number;
  nama_hari: string;
  jam_masuk: string;
  jam_keluar: string;
  status: boolean;
  keterangan?: string;
}

export interface StatKategori {
  label: string | null;
  jumlah: number;
  laki?: number;
  perempuan?: number;
  persen: string | number | null;
}

export interface StatistikPenduduk {
  judul: string;
  total: number;
  kategori: StatKategori[];
}

export interface StatistikPengunjung {
  hari_ini: number;
  kemarin: number;
  total: number;
}

export interface ArsipItem {
  id: number | null;
  judul: string | null;
  url: string | null;
  tanggal: string | null;
  dilihat: number;
}

export interface KategoriMenu {
  id: string;
  kategori: string;
  slug: string;
  submenu?: KategoriMenu[];
}

export interface Aparatur {
  id: number | null;
  nama: string | null;
  jabatan: string | null;
  foto_url: string | null;
}

export interface GaleriItem {
  id: number | null;
  nama: string | null;
  gambar_url: string | null;
}

export interface AgendaItem {
  [key: string]: unknown;
}

export interface BerandaWidgets {
  jam_kerja: JamKerjaHari[];
  statistik_penduduk: StatistikPenduduk;
  statistik_pengunjung: StatistikPengunjung;
  arsip: { terkini: ArsipItem[]; populer: ArsipItem[]; acak: ArsipItem[] };
  menu_kategori: KategoriMenu[];
  aparatur: Aparatur[];
  galeri: GaleriItem[];
  agenda: { hari_ini: AgendaItem[]; yad: AgendaItem[]; lama: AgendaItem[] };
  sosmed: SosialMedia[];
  komentar: unknown[];
  sinergi_program: unknown;
  keuangan: unknown;
}

export interface BerandaData {
  artikel: { items: ArtikelRingkas[]; meta: PaginationMeta };
  headline: ArtikelRingkas | null;
  slider: SliderItem[];
  menu_atas: MenuAtas[];
  teks_berjalan: unknown[];
  latar_website: string | null;
  widgets: BerandaWidgets;
}
