// Data contoh (mock) supaya website bisa dilihat sebelum API backend OpenSID selesai dibangun.
// Aktif jika NEXT_PUBLIC_USE_MOCK=true ATAU jika fetch ke API gagal (lihat lib/api.ts).

import type {
  AlbumDetail,
  AlbumRingkas,
  AparaturDetail,
  ArtikelDetail,
  ArtikelRingkas,
  Komentar,
  PemerintahNagariData,
  ProfilDesa,
  ProyekPembangunan,
  SotkNode,
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

export const mockAlbumList: AlbumRingkas[] = [
  {
    id: 1,
    nama: "Panen Raya 2024",
    gambar_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBdUNI6bkwhfNDR7czl6-4r2tSEdaqEN6MHJp7sNTxIEe4Goh9uGvbHp-Iem6xB2sb_ky3Wb9WVAq6MRUh0IreovN2NXHJbqiuw-4XqD3eVKLM_EPfEgCCCc06KzOrbDMeYA9UqVyhEe0mrgpHM47NzenYtHtpx7nkul5THoy_xUfxIM5f37cWf1uYSBOtJGRY4cbYHWDYDnpgwZkHNZyB1h_GLk10C3O8v0GO5dudeDy1iz2FqXFsRdbMAcwnHFBaYBTwsL5GBlxbZ",
    jumlah_foto: 12,
    tgl_upload: "2024-10-15T08:00:00Z",
    slug: "1",
  },
  {
    id: 2,
    nama: "Pembangunan Balai Adat",
    gambar_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuCsZtes2IoE5mJPGurKE2NCaXMuCTNdTZS6mWpqaq4qiI6CU5HCA6jxRasK5Kq5-9_V06HyX4MKRd6apm7eSIKZmOzhOrodF_-kZDNqGTrlCQMTLeMTA_CYcDdSLM-p_GRDgkwWz7iNbfcbnPiNYIVmpveGTs3hqI5s21N8bGcpo8ohMhzUEGEN4u4_DldFlt0hW8z7nsIWW76xectmMunGoqOk9oKbb-FBZqcKbqTd2pJjruu8ThJNGVSxH3yrFFef_K79aAJAW3vv",
    jumlah_foto: 24,
    tgl_upload: "2024-08-20T08:00:00Z",
    slug: "2",
  },
  {
    id: 3,
    nama: "Pesona Alam Bantaran Sungai",
    gambar_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBdu3HBko2Y96ran8XvqYa_fVBpHr-VgkkDH3xnNJk8GBw465tBDX4-d7MfdSKq8a5_xpRoapegBpEYOgMBTo86wRlMRKHWe-MMsq9m9HDuDh_YtaybEzzfnEobL31X_ok8CIUrugxSfKZ8rqLMVfByFyBsv7A3iIhUiL_R1IsM84flEV0RLKvOCYVDU0UJSch_PuyLRgEGxRbkZY6dOnFo0CSyE8oCpFXfYtVeiA5wrtVdnKtG3-WBlM_lXJrKzRHht4FMCcxZ5OzH",
    jumlah_foto: 8,
    tgl_upload: "2024-07-10T08:00:00Z",
    slug: "3",
  },
  {
    id: 4,
    nama: "Pelatihan Tenun Songket",
    gambar_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBf-1HLW717saMqpH9z5cUjxdmuZ99X0BEDv4RlpFuqvj4H9EFBE4IWGH5dKm57OhNaVj5iP_j_4P4s1NCZuG1EJZudzk_RJirvRQiOQb-owPb76_P9neNEKH317FaU1K5WwRZBd7Ff77_y_4QM6_Hal2VLNW6gawEcWyR_WZ8EjYIEJLlbrCBJaz4C1FZqYUfvR6GmLv7CG-Y9BUlG82Dk9Z56On7dmGnDDMRO-KaKpwU-FaYgxdjeyokr5n6pHaOEQVb_OPg6pWFL",
    jumlah_foto: 15,
    tgl_upload: "2024-06-05T08:00:00Z",
    slug: "4",
  },
];

export const mockAlbumDetail: AlbumDetail = {
  ...mockAlbumList[0],
  nama: "Keindahan Alam & Harmoni Budaya",
  deskripsi:
    "Kumpulan potret yang menangkap esensi kehidupan komunal, kekayaan arsitektur tradisional, dan hamparan alam agraris yang subur di Nagari Simpang. Jelajahi sudut pandang yang mencerminkan filosofi 'Alam Takambang Jadi Guru'.",
  foto: [
    {
      id: 101,
      nama: "Hamparan Sawah Terasering",
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuAnhJ6X3IVJXPBfirexq3VF-Ekfg0ETf8Tf6xh7nQ7QbOSr31IiVVZOCpSsGOlPb3JJsKaLTi4PyryR3OmMiiWvdByX9wndQIZ8ea-k3gXJzZgNlF1fo0_H9FYCsZ6jczcIc4dBxBiYEw2ZRrRgo8kW9SsHUQoNpSkl1QD8FOxD1A_tyvjHl5y8-dscujuigDLeHoE6op_4_T5trAX__Fp1YSQkh_473WbMK6JrxwWe1y5p-EPDziTolxm3LBXsOF8zjnpMjv9tduJm",
      jenis: "foto",
      tgl_upload: "2024-10-15T08:00:00Z",
    },
    {
      id: 102,
      nama: "Kesenian Tari Piring",
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuCg4obei21UNF9h8wo-dz2OTjlht-nAvTQVmReR1uZSRN267XEnb2xUhPBFvMV_bP2Wtm3prdh4a30ebEYPupIg3Wt05x40q2u5OXh9HnqNzERqFkJ9lXxrG6HEyIPAqkAxkhZ_twRjFAXNbqjA1MaXeCAYHnIsb5xiq6l5AKbt0a4N-YKGRSL1NNSl-nYg9UXf7MZT-ZJJ32DqpbjL9GalEUxNrow9AvdFEUIK5VEhTKLZ2tvDyMGtkBdxSjsO6i8t6hJezhxVnLYi",
      jenis: "foto",
      tgl_upload: "2024-10-15T08:05:00Z",
    },
    {
      id: 103,
      nama: "Dokumenter Arsitektur Adat",
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuCJxappXtJxfHke4Qr39Nhy-ibwPFSDQFqeY0Eup1Lfo1FoJN2v3lhySU_jMqWTfNgdP9XIanNn0nVJecD4EmxPxGeZLkaGNL7CDnDBhprLLNenU3cuhs0yaLcGj-zv9nE5LykquM_QLUb_RDCI7DCvRZ4qBrHSeAocFSc-5WszCqSFupKqsxikhNeLo4aqfWsi8j5xAInHzhFAmCSSuZ5otKxKxrNN8bv8AwyXZRf0KwA8QqN4-so8UqsO-umeGwRVsBilhzrrBxjK",
      jenis: "video",
      link: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
      tgl_upload: "2024-10-15T08:10:00Z",
    },
    {
      id: 104,
      nama: "Pengrajin Tenun Lokal",
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuA-OFgdIiEDieDFVBsJyrfGKFAtqNBS89DG1K4pzl2Zrex1TVtUZq1NOp3-7DtE5ypcEsyMDkSrr_xlExbaEp6C1NvAcMOxLxSvF55g_xbs9Dl-e1mqZB9sMvp874f37uxjDCo7mal6Bk1lWP06copBhaGYAjPMklAU-26IWOX22IIOeC6SUp_2DKecIWWrKq11qgi5IXh0SWoqB_gnuIwFoSjp4vx_NPBY6uuEH0xWI4v0zAuZZFdXQn9nLKlmoBYhxpGWQGnEBiw1",
      jenis: "foto",
      tgl_upload: "2024-10-15T08:15:00Z",
    },
    {
      id: 105,
      nama: "Semangat Gotong Royong",
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuDjg2_V87DeDr5xlRDTyuneimr7Rf96Ub4ngwHq0cEKH9J5joJ0XCk3i9rPV11UDD3VBHV6cHic8_z95muJwlt7Kz6Zpxsz5lKPNKpBFXKuf_M6KvDjGZm-dKWCZGMJHy9uklq-Hb3EgWz-tJWnqHQxoPPKZ4WpIbmlE5ZFqDRhN-UYi_iZrtb_mgUfocOq09vSdIKPuvn7gz9TvPZt8_TRrbIES0DHprck5fBxlYfBU3d03VLdkdR2ATVZVqjltlNjKhwGxlNdqJFm",
      jenis: "foto",
      tgl_upload: "2024-10-15T08:20:00Z",
    },
    {
      id: 106,
      nama: "Kekayaan Kuliner Nagari",
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuBQUJ4NXBrmcjfbQVKkSWqky0v_xEkNM4dKc8X8L53PLDylssWUvKPCkXDf-0jh24xSjPWUMiMDsXHEDc9BwM3anCtV3dD2pOBuCHd8db0tAVsyTCrUNAFg2zrf9Tb4jIliOyhnEpRfBWRn2_yN6jArjOYiADPU5J3PMcmjqm6SHMFpHWDtIFoBsOh9ANg6Xp1PALCcsPEZCt8KLCYEddNgDfabm2OCXop7MTV1h0oqhNYlWsjwpyGJAHemI6QukFeKMQC8l_yXb_e3",
      jenis: "foto",
      tgl_upload: "2024-10-15T08:25:00Z",
    },
  ],
};

export const mockWaliNagari: AparaturDetail = {
  id: 1,
  nama: "Bpk. Sudirman, S.Sos",
  jabatan: "Wali Nagari (Kepala Desa)",
  nip_niap: "19750812 200501 1 003",
  foto_url:
    "https://lh3.googleusercontent.com/aida-public/AB6AXuDIig5c28yDzlWZhXLLoKBnFyqO9AmUehYYREHpBQ495I4xslvUQgwZbg1zqvl30pe0CB7nnp714hr00K2Uq9suEJMCAR2HM1fzEkRcz8s5XdCjOq37f00u7fvjUEwSvLMqrrYYzJl8iTVUMFQn6C7Wy8zeRkFD0hwLxShOjL7OnG1UEfgHAkJhmVxf8fW3H0DsW0k08rde_4ABpyQGH7fOino9_NGS_U59X3ujB45O7h2Pv5LHsf7zyTvB2PHbYNy3Zm3S7FUjh-Mb",
  masa_jabatan: "2021 - 2027",
  deskripsi:
    "Memimpin penyelenggaraan pemerintahan desa, pelaksanaan pembangunan desa, pembinaan kemasyarakatan desa, dan pemberdayaan masyarakat desa berlandaskan Adat Basandi Syarak, Syarak Basandi Kitabullah.",
  email: "walinagari@nagarisimpang.web.id",
};

export const mockAparaturList: AparaturDetail[] = [
  {
    id: 2,
    nama: "Rahmat Hidayat, S.E.",
    jabatan: "Sekretaris Nagari",
    nip_niap: "NIP: 19800520 200801 1 012",
    foto_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuAeLVOctKoeqCCjMxqcBYhR3_mIAIIQRmyT1GAZPm-ndsqsdfdBLye8t7FJljtYo7vIag0SF5MAvzrnyL8-iNP2IRpbcmEadymGuCCtraMGSDvAk-_3qQt0yBDlnVtGSBMHQzS44zFGJNMPHyxS-I6UVZU1PcCz3tbFGsr_2O08drXb9MNp2hcvIc5G2rYFVTOdHzL8McYpcRwS-LvBPezeX36LwV70K_gkhIAdj7IQ9NtNWVEDO4D6ZNc6tNHzQzWzVTaab6ZGb4nx",
    urut: 1,
  },
  {
    id: 3,
    nama: "Siti Nurhaliza, S.H.",
    jabatan: "Kasi Pemerintahan",
    nip_niap: "NIAP: 19851110 201001 2 005",
    foto_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuB1KCy0BPMSYdj8Q9vny2IVSFw8HCwTjYHfKymO80cc1rTMc72boJjrI7duLigYUF-2TDfY1B5WfkhL3YUBltNAp0v69_RflEr0uOismqBvsPP98BCZIBlH1OWa1av1NMebOtbbGcyTD70HRfTYhAtZWp5ge3XRJeq56WyQwKdbKV9S5HbJoPR4HZUassE2sGtqShW2HBdH6bD0LA6S8xDsvN-SrWfKgGsG2IoPWTeghWKNJRfcre7FOCcA5uQamxZ_GUHe1dv_evmm",
    urut: 2,
  },
  {
    id: 4,
    nama: "Budi Santoso, S.Ag.",
    jabatan: "Kasi Kesejahteraan",
    nip_niap: "NIAP: 19820315 200901 1 008",
    foto_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuB1ZVa0F0CiS2w1-AaVzK2XQMkWMxnpY1Hjqfz2Rf_sz-RV0oEkvRIa3gMCxTHNEVVv1nnxPMejWNtCiaMb4Tre_w7leOr6F9CDxGLZLSAR7JPgjRGZgsLvB3s3d-Ohgqq44f-65GW73ytzYU3ZrKE93m_dnHMdy2Msqry21r9s0VMi5PFJyF1iRXu53X36ybjJOtK9XuGLPDXfgsOVBIYwMPmkNiXYcJQYAbkRUgpcZv6Z00i_oiO9t-HQaMJflQCNUoI0ILCTgvwv",
    urut: 3,
  },
  {
    id: 5,
    nama: "Dian Pertiwi, S.Kom.",
    jabatan: "Kasi Pelayanan",
    nip_niap: "NIAP: 19900722 201501 2 003",
    foto_url:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuA-qEadBrvRayBuHal_LEZoFNcRX3Sbu2T8R3hSIoH7iDIn1YwwRV7iUqYqn3XINUBv6Zn6a5nCydss0pAR04TY7jq0ivpjWNscJXvwg6-UBoQJDzHrPsDud-fRERQnFgXU6Uuz8MVBQtEAvM7jrdP4bVeCe9VhHiycsd2ppNWBAvjRj-oxWSYPlfQb1HRIbAv95nafHfyaxlRjGslXSahanIFVJFfjsFiK75ix9ieCZ7Fn3reBSRJfFT2SkvHy_Y2zEX1oo_O8ZV6z",
    urut: 4,
  },
];

export const mockPemerintahNagari: PemerintahNagariData = {
  wali_nagari: mockWaliNagari,
  perangkat: mockAparaturList,
};

export const mockSotkTree: SotkNode = {
  id: 1,
  nama: "H. Sutan Riska",
  jabatan: "Wali Nagari",
  foto_url:
    "https://lh3.googleusercontent.com/aida-public/AB6AXuD8UwGhHtEvLUrCM4ieDg0v1u6MWDRY8qonYbRs6Kvr3IPzU448MrNGtRHAiu2uVFv8wJcajFC7qeYA_HbRKfjF9iDwnBBpovD98TVVdyljj6LZqsC_AYnm5D-8uCYOs0e2Eo7KkGnttaNj_tzwVvEv6aVgKk99bbUZtsyU5KHs7_SxBU9_fgHD9zPkVn4FU2rtzEabU_UjLfGep2staPRP5QQF3g9LGWeyOQ3vfvlcw3sdw8ijGrVyo7RiiPGfCqofdtYZOfEXStM1",
  bawahan: [
    {
      id: 2,
      nama: "Irwan Syahputra",
      jabatan: "Sekretaris Nagari",
      foto_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuBZP084HW9XewP1s48DFs8V5Kqj0Jle1ilmqC20cWr0ZBWSI2GqOj_c-mlYZx2L7dQ9DKwLPF3GRCAM3VGIKQr8ao7ShIB_PEyQWoSB4O2EmhqtTZBjF3566SuU4QmG0VeDln0pcQlqhQP-blBw0EgdDTavenFWui5WhhOTzlNmE47p0ExUljK1AM3Ti1G5T1-6TRSuJW618bByx6IoMNXUzIrfEJZCbZyteCAaJTFqDwPwm-dT1dDcqIJsS0tgvop7DtRQjd7LN1w3",
      bawahan: [
        {
          id: 3,
          nama: "Siti Aminah",
          jabatan: "Kaur TU & Umum",
          foto_url:
            "https://lh3.googleusercontent.com/aida-public/AB6AXuDIV1p7NJRw_3D02oTHUccz0D5ihuyVnGs5YfzQZJeBIRcecBbiLL_cjNQcpleo4gBK0Bk6mMyQzmNqnEV-XN_ONPDcbSVCL5kxmuLzLkn_4gkarRTguj69YcYPtnp6cftET3jdCuxXWlW4R6UugMmHFzLkND9HGaPkE8L11HZ6rAWIKbDpnup4lWt7DsEHaHRNs12aseoJV5KOuty4GVhEek1XSvvVZOz8IRsdk-9CWC8mpRsQDUgXgMhHCBuTzNoZdk9r-EUlwiAl",
        },
        {
          id: 4,
          nama: "Budi Santoso",
          jabatan: "Kaur Keuangan",
          foto_url:
            "https://lh3.googleusercontent.com/aida-public/AB6AXuB8t8BD-tAYMDjN52yyMBblNVRScoGM99WPiXdpMAq1F0tvAPw5E5KT0YEr3yWZGpUvcMskQXUT1ctmY89ccX8XSrNtfZJ0t9xHE9vINhEu07vZfU0dKGLlGDJCIN_jXoocbjFAHBTSEEHJ7zyiVtJUsXOBiHuZ0r7-h4raksN7wW0bG-lZLVYXZzMG0p6ufQukzeC_fjtsD8HCZErL57jelXe0qZgMC3bd8OPJVqHoWkgfmSGv-Ek08YECEESCDTFsPXZGsH2o2Wu1",
        },
        {
          id: 5,
          nama: "Ratna Dewi",
          jabatan: "Kaur Perencanaan",
          foto_url:
            "https://lh3.googleusercontent.com/aida-public/AB6AXuBkFmOYK9fRd8hHiBGcfuaeF1UVyf-tFOi91bT_IYNRCIP_4hprAfMr-GoDue_KCPdN7yTpI7ywCaRlW9RNrf4Tyl3KNClaenWWDgRDl2IJTGdajbqF6t_Z3amX8UQSr4jzNFDU9UDJcWwWUQr_CdQzebSVHEs9zNDFUIgbfEnt_VPUujNjq6UXUTfTuposyZyYYnoSYHyBbVKTNbwb0e9DfbHeR_zVpRIhPbFEURUF3Pbfr6jjYHeXtKU8syQzkoVOqbYiEllzG__6",
        },
      ],
    },
    {
      id: 6,
      nama: "Ahmad Yani",
      jabatan: "Kasi Pemerintahan",
      foto_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuD5Sk73bOWHNEB9_J36gbR4J0d6ILPthElET23Zp_suiBh1ixutZlMmARohdUE0NckyY7HfMb4QztQ2K0ZroHA3QHjuGpRN6vpiofgG-ev8Bl4KO_gwafeYl02zud0sJ4Y3wLZsSlLhQVkMez0dAOf_Jw76crVZlsJEaUy0seetzYyJiGkaWXnBdYZcsLptZpZIi-eVifqi0_JDbsnzHovprpEddwNQzSkr1ZbnwNgtot4ReTnun7JPYwxVy9NWdZ-G0B9BDjYb9Lwn",
    },
    {
      id: 7,
      nama: "Yulianti",
      jabatan: "Kasi Kesejahteraan",
      foto_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuBrNBDoSzaxY_BG6SV7VXNcIh0VoEiHNEYhFeggsPXPLtyxYeFmVIiPyqL97V0vXssAXRgQcPnaYdNzF3_MQElkVo8mqOJB2LmHohHW6sczPyB-AO0Rb44Amgb61g4WpsENHBFFiRA5tYF9X3N7HYv_l5R_emCi9m-3kqOdQorSzCQLPa69JQFET0mNL-Z6bawfnjbabLjD7UdmvEdZsTcuZHW7KGN0SVSJO5pGJCkKOG_C5REuaPwn0v9HD5QSXbJBGIdPE7I0HdaL",
    },
  ],
};

export const mockPembangunanList: ProyekPembangunan[] = [
  {
    id: 1,
    judul: "Pembangunan Jalan Usaha Tani Jorong Simpang",
    lokasi: "Jorong Simpang",
    anggaran: 150000000,
    sumber_dana: "Dana Desa (APBNag)",
    tahun_anggaran: 2024,
    pelaksana: "Tim Pelaksana Kegiatan (TPK) Nagari",
    progres_persen: 75,
    status: "Dalam Proses",
    foto_cover:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuCsZtes2IoE5mJPGurKE2NCaXMuCTNdTZS6mWpqaq4qiI6CU5HCA6jxRasK5Kq5-9_V06HyX4MKRd6apm7eSIKZmOzhOrodF_-kZDNqGTrlCQMTLeMTA_CYcDdSLM-p_GRDgkwWz7iNbfcbnPiNYIVmpveGTs3hqI5s21N8bGcpo8ohMhzUEGEN4u4_DldFlt0hW8z7nsIWW76xectmMunGoqOk9oKbb-FBZqcKbqTd2pJjruu8ThJNGVSxH3yrFFef_K79aAJAW3vv",
    deskripsi:
      "Pengerjaan pengerasan jalan beton sepanjang 800 meter untuk mempermudah akses pengangkutan hasil panen warga nagari.",
  },
  {
    id: 2,
    judul: "Rehabilitasi Drainase Pemukiman Warga",
    lokasi: "Jorong Koto Baru",
    anggaran: 85000000,
    sumber_dana: "Alokasi Dana Desa (ADD)",
    tahun_anggaran: 2024,
    pelaksana: "Masyarakat Gotong Royong",
    progres_persen: 100,
    status: "Selesai",
    foto_cover:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBdu3HBko2Y96ran8XvqYa_fVBpHr-VgkkDH3xnNJk8GBw465tBDX4-d7MfdSKq8a5_xpRoapegBpEYOgMBTo86wRlMRKHWe-MMsq9m9HDuDh_YtaybEzzfnEobL31X_ok8CIUrugxSfKZ8rqLMVfByFyBsv7A3iIhUiL_R1IsM84flEV0RLKvOCYVDU0UJSch_PuyLRgEGxRbkZY6dOnFo0CSyE8oCpFXfYtVeiA5wrtVdnKtG3-WBlM_lXJrKzRHht4FMCcxZ5OzH",
    deskripsi:
      "Pembangunan dan perbaikan drainase sepanjang 450 meter untuk mengantisipasi luapan air saat musim penghujan.",
  },
  {
    id: 3,
    judul: "Pembangunan Posyandu Lansia & Balai Pertemuan",
    lokasi: "Jorong Bukik Nan Tigo",
    anggaran: 120000000,
    sumber_dana: "Dana Desa 2024",
    tahun_anggaran: 2024,
    pelaksana: "TPK Nagari Simpang",
    progres_persen: 0,
    status: "Perencanaan",
    foto_cover:
      "https://lh3.googleusercontent.com/aida-public/AB6AXuBf-1HLW717saMqpH9z5cUjxdmuZ99X0BEDv4RlpFuqvj4H9EFBE4IWGH5dKm57OhNaVj5iP_j_4P4s1NCZuG1EJZudzk_RJirvRQiOQb-owPb76_P9neNEKH317FaU1K5WwRZBd7Ff77_y_4QM6_Hal2VLNW6gawEcWyR_WZ8EjYIEJLlbrCBJaz4C1FZqYUfvR6GmLv7CG-Y9BUlG82Dk9Z56On7dmGnDDMRO-KaKpwU-FaYgxdjeyokr5n6pHaOEQVb_OPg6pWFL",
    deskripsi:
      "Fasilitas kesehatan dan ruang pertemuan publik terpadu untuk pelayanan masyarakat terkhusus lansia dan balita.",
  },
];

export const mockPembangunanDetail: ProyekPembangunan = {
  ...mockPembangunanList[0],
  foto_dokumentasi: [
    {
      id: 1,
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuCsZtes2IoE5mJPGurKE2NCaXMuCTNdTZS6mWpqaq4qiI6CU5HCA6jxRasK5Kq5-9_V06HyX4MKRd6apm7eSIKZmOzhOrodF_-kZDNqGTrlCQMTLeMTA_CYcDdSLM-p_GRDgkwWz7iNbfcbnPiNYIVmpveGTs3hqI5s21N8bGcpo8ohMhzUEGEN4u4_DldFlt0hW8z7nsIWW76xectmMunGoqOk9oKbb-FBZqcKbqTd2pJjruu8ThJNGVSxH3yrFFef_K79aAJAW3vv",
      persentase: 35,
      keterangan: "Pembersihan lahan dan pengukuran fondasi badan jalan.",
      tgl_upload: "2024-05-10T08:00:00Z",
    },
    {
      id: 2,
      gambar_url:
        "https://lh3.googleusercontent.com/aida-public/AB6AXuBdu3HBko2Y96ran8XvqYa_fVBpHr-VgkkDH3xnNJk8GBw465tBDX4-d7MfdSKq8a5_xpRoapegBpEYOgMBTo86wRlMRKHWe-MMsq9m9HDuDh_YtaybEzzfnEobL31X_ok8CIUrugxSfKZ8rqLMVfByFyBsv7A3iIhUiL_R1IsM84flEV0RLKvOCYVDU0UJSch_PuyLRgEGxRbkZY6dOnFo0CSyE8oCpFXfYtVeiA5wrtVdnKtG3-WBlM_lXJrKzRHht4FMCcxZ5OzH",
      persentase: 75,
      keterangan: "Pengecoran jalan beton segmen I dan pemasangan patok pembatas.",
      tgl_upload: "2024-06-25T08:00:00Z",
    },
  ],
};
