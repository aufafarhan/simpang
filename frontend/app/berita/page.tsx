import type { Metadata } from "next";
import Link from "next/link";
import { Suspense } from "react";
import ArtikelCard from "@/components/artikel/ArtikelCard";
import FilterBerita from "@/components/berita/FilterBerita";
import Breadcrumb from "@/components/ui/Breadcrumb";
import Icon from "@/components/ui/Icon";
import Pagination from "@/components/ui/Pagination";
import { getArtikel, getKategori } from "@/lib/api";

export const metadata: Metadata = {
  title: "Berita & Pengumuman",
  description:
    "Informasi terkini seputar kegiatan, pembangunan, dan kebijakan di lingkungan Nagari Simpang.",
};

// Di Next.js 16 searchParams adalah Promise — wajib di-await.
type Search = Promise<{ page?: string; cari?: string; kategori?: string }>;

export default async function BeritaPage({ searchParams }: { searchParams: Search }) {
  const sp = await searchParams;
  const page = Math.max(1, Number(sp.page ?? 1) || 1);
  const cari = sp.cari?.trim() || undefined;
  const kategori = sp.kategori?.trim() || undefined;

  const [{ items, total_pages, total }, daftarKategori] = await Promise.all([
    getArtikel(page, { cari, kategori }),
    getKategori(),
  ]);

  // Sorotan hanya di halaman pertama tanpa filter aktif
  const adaFilter = Boolean(cari || kategori);
  const sorotan = page === 1 && !adaFilter ? items[0] : null;
  const sisa = sorotan ? items.slice(1) : items;

  function buatHref(p: number): string {
    const q = new URLSearchParams();
    if (cari) q.set("cari", cari);
    if (kategori) q.set("kategori", kategori);
    if (p > 1) q.set("page", String(p));
    const s = q.toString();

    return s ? `/berita?${s}` : "/berita";
  }

  return (
    <div className="mx-auto w-full max-w-7xl px-4 py-12 md:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Beranda", href: "/" }, { label: "Berita & Pengumuman" }]} />

      {/* --- Judul + pencarian --- */}
      <div className="mb-8 flex flex-col items-start justify-between gap-6 md:flex-row md:items-end">
        <div>
          <h1 className="mb-2 font-heading text-4xl font-bold text-primary md:text-5xl">
            Berita &amp; Pengumuman
          </h1>
          <p className="max-w-2xl text-lg text-on-surface-variant">
            Informasi terkini seputar kegiatan, pembangunan, dan kebijakan di lingkungan
            Nagari Simpang.
          </p>
        </div>

        <Suspense fallback={<div className="h-12 w-full md:w-72" />}>
          <FilterBerita kategori={daftarKategori} aktif={kategori} cari={cari} />
        </Suspense>
      </div>

      {/* --- Ringkasan hasil --- */}
      {adaFilter && (
        <p className="mb-8 text-sm text-on-surface-variant">
          Menampilkan <strong className="text-on-surface">{total}</strong> artikel
          {cari && (
            <>
              {" "}
              untuk pencarian &ldquo;<strong className="text-on-surface">{cari}</strong>&rdquo;
            </>
          )}
          .{" "}
          <Link href="/berita" className="font-semibold text-primary hover:underline">
            Atur ulang
          </Link>
        </p>
      )}

      {/* --- Kosong --- */}
      {items.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-outline-variant p-16 text-center">
          <Icon name="search_off" size={48} className="text-outline" />
          <p className="mt-4 font-heading text-xl text-on-surface">Tidak ada berita ditemukan</p>
          <p className="mt-1 text-sm text-on-surface-variant">
            Coba kata kunci lain atau lihat semua berita.
          </p>
          <Link
            href="/berita"
            className="mt-6 inline-flex min-h-11 items-center rounded-full bg-primary px-6 py-2 text-sm font-semibold text-on-primary transition hover:bg-primary-container"
          >
            Lihat semua berita
          </Link>
        </div>
      ) : (
        <>
          {/* --- Sorotan --- */}
          {sorotan && (
            <Link
              href={sorotan.url}
              className="group relative mb-12 flex min-h-[400px] items-end overflow-hidden rounded-2xl shadow-level1 transition-shadow hover:shadow-level2"
            >
              {sorotan.gambar_url ? (
                // eslint-disable-next-line @next/next/no-img-element
                <img
                  src={sorotan.gambar_url}
                  alt={sorotan.judul}
                  className="absolute inset-0 h-full w-full object-cover transition-transform duration-700 group-hover:scale-105"
                />
              ) : (
                <div className="absolute inset-0 bg-primary-container" />
              )}
              <div
                aria-hidden
                className="absolute inset-0 bg-gradient-to-t from-on-surface/90 via-on-surface/40 to-transparent"
              />

              <div className="relative z-10 w-full p-6 md:w-2/3 md:p-10">
                {sorotan.kategori?.nama && (
                  <span className="mb-4 inline-block rounded-full bg-tertiary-container px-3 py-1 text-xs font-semibold text-on-tertiary">
                    {sorotan.kategori.nama}
                  </span>
                )}
                <h2 className="mb-2 font-heading text-2xl font-semibold text-on-primary transition-colors group-hover:text-secondary-container md:text-3xl">
                  {sorotan.judul}
                </h2>
                <p className="mb-6 line-clamp-2 text-surface-variant">{sorotan.ringkasan}</p>
                <div className="flex items-center gap-4 text-xs text-surface-variant">
                  <span className="flex items-center gap-1">
                    <Icon name="calendar_today" size={16} />
                    {new Date(sorotan.tanggal).toLocaleDateString("id-ID", {
                      day: "numeric",
                      month: "long",
                      year: "numeric",
                    })}
                  </span>
                  <span className="flex items-center gap-1">
                    <Icon name="visibility" size={16} />
                    {sorotan.dilihat.toLocaleString("id-ID")} tayangan
                  </span>
                </div>
              </div>
            </Link>
          )}

          {/* --- Grid --- */}
          <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            {sisa.map((a) => (
              <ArtikelCard key={a.id} artikel={a} />
            ))}
          </div>

          <Pagination page={page} totalPages={total_pages} buatHref={buatHref} />
        </>
      )}
    </div>
  );
}
