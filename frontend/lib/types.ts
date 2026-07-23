// Tipe data frontend — mengikuti kontrak data di docs/TDD-Frontend-Publik-NextJS.md (§4)

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
  url: string;
}

export interface ProfilDesa {
  nama_desa: string;
  sebutan_desa: string;
  kepala_desa: string;
  alamat: string;
  kode_pos: string;
  kecamatan: string;
  kabupaten: string;
  provinsi: string;
  email: string;
  telepon: string;
  logo_url: string | null;
  favicon_url: string | null;
  tema: { warna_utama: string; judul_web: string };
  sosial_media: SosialMedia[];
  menu: MenuItem[];
}

export interface Kategori {
  id: number;
  nama: string;
}

export interface ArtikelRingkas {
  id: number;
  judul: string;
  slug: string;
  url: string;
  ringkasan: string;
  gambar_url: string | null;
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

export interface StatistikKategori {
  label: string;
  jumlah: number;
  persen: number;
}

export interface StatistikPenduduk {
  judul: string;
  total: number;
  kategori: StatistikKategori[];
}
