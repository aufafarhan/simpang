import Link from "next/link";
import ArtikelCard from "@/components/artikel/ArtikelCard";
import { getArtikel, getHeadline, getProfilDesa } from "@/lib/api";

export default async function Beranda() {
  const [profil, headline, { items }] = await Promise.all([
    getProfilDesa(),
    getHeadline(),
    getArtikel(1),
  ]);

  const utama = headline[0] ?? items[0];

  return (
    <div className="mx-auto max-w-6xl px-4">
      {/* HERO */}
      <section className="my-6 overflow-hidden rounded-2xl bg-gradient-to-r from-navy-900 to-navy-700 px-6 py-12 text-white sm:px-10">
        <p className="mb-2 text-sm uppercase tracking-widest opacity-80">
          Selamat datang di
        </p>
        <h1 className="text-3xl font-extrabold sm:text-4xl">{profil.nama_desa}</h1>
        <p className="mt-3 max-w-xl text-white/90">{profil.tema.judul_web}</p>
        <div className="mt-6 flex gap-3">
          <Link
            href="/profil"
            className="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-navy-700 hover:bg-navy-50"
          >
            Profil Desa
          </Link>
          <Link
            href="/statistik"
            className="rounded-lg border border-white/40 px-4 py-2 text-sm font-semibold hover:bg-white/10"
          >
            Lihat Statistik
          </Link>
        </div>
      </section>

      {/* HEADLINE */}
      {utama && (
        <section className="mb-8">
          <h2 className="mb-3 text-lg font-bold text-slate-900">Sorotan</h2>
          <Link
            href={utama.url}
            className="block rounded-xl border border-slate-200 p-6 transition hover:shadow-md"
          >
            <span className="text-xs font-medium text-navy-500">
              {utama.kategori?.nama}
            </span>
            <h3 className="mt-1 text-xl font-bold text-slate-900">{utama.judul}</h3>
            <p className="mt-2 text-slate-600">{utama.ringkasan}</p>
          </Link>
        </section>
      )}

      {/* BERITA TERBARU */}
      <section className="mb-10">
        <div className="mb-4 flex items-center justify-between">
          <h2 className="text-lg font-bold text-slate-900">Berita Terbaru</h2>
          <Link href="/arsip" className="text-sm font-medium text-navy-500 hover:underline">
            Lihat semua →
          </Link>
        </div>
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
          {items.map((a) => (
            <ArtikelCard key={a.id} artikel={a} />
          ))}
        </div>
      </section>
    </div>
  );
}
