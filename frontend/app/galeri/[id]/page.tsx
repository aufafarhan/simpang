import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import Icon from "@/components/ui/Icon";
import GaleriDetailClient from "@/components/galeri/GaleriDetailClient";
import { getGaleriDetail } from "@/lib/api";

type Params = Promise<{ id: string }>;

export async function generateMetadata({
  params,
}: {
  params: Params;
}): Promise<Metadata> {
  const { id } = await params;
  const album = await getGaleriDetail(id);
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  const title = album?.nama ? `${album.nama} — Galeri` : "Galeri";

  return {
    title,
    description:
      album?.deskripsi ||
      "Kumpulan potret dan momen penting di Nagari Simpang.",
  };
}

export default async function AlbumDetailPage({
  params,
}: {
  params: Params;
}) {
  const { id } = await params;
  const album = await getGaleriDetail(id);

  if (!album) {
    notFound();
  }

  return (
    <main className="mx-auto flex-grow w-full max-w-6xl px-4 py-8 md:px-6 md:py-12">
      {/* Breadcrumbs */}
      <nav aria-label="Breadcrumb" className="mb-6 flex items-center text-xs font-semibold text-primary">
        <Link href="/" className="hover:underline">
          Beranda
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <Link href="/galeri" className="hover:underline">
          Galeri
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <span aria-current="page" className="text-secondary font-bold truncate max-w-[200px] md:max-w-xs">
          {album.nama}
        </span>
      </nav>

      {/* Album Header */}
      <section className="mx-auto mb-12 max-w-3xl text-center">
        <div className="mb-4 inline-flex items-center justify-center gap-2 text-secondary">
          <span className="h-[2px] w-8 bg-secondary" />
          <span className="text-xs uppercase tracking-widest font-bold text-secondary">
            Galeri Nagari
          </span>
          <span className="h-[2px] w-8 bg-secondary" />
        </div>
        <h1 className="font-heading text-3xl font-bold text-primary md:text-5xl mb-4">
          {album.nama}
        </h1>
        {album.deskripsi && (
          <p className="text-base text-on-surface-variant md:text-lg leading-relaxed">
            {album.deskripsi}
          </p>
        )}
      </section>

      {/* Photo / Video Grid with Lightbox */}
      {album.foto && album.foto.length > 0 ? (
        <GaleriDetailClient album={album} />
      ) : (
        <div className="rounded-xl border border-outline-variant bg-surface-container-low p-12 text-center text-on-surface-variant">
          <Icon name="photo" size={48} className="mx-auto mb-3 text-outline" />
          <p className="text-lg font-medium">Belum ada foto dalam album ini.</p>
        </div>
      )}
    </main>
  );
}
