# Frontend Publik — SID Nagari Simpang

Frontend website publik berbasis **Next.js 16 (App Router) + TypeScript + Tailwind CSS v4**.
Mengonsumsi data dari API OpenSID (`/api/v1/...`). Lihat [../docs/TDD-Frontend-Publik-NextJS.md](../docs/TDD-Frontend-Publik-NextJS.md).

## Menjalankan (development)

```bash
cd frontend
npm install        # jika node_modules belum ada
npm run dev        # buka http://localhost:3000
```

Secara default berjalan dengan **data contoh (mock)** sehingga website langsung bisa dilihat
walaupun API backend OpenSID belum dibangun.

## Konfigurasi (`.env.local`)

| Variabel | Arti |
|----------|------|
| `NEXT_PUBLIC_API_URL` | URL API OpenSID, mis. `https://simpang.desa.id/api/v1`. Kosong = pakai mock. |
| `NEXT_PUBLIC_SITE_URL` | URL publik website (untuk sitemap & Open Graph). |
| `NEXT_PUBLIC_USE_MOCK` | `true` = pakai data contoh; `false` = ambil dari API asli. |

> Setelah endpoint API backend jadi: isi `NEXT_PUBLIC_API_URL`, set `NEXT_PUBLIC_USE_MOCK=false`.

## Struktur

```
app/                     # halaman (routing App Router)
  page.tsx               # Beranda
  artikel/[thn]/[bln]/[hr]/[slug]/   # detail artikel (URL SEO dipertahankan)
  statistik/ profil/ buku-tamu/
  sitemap.ts  robots.ts  # SEO
components/               # Header, Footer, ArtikelCard, KomentarThread, ChartPenduduk
lib/
  api.ts                 # wrapper fetch ke OpenSID (+ fallback mock)
  types.ts               # tipe data (kontrak API)
  mock.ts                # data contoh sementara
```

## Catatan

- **Next.js 16**: `params` & `searchParams` di halaman adalah `Promise` — wajib `await`.
- Grafik statistik memakai CSS sederhana; bisa diganti Recharts/Chart.js bila perlu.
- Isi artikel dirender via `dangerouslySetInnerHTML` — **wajib** sudah disanitasi di backend.

## Perintah

| Perintah | Fungsi |
|----------|--------|
| `npm run dev` | Jalankan server pengembangan |
| `npm run build` | Build produksi (sudah diverifikasi ✓) |
| `npm run start` | Jalankan hasil build |
| `npm run lint` | Cek ESLint |
