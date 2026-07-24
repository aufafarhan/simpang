import type { GaleriItem } from "@/lib/types";

/** Grid album galeri (dipakai sebagai section penuh di beranda). */
export default function Galeri({ data }: { data: GaleriItem[] }) {
  const items = (data ?? []).filter((g) => g.gambar_url);
  if (!items.length) return null;

  return (
    <ul className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      {items.slice(0, 8).map((g, i) => (
        <li key={g.id ?? i}>
          <figure className="overflow-hidden rounded-xl border border-outline-variant bg-surface shadow-level1">
            <div className="gonjong-clip aspect-square overflow-hidden bg-surface-variant">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={g.gambar_url as string}
                alt={g.nama ?? "Foto galeri"}
                className="h-full w-full object-cover transition-transform duration-500 hover:scale-105"
                loading="lazy"
              />
            </div>
            {g.nama && (
              <figcaption className="truncate px-3 py-2 text-xs font-medium text-on-surface-variant">
                {g.nama}
              </figcaption>
            )}
          </figure>
        </li>
      ))}
    </ul>
  );
}
