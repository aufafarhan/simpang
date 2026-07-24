import Link from "next/link";
import Icon from "@/components/ui/Icon";
import type { ArtikelRingkas } from "@/lib/types";

function formatTanggal(iso: string): string {
  try {
    return new Date(iso).toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    });
  } catch {
    return iso;
  }
}

export default function ArtikelCard({ artikel }: { artikel: ArtikelRingkas }) {
  const gambar = artikel.thumbnail_url ?? artikel.gambar_url;

  return (
    <article className="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface shadow-level1 transition-shadow hover:shadow-level2">
      {/* Gambar dengan lengkung atap "gonjong" khas Minangkabau */}
      <div className="gonjong-clip relative h-48 w-full overflow-hidden bg-surface-variant">
        {gambar ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={gambar}
            alt={artikel.judul}
            loading="lazy"
            className="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
          />
        ) : (
          <div className="flex h-full items-center justify-center text-outline">
            <Icon name="image" size={40} />
          </div>
        )}

        {artikel.kategori?.nama && (
          <span className="absolute right-2 top-2 rounded bg-surface/90 px-2 py-1 text-xs font-medium text-primary backdrop-blur-sm">
            {artikel.kategori.nama}
          </span>
        )}
      </div>

      <div className="flex flex-grow flex-col p-4">
        <div className="mb-2 flex items-center gap-1 text-xs text-on-surface-variant">
          <Icon name="schedule" size={14} />
          {formatTanggal(artikel.tanggal)}
          <span aria-hidden className="mx-1">
            ·
          </span>
          {artikel.dilihat}× dilihat
        </div>

        <h3 className="mb-2 line-clamp-2 font-heading text-xl font-semibold leading-tight text-on-surface">
          <Link href={artikel.url} className="transition-colors hover:text-primary">
            {artikel.judul}
          </Link>
        </h3>

        <p className="mb-4 line-clamp-3 flex-grow text-sm text-on-surface-variant">
          {artikel.ringkasan}
        </p>
      </div>
    </article>
  );
}
