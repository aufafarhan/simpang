// Wrapper fetch ke API OpenSID (backend CI3).
// Sesuai docs/TDD-Frontend-Publik-NextJS.md (§3, §5.4).
//
// Selama endpoint backend belum jadi, aktifkan mock dengan NEXT_PUBLIC_USE_MOCK=true
// (default true di .env.local). Jika fetch gagal, otomatis fallback ke mock juga.

import {
  mockArtikelDetail,
  mockArtikelList,
  mockKomentar,
  mockProfil,
  mockStatistik,
} from "./mock";
import type {
  ApiResponse,
  ArtikelDetail,
  ArtikelRingkas,
  BerandaData,
  KategoriRingkas,
  Komentar,
  ProfilDesa,
  StatistikPenduduk,
} from "./types";

const BASE = process.env.NEXT_PUBLIC_API_URL ?? "";
const USE_MOCK = process.env.NEXT_PUBLIC_USE_MOCK === "true" || BASE === "";

interface FetchOpts {
  revalidate?: number; // detik (ISR)
}

async function apiGet<T>(path: string, opts: FetchOpts = {}): Promise<ApiResponse<T> | null> {
  if (USE_MOCK) return null; // penanda: pakai mock di pemanggil
  try {
    const res = await fetch(`${BASE}${path}`, {
      next: { revalidate: opts.revalidate ?? 60 },
      headers: { Accept: "application/json" },
    });
    if (!res.ok) throw new Error(`HTTP ${res.status} untuk ${path}`);
    return (await res.json()) as ApiResponse<T>;
  } catch (err) {
    console.warn(`[api] gagal fetch ${path} — fallback ke mock.`, err);
    return null;
  }
}

async function apiPost<T>(path: string, body: any): Promise<ApiResponse<T> | null> {
  if (USE_MOCK) throw new Error("Mock mode aktif, tidak bisa mengirim data.");
  try {
    const res = await fetch(`${BASE}${path}`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(body),
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.message || `HTTP ${res.status} untuk ${path}`);
    return data as ApiResponse<T>;
  } catch (err) {
    console.warn(`[api] gagal POST ${path}`, err);
    throw err;
  }
}

// ---- Endpoint publik (§3.3) ----

/**
 * Data agregat halaman depan: slider, headline, artikel, dan seluruh widget
 * sidebar dalam satu panggilan. Mengembalikan null bila API tidak tersedia —
 * pemanggil wajib menangani kondisi ini.
 */
export async function getBeranda(page = 1): Promise<BerandaData | null> {
  const r = await apiGet<BerandaData>(`/beranda?page=${page}`, { revalidate: 60 });

  return r?.data ?? null;
}

export async function getProfilDesa(): Promise<ProfilDesa> {
  const r = await apiGet<ProfilDesa>("/desa/profil", { revalidate: 3600 });
  return r?.data ?? mockProfil;
}

export interface OpsiArtikel {
  cari?: string;
  kategori?: string;
}

export async function getArtikel(
  page = 1,
  opsi: OpsiArtikel = {},
): Promise<{ items: ArtikelRingkas[]; total_pages: number; total: number }> {
  const q = new URLSearchParams({ page: String(page) });
  if (opsi.cari) q.set("cari", opsi.cari);
  if (opsi.kategori) q.set("kategori", opsi.kategori);

  const r = await apiGet<ArtikelRingkas[]>(`/artikel?${q}`, { revalidate: 60 });
  if (!r) return { items: mockArtikelList, total_pages: 1, total: mockArtikelList.length };

  return {
    items: r.data,
    total_pages: r.meta?.total_pages ?? 1,
    total: r.meta?.total ?? r.data.length,
  };
}

export async function getKategori(): Promise<KategoriRingkas[]> {
  const r = await apiGet<KategoriRingkas[]>("/kategori", { revalidate: 3600 });

  return r?.data ?? [];
}

export async function getHeadline(): Promise<ArtikelRingkas[]> {
  const r = await apiGet<ArtikelRingkas[]>("/artikel/headline", { revalidate: 60 });
  return r?.data ?? mockArtikelList.filter((a) => a.headline);
}

export async function getArtikelDetail(
  thn: string,
  bln: string,
  hr: string,
  slug: string,
): Promise<ArtikelDetail | null> {
  const r = await apiGet<ArtikelDetail>(`/artikel/${thn}/${bln}/${hr}/${slug}`, {
    revalidate: 60,
  });
  if (!r) return { ...mockArtikelDetail, slug };
  return r.data;
}

export async function getKomentar(idArtikel: number): Promise<Komentar[]> {
  const r = await apiGet<Komentar[]>(`/artikel/${idArtikel}/komentar`, { revalidate: 30 });
  return r?.data ?? mockKomentar;
}

export interface CaptchaData {
  pertanyaan: string;
  token: string;
}

export async function getCaptcha(): Promise<CaptchaData | null> {
  // force no-cache agar captcha selalu baru
  const r = await fetch(`${BASE}/artikel/captcha`, { cache: "no-store", headers: { Accept: "application/json" } })
    .then((res) => res.ok ? res.json() : null)
    .catch(() => null);
  return r?.data ?? { pertanyaan: "2 + 3 = ?", token: "mock_token" };
}

export async function postKomentar(
  idArtikel: number,
  data: {
    nama: string;
    email: string;
    isi: string;
    captcha_jawaban: string;
    captcha_token: string;
  }
): Promise<{ success: boolean; message: string }> {
  try {
    if (USE_MOCK) return { success: true, message: "Komentar mock berhasil dikirim." };
    const r = await apiPost<any>(`/artikel/${idArtikel}/komentar`, data);
    return { success: true, message: r?.message ?? "Komentar berhasil dikirim." };
  } catch (err: any) {
    return { success: false, message: err.message ?? "Gagal mengirim komentar." };
  }
}

export async function getStatistikPenduduk(
  stat = "jenis_kelamin",
): Promise<StatistikPenduduk> {
  const r = await apiGet<StatistikPenduduk>(`/statistik/penduduk?stat=${stat}`, {
    revalidate: 300,
  });
  return r?.data ?? mockStatistik;
}
