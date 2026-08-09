/**
 * Peta menu statistik: 4 kategori utama beserta sub-kategorinya.
 *
 * `slug` mengikuti slug resmi OpenSID (StatistikPendudukEnum / KeluargaEnum /
 * JenisBantuanEnum) sehingga bisa langsung dioper ke
 * GET /api/v1/statistik/penduduk?stat=<slug>.
 *
 * `tersedia: false` menandai sub-kategori yang FITURNYA belum ada di API —
 * bukan sekadar datanya kosong. Ketiganya (Calon Pemilih, Populasi per Wilayah,
 * Vaksinasi) memang tidak terdaftar di enum statistik OpenSID; masing-masing
 * modul terpisah (DPT, wilayah, Covid-19) dan butuh endpoint sendiri.
 */

export interface SubStatistik {
  slug: string;
  label: string;
  tersedia?: boolean;
}

export interface KategoriStatistik {
  slug: string;
  label: string;
  ikon: string;
  sub: SubStatistik[];
}

export const KATEGORI_STATISTIK: KategoriStatistik[] = [
  {
    slug: "penduduk",
    label: "Statistik Penduduk",
    ikon: "groups",
    sub: [
      { slug: "rentang-umur", label: "Rentang Umur" },
      { slug: "kategori-umur", label: "Kategori Umur" },
      { slug: "pendidikan-dalam-kk", label: "Pendidikan" },
      { slug: "pekerjaan", label: "Pekerjaan" },
      { slug: "status-perkawinan", label: "Status Perkawinan" },
      { slug: "agama", label: "Agama" },
      { slug: "jenis-kelamin", label: "Jenis Kelamin" },
      { slug: "hubungan-dalam-kk", label: "Status Keluarga" },
      { slug: "status-penduduk", label: "Status Penduduk" },
      { slug: "golongan-darah", label: "Golongan Darah" },
      { slug: "penyandang-disabilitas", label: "Disabilitas" },
    ],
  },
  {
    slug: "keluarga",
    label: "Statistik Keluarga",
    ikon: "family_restroom",
    sub: [{ slug: "kelas-sosial", label: "Kelas Sosial" }],
  },
  {
    slug: "bantuan",
    label: "Statistik Bantuan",
    ikon: "volunteer_activism",
    sub: [
      { slug: "bantuan-penduduk", label: "Penerima Bantuan Penduduk" },
      { slug: "bantuan-keluarga", label: "Penerima Bantuan Keluarga" },
    ],
  },
  {
    slug: "lainnya",
    label: "Statistik Lainnya",
    ikon: "insights",
    sub: [
      { slug: "calon-pemilih", label: "Calon Pemilih", tersedia: false },
      { slug: "populasi", label: "Populasi per Wilayah", tersedia: false },
      { slug: "vaksinasi", label: "Vaksinasi", tersedia: false },
    ],
  },
];

export function cariKategori(slug?: string): KategoriStatistik {
  return KATEGORI_STATISTIK.find((k) => k.slug === slug) ?? KATEGORI_STATISTIK[0];
}

export function cariSub(kategori: KategoriStatistik, slug?: string): SubStatistik {
  return kategori.sub.find((s) => s.slug === slug) ?? kategori.sub[0];
}
