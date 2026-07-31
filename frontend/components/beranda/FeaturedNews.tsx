import Link from "next/link";
import Icon from "@/components/ui/Icon";
import type { ArtikelRingkas } from "@/lib/types";

/** Sorotan utama — gambar besar dengan gradasi & judul di atasnya. */
export default function FeaturedNews({ artikel }: { artikel: ArtikelRingkas }) {
  return (
    <section className="overflow-hidden rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-level1">
      <Link href={artikel.url} className="group relative block h-64 w-full bg-surface-variant md:h-80">
        {artikel.gambar_url ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={artikel.gambar_url}
            alt={artikel.judul}
            className="h-full w-full object-cover"
          />
        ) : (
          <div className="flex h-full items-center justify-center text-outline">
            <Icon name="image" size={56} />
          </div>
        )}

        <div
          aria-hidden
          className="absolute inset-0 bg-gradient-to-t from-on-surface/90 via-on-surface/40 to-transparent"
        />

        <div className="absolute bottom-0 left-0 w-full p-6">
          <div className="mb-2 flex flex-wrap items-center gap-2">
            {artikel.kategori?.nama && (
              <span className="rounded bg-secondary px-2 py-1 text-xs font-medium text-on-secondary">
                {artikel.kategori.nama}
              </span>
            )}
            <span className="flex items-center gap-1 text-xs text-surface-container-highest">
              <Icon name="calendar_today" size={14} />
              {new Date(artikel.tanggal).toLocaleDateString("id-ID", {
                day: "numeric",
                month: "long",
                year: "numeric",
              })}
            </span>
          </div>

          <h3 className="line-clamp-2 font-heading text-xl font-semibold text-on-primary transition-colors group-hover:text-secondary-container md:text-2xl">
            {artikel.judul}
          </h3>
        </div>
      </Link>
    </section>
  );
}
