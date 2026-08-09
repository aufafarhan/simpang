import type { Metadata } from "next";
import Link from "next/link";
import AksiStatistik from "@/components/statistik/AksiStatistik";
import GrafikStatistik from "@/components/statistik/GrafikStatistik";
import Breadcrumb from "@/components/ui/Breadcrumb";
import Icon from "@/components/ui/Icon";
import { getStatistikPenduduk } from "@/lib/api";
import { KATEGORI_STATISTIK, cariKategori, cariSub } from "@/lib/statistik-menu";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  title: "Data Statistik",
  description:
    "Data statistik kependudukan Nagari Simpang: rentang umur, pendidikan, pekerjaan, agama, dan lainnya.",
};

// Di Next.js 16 searchParams adalah Promise — wajib di-await.
type Search = Promise<{ kategori?: string; stat?: string }>;

export default async function StatistikPage({ searchParams }: { searchParams: Search }) {
  const sp = await searchParams;
  const kategori = cariKategori(sp.kategori);
  const sub = cariSub(kategori, sp.stat);

  // Sub-kategori yang endpoint-nya belum ada sengaja tidak dipanggil ke API.
  const data = sub.tersedia === false ? null : await getStatistikPenduduk(sub.slug);

  return (
    <main className="mx-auto w-full max-w-6xl px-4 py-12 md:px-6 lg:px-8">
      <Breadcrumb
        items={[
          { label: "Beranda", href: "/" },
          { label: "Data Statistik" },
          { label: kategori.label },
        ]}
      />

      <header className="mb-8">
        <h1 className="mb-2 font-heading text-3xl font-bold text-primary md:text-4xl">
          Data Statistik
        </h1>
        <p className="max-w-3xl text-on-surface-variant">
          Angka di bawah dihitung langsung dari basis data kependudukan nagari, memakai
          mesin statistik yang sama dengan panel admin.
        </p>
      </header>

      {/* --- Kategori utama --- */}
      <nav aria-label="Kategori statistik" className="mb-6">
        <ul className="flex flex-wrap gap-2">
          {KATEGORI_STATISTIK.map((k) => {
            const aktif = k.slug === kategori.slug;

            return (
              <li key={k.slug}>
                <Link
                  href={`/statistik?kategori=${k.slug}`}
                  aria-current={aktif ? "page" : undefined}
                  className={`flex min-h-11 items-center gap-2 rounded-lg px-4 text-sm font-semibold transition ${
                    aktif
                      ? "bg-primary text-on-primary"
                      : "border border-outline-variant text-on-surface hover:bg-surface-container"
                  }`}
                >
                  <Icon name={k.ikon} size={18} />
                  {k.label}
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>

      {/* --- Sub-kategori --- */}
      <nav aria-label={`Sub-kategori ${kategori.label}`} className="songket-border mb-8 pb-4">
        <ul className="flex flex-wrap gap-2">
          {kategori.sub.map((s) => {
            const aktif = s.slug === sub.slug;

            return (
              <li key={s.slug}>
                <Link
                  href={`/statistik?kategori=${kategori.slug}&stat=${s.slug}`}
                  aria-current={aktif ? "page" : undefined}
                  className={`block min-h-11 rounded-full px-4 py-2.5 text-sm transition ${
                    aktif
                      ? "bg-secondary-container font-semibold text-on-secondary-container"
                      : "border border-outline-variant text-on-surface-variant hover:bg-surface-container"
                  }`}
                >
                  {s.label}
                  {s.tersedia === false && (
                    <span className="ml-1 text-xs text-outline">(belum tersedia)</span>
                  )}
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>

      {/* --- Hasil --- */}
      {data === null ? (
        <div className="rounded-2xl border border-dashed border-outline-variant p-12 text-center">
          <Icon name="construction" size={48} className="text-outline" />
          <p className="mt-4 font-heading text-xl text-on-surface">
            {sub.label} belum tersedia
          </p>
          <p className="mx-auto mt-2 max-w-md text-sm text-on-surface-variant">
            Statistik ini bukan bagian dari modul statistik OpenSID — datanya berasal
            dari modul terpisah dan belum disambungkan ke situs.
          </p>
        </div>
      ) : data.kategori.length === 0 ? (
        <div className="rounded-2xl border border-dashed border-outline-variant p-12 text-center">
          <Icon name="inbox" size={48} className="text-outline" />
          <p className="mt-4 font-heading text-xl text-on-surface">
            Belum ada data {sub.label.toLowerCase()}
          </p>
          <p className="mx-auto mt-2 max-w-md text-sm text-on-surface-variant">
            Datanya belum diisi oleh perangkat nagari lewat panel admin.
          </p>
        </div>
      ) : (
        <>
          <AksiStatistik data={data} />
          <GrafikStatistik data={data} />
        </>
      )}
    </main>
  );
}
