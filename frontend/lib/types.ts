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

export interface AlbumRingkas {
  id: number;
  nama: string;
  gambar_url: string | null;
  jumlah_foto: number;
  tgl_upload: string | null;
  slug: string;
}

export interface FotoDetail {
  id: number;
  nama: string;
  gambar_url: string | null;
  jenis: "foto" | "video";
  link?: string | null;
  tgl_upload?: string | null;
}

export interface AlbumDetail extends AlbumRingkas {
  deskripsi?: string;
  foto: FotoDetail[];
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

export interface AparaturDetail {
  id: number;
  nama: string;
  jabatan: string;
  nip_niap: string;
  foto_url: string | null;
  masa_jabatan?: string | null;
  deskripsi?: string | null;
  email?: string | null;
  urut?: number;
}

export interface PemerintahNagariData {
  wali_nagari: AparaturDetail | null;
  perangkat: AparaturDetail[];
}

export interface SotkNode {
  id: number;
  nama: string;
  jabatan: string;
  foto_url: string | null;
  kriteria?: string;
  bawahan?: SotkNode[];
}

export interface FotoDokumentasiPembangunan {
  id: number;
  gambar_url: string | null;
  persentase: number;
  keterangan: string;
  tgl_upload?: string | null;
}

export interface ProyekPembangunan {
  id: number;
  judul: string;
  lokasi: string;
  anggaran: number;
  sumber_dana: string;
  tahun_anggaran: number;
  pelaksana: string;
  progres_persen: number;
  status: "Selesai" | "Dalam Proses" | "Perencanaan";
  foto_cover: string | null;
  deskripsi: string;
  foto_dokumentasi?: FotoDokumentasiPembangunan[];
}

export interface ProdukLapak {
  id: number;
  nama: string;
  kategori: string;
  harga: number;
  satuan: string | null;
  deskripsi: string;
  foto_url: string | null;
  pelapak: string;
  telepon: string | null;
}

// ---- Status Desa: IDM & SDGs ----
// Sumber: API Kemendesa lewat helper OpenSID (di-cache setahun).

/** Pihak yang dapat melaksanakan kegiatan perbaikan indikator. */
export interface PelaksanaIdm {
  pusat: string | null;
  provinsi: string | null;
  kabupaten: string | null;
  desa: string | null;
  csr: string | null;
  lainnya: string | null;
}

export interface IndikatorIdm {
  no: number;
  indikator: string;
  skor: number | null;
  keterangan: string;
  kegiatan: string;
  nilai: string | null;
  pelaksana: PelaksanaIdm;
}

export interface StatusIdm {
  tahun: number;
  /** Hanya tahun yang benar-benar punya data (cache error dikecualikan). */
  tahun_tersedia: number[];
  skor: number;
  status: string | null;
  target_status: string | null;
  skor_minimal: number | null;
  /** Selisih skor yang masih perlu dicapai untuk naik status. */
  penambahan: number | null;
  wilayah: {
    desa: string | null;
    kecamatan: string | null;
    kabupaten: string | null;
    provinsi: string | null;
  };
  indikator: IndikatorIdm[];
}

export interface TujuanSdgs {
  nomor: number;
  judul: string;
  skor: number | null;
  gambar_url: string | null;
}

export interface StatusSdgs {
  skor_rata_rata: number | null;
  jumlah_tujuan: number;
  tujuan: TujuanSdgs[];
}
