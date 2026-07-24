import type { Metadata } from "next";
import { notFound } from "next/navigation";
import KomentarThread from "@/components/artikel/KomentarThread";
import { getArtikelDetail, getKomentar } from "@/lib/api";

// Di Next.js 16 params adalah Promise — wajib di-await.
type Params = Promise<{ thn: string; bln: string; hr: string; slug: string }>;

export async function generateMetadata({
  params,
}: {
  params: Params;
}): Promise<Metadata> {
  const { thn, bln, hr, slug } = await params;
  const artikel = await getArtikelDetail(thn, bln, hr, slug);
  if (!artikel) return { title: "Artikel tidak ditemukan" };
  return {
    title: artikel.seo.title,
    description: artikel.seo.description,
    openGraph: {
      title: artikel.seo.title,
      description: artikel.seo.description,
      type: "article",
      images: artikel.seo.og_image ? [artikel.seo.og_image] : [],
    },
  };
}

export default async function DetailArtikel({ params }: { params: Params }) {
  const { thn, bln, hr, slug } = await params;
  const artikel = await getArtikelDetail(thn, bln, hr, slug);
  if (!artikel) notFound();

  const komentar = await getKomentar(artikel.id);

  return (
    <article className="mx-auto max-w-3xl px-4 py-8">
      {artikel.kategori && (
        <span className="text-sm font-medium text-primary">{artikel.kategori.nama}</span>
      )}
      <h1 className="mt-1 text-3xl font-extrabold text-on-surface">{artikel.judul}</h1>
      <div className="mt-2 text-sm text-outline">
        {new Date(artikel.tanggal).toLocaleDateString("id-ID", {
          day: "numeric",
          month: "long",
          year: "numeric",
        })}{" "}
        · {artikel.dilihat}× dilihat
      </div>

      {/* Isi artikel sudah disanitasi di backend (lihat TDD §7). */}
      <div
        className="prose mt-6 max-w-none text-on-surface-variant"
        dangerouslySetInnerHTML={{ __html: artikel.isi }}
      />

      {artikel.dokumen.length > 0 && (
        <section className="mt-8">
          <h2 className="mb-2 font-bold text-on-surface">Lampiran</h2>
          <ul className="list-inside list-disc text-sm text-primary">
            {artikel.dokumen.map((d) => (
              <li key={d.id}>
                <a href={d.url} className="hover:underline">
                  {d.nama}
                </a>
              </li>
            ))}
          </ul>
        </section>
      )}

      <section className="mt-10">
        <h2 className="mb-4 text-lg font-bold text-on-surface">Komentar</h2>
        <KomentarThread komentar={komentar} />
      </section>
    </article>
  );
}
