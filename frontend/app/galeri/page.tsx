import type { Metadata } from "next";
import Link from "next/link";
import Icon from "@/components/ui/Icon";
import { getGaleri } from "@/lib/api";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  title: "Galeri Album",
  description:
    "Dokumentasi kegiatan, keindahan alam, dan momen penting di Nagari Simpang. Jelajahi memori desa kami melalui lensa.",
};

function formatBulanTahun(tglStr: string | null): string {
  if (!tglStr) return "";
  try {
    const d = new Date(tglStr);
    if (isNaN(d.getTime())) return tglStr;
    return d.toLocaleDateString("id-ID", { month: "long", year: "numeric" });
  } catch {
    return tglStr;
  }
}

export default async function GaleriPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string }>;
}) {
  const params = await searchParams;
  const page = Math.max(1, parseInt(params.page || "1", 10));

  const { items: albums, total_pages } = await getGaleri(page);

  return (
    <main className="mx-auto flex-grow w-full max-w-6xl px-4 py-8 md:px-6 md:py-12">
      {/* Breadcrumbs */}
      <nav aria-label="Breadcrumb" className="mb-6 flex items-center text-xs font-semibold text-primary">
        <Link href="/" className="hover:underline">
          Beranda
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <span aria-current="page" className="text-secondary font-bold">
          Galeri
        </span>
      </nav>

      {/* Page Header */}
      <div className="mb-10 border-b-2 border-secondary/30 pb-4">
        <h1 className="font-heading text-3xl font-bold text-primary md:text-5xl mb-2">
          Galeri Album
        </h1>
        <p className="max-w-2xl text-base text-on-surface-variant md:text-lg">
          Dokumentasi kegiatan, keindahan alam, dan momen penting di Nagari Simpang. Jelajahi memori desa kami melalui lensa.
        </p>
      </div>

      {/* Gallery Grid */}
      {albums.length === 0 ? (
        <div className="rounded-xl border border-outline-variant bg-surface-container-low p-12 text-center text-on-surface-variant">
          <Icon name="photo_library" size={48} className="mx-auto mb-3 text-outline" />
          <p className="text-lg font-medium">Belum ada album galeri yang tersedia.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          {albums.map((album) => (
            <Link
              key={album.id}
              href={`/galeri/${album.id}`}
              className="group block overflow-hidden rounded-lg border border-outline-variant border-b-[3px] border-b-secondary bg-surface shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
            >
              <div className="relative h-64 overflow-hidden">
                {/* eslint-disable-next-line @next/next/no-img-element */}
                <img
                  src={album.gambar_url || "/placeholder-gallery.jpg"}
                  alt={album.nama}
                  className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent" />

                {/* Badge Jumlah Foto */}
                <div className="absolute right-4 top-4 flex items-center gap-1 rounded-full border border-outline-variant bg-surface/90 px-3 py-1 text-xs font-semibold text-primary shadow-sm backdrop-blur-sm">
                  <Icon name="photo_library" size={16} filled />
                  <span>{album.jumlah_foto} Foto</span>
                </div>
              </div>

              <div className="p-4">
                <h2 className="font-heading text-xl font-bold text-primary transition-colors group-hover:text-tertiary mb-1">
                  {album.nama}
                </h2>
                {album.tgl_upload && (
                  <p className="flex items-center gap-1.5 text-xs text-on-surface-variant">
                    <Icon name="calendar_today" size={14} />
                    <span>{formatBulanTahun(album.tgl_upload)}</span>
                  </p>
                )}
              </div>
            </Link>
          ))}
        </div>
      )}

      {/* Pagination */}
      {total_pages > 1 && (
        <div className="mt-12 flex justify-center gap-2">
          {page > 1 && (
            <Link
              href={`/galeri?page=${page - 1}`}
              className="flex h-10 w-10 items-center justify-center rounded border border-outline-variant text-on-surface-variant transition hover:bg-surface-container"
              aria-label="Halaman sebelumnya"
            >
              <Icon name="chevron_left" size={20} />
            </Link>
          )}

          {Array.from({ length: total_pages }, (_, i) => i + 1).map((p) => (
            <Link
              key={p}
              href={`/galeri?page=${p}`}
              className={`flex h-10 w-10 items-center justify-center rounded font-semibold transition ${
                p === page
                  ? "bg-secondary text-on-secondary-container shadow-sm"
                  : "border border-outline-variant text-on-surface-variant hover:bg-surface-container"
              }`}
            >
              {p}
            </Link>
          ))}

          {page < total_pages && (
            <Link
              href={`/galeri?page=${page + 1}`}
              className="flex h-10 w-10 items-center justify-center rounded border border-outline-variant text-on-surface-variant transition hover:bg-surface-container"
              aria-label="Halaman selanjutnya"
            >
              <Icon name="chevron_right" size={20} />
            </Link>
          )}
        </div>
      )}
    </main>
  );
}
