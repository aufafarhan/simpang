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

// ---- Endpoint publik (§3.3) ----

export async function getProfilDesa(): Promise<ProfilDesa> {
  const r = await apiGet<ProfilDesa>("/desa/profil", { revalidate: 3600 });
  return r?.data ?? mockProfil;
}

export async function getArtikel(
  page = 1,
): Promise<{ items: ArtikelRingkas[]; total_pages: number }> {
  const r = await apiGet<ArtikelRingkas[]>(`/artikel?page=${page}`, { revalidate: 60 });
  if (!r) return { items: mockArtikelList, total_pages: 1 };
  return { items: r.data, total_pages: r.meta?.total_pages ?? 1 };
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

export async function getStatistikPenduduk(
  stat = "jenis_kelamin",
): Promise<StatistikPenduduk> {
  const r = await apiGet<StatistikPenduduk>(`/statistik/penduduk?stat=${stat}`, {
    revalidate: 300,
  });
  return r?.data ?? mockStatistik;
}
