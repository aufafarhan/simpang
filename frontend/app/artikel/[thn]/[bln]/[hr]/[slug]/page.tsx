import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import ArtikelCard from "@/components/artikel/ArtikelCard";
import FormKomentar from "@/components/artikel/FormKomentar";
import KomentarThread from "@/components/artikel/KomentarThread";
import Breadcrumb from "@/components/ui/Breadcrumb";
import Icon from "@/components/ui/Icon";
import { getArtikel, getArtikelDetail, getKomentar } from "@/lib/api";

// Di Next.js 16 params adalah Promise — wajib di-await.
type Params = Promise<{ thn: string; bln: string; hr: string; slug: string }>;

export async function generateMetadata({ params }: { params: Params }): Promise<Metadata> {
  const { thn, bln, hr, slug } = await params;
  const artikel = await getArtikelDetail(thn, bln, hr, slug);
  if (!artikel) return { title: "Artikel tidak ditemukan" };

  return {
    // `absolute` mencegah template judul di layout menambah nama desa dua kali,
    // karena seo.title dari API sudah menyertakannya.
    title: { absolute: artikel.seo.title },
    description: artikel.seo.description,
    openGraph: {
      title: artikel.seo.title,
      description: artikel.seo.description,
      type: "article",
      publishedTime: artikel.tanggal,
      images: artikel.seo.og_image ? [artikel.seo.og_image] : [],
    },
  };
}

function hitungKomentar(list: { children?: unknown[] }[]): number {
  return list.reduce(
    (n, k) => n + 1 + hitungKomentar((k.children ?? []) as { children?: unknown[] }[]),
    0,
  );
}

export default async function DetailArtikel({ params }: { params: Params }) {
  const { thn, bln, hr, slug } = await params;
  const artikel = await getArtikelDetail(thn, bln, hr, slug);
  if (!artikel) notFound();

  const [komentar, terbaru] = await Promise.all([
    getKomentar(artikel.id),
    getArtikel(1),
  ]);

  const terkait = terbaru.items.filter((a) => a.id !== artikel.id).slice(0, 3);
  const jumlahKomentar = hitungKomentar(komentar);
  const tanggal = new Date(artikel.tanggal).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric",
  });

  return (
    <div className="mx-auto w-full max-w-7xl px-4 py-12 md:px-6 lg:px-8">
      <Breadcrumb
        items={[
          { label: "Beranda", href: "/" },
          { label: "Berita", href: "/berita" },
          { label: artikel.judul },
        ]}
      />

      <div className="grid grid-cols-1 gap-10 lg:grid-cols-12">
        {/* ================= ARTIKEL ================= */}
        <article className="lg:col-span-8">
          {artikel.kategori?.nama && (
            <Link
              href={`/berita?kategori=${artikel.kategori.id}`}
              className="mb-3 inline-block rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-secondary-container"
            >
              {artikel.kategori.nama}
            </Link>
          )}

          <h1 className="mb-4 font-heading text-3xl font-bold leading-tight text-on-surface md:text-4xl">
            {artikel.judul}
          </h1>

          <div className="mb-8 flex flex-wrap items-center gap-4 border-b border-outline-variant pb-6 text-sm text-on-surface-variant">
            <span className="flex items-center gap-1">
              <Icon name="person" size={16} />
              {artikel.penulis}
            </span>
            <span className="flex items-center gap-1">
              <Icon name="calendar_today" size={16} />
              {tanggal}
            </span>
            <span className="flex items-center gap-1">
              <Icon name="visibility" size={16} />
              {artikel.dilihat.toLocaleString("id-ID")} tayangan
            </span>
          </div>

          {artikel.gambar_url && (
            <figure className="mb-8 overflow-hidden rounded-2xl border border-outline-variant">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={artikel.gambar_url}
                alt={artikel.judul}
                className="w-full object-cover"
              />
            </figure>
          )}

          {/* Isi sudah disanitasi di backend (lihat TDD §7). */}
          <div
            className="artikel-isi max-w-none text-lg leading-relaxed text-on-surface-variant"
            dangerouslySetInnerHTML={{ __html: artikel.isi }}
          />

          {artikel.dokumen.length > 0 && (
            <section className="mt-10 rounded-xl border border-outline-variant bg-surface-container-low p-5">
              <h2 className="mb-3 flex items-center gap-2 font-heading text-lg font-semibold text-primary">
                <Icon name="attach_file" size={20} />
                Lampiran
              </h2>
              <ul className="space-y-2">
                {artikel.dokumen.map((d) => (
                  <li key={d.id}>
                    <a
                      href={d.url}
                      className="flex items-center gap-2 rounded-lg border border-outline-variant bg-surface p-3 text-sm font-medium text-primary transition hover:bg-surface-container"
                    >
                      <Icon name="description" size={20} />
                      {d.nama}
                      <Icon name="download" size={18} className="ml-auto" />
                    </a>
                  </li>
                ))}
              </ul>
            </section>
          )}

          {/* --- Bagikan --- */}
          <BagikanBerita judul={artikel.judul} />

          {/* --- Komentar --- */}
          <section className="mt-16 rounded-xl bg-surface-container-low p-6">
            <h2 className="mb-6 flex items-center gap-2 font-heading text-2xl font-semibold text-primary">
              <Icon name="forum" />
              Komentar ({jumlahKomentar})
            </h2>

            <KomentarThread komentar={komentar} />

            <div className="mt-8 border-t border-outline-variant pt-6">
              <h3 className="mb-4 font-heading text-lg font-semibold text-on-surface">
                Tinggalkan Komentar
              </h3>
              <FormKomentar idArtikel={artikel.id} />
            </div>
          </section>
        </article>

        {/* ================= SIDEBAR ================= */}
        <aside className="flex flex-col gap-10 lg:col-span-4">
          {terkait.length > 0 && (
            <section>
              <h2 className="songket-border mb-6 inline-block pb-1 font-heading text-xl font-semibold text-primary">
                Berita Terkait
              </h2>
              <div className="flex flex-col gap-6">
                {terkait.map((a) => (
                  <ArtikelCard key={a.id} artikel={a} />
                ))}
              </div>
            </section>
          )}
        </aside>
      </div>
    </div>
  );
}

/** Tombol bagikan (tanpa JS — memakai tautan share bawaan platform). */
function BagikanBerita({ judul }: { judul: string }) {
  const teks = encodeURIComponent(judul);

  const tautan = [
    { nama: "WhatsApp", href: `https://wa.me/?text=${teks}`, icon: "chat" },
    {
      nama: "Facebook",
      href: `https://www.facebook.com/sharer/sharer.php?quote=${teks}`,
      icon: "thumb_up",
    },
  ];

  return (
    <div className="mt-10 flex flex-wrap items-center gap-3 border-t border-outline-variant pt-6">
      <span className="text-sm font-semibold text-on-surface-variant">Bagikan berita ini:</span>
      {tautan.map((t) => (
        <a
          key={t.nama}
          href={t.href}
          target="_blank"
          rel="noopener noreferrer"
          className="inline-flex min-h-11 items-center gap-2 rounded-full border border-outline-variant px-4 py-2 text-sm font-medium text-on-surface-variant transition hover:border-primary hover:text-primary"
        >
          <Icon name={t.icon} size={18} />
          {t.nama}
        </a>
      ))}
    </div>
  );
}
