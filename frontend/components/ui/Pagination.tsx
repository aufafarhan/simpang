import Link from "next/link";
import Icon from "@/components/ui/Icon";

/** Paginasi bulat. `buatHref` menentukan tautan per halaman. */
export default function Pagination({
  page,
  totalPages,
  buatHref,
}: {
  page: number;
  totalPages: number;
  buatHref: (p: number) => string;
}) {
  if (totalPages <= 1) return null;

  const jendela = 2;
  const mulai = Math.max(1, page - jendela);
  const akhir = Math.min(totalPages, page + jendela);
  const halaman = Array.from({ length: akhir - mulai + 1 }, (_, i) => mulai + i);

  const dasar =
    "flex h-10 w-10 items-center justify-center rounded-full text-sm font-semibold transition-colors";
  const mati = `${dasar} border border-outline-variant text-outline opacity-50`;

  return (
    <nav aria-label="Navigasi halaman" className="mt-12 flex items-center justify-center gap-2">
      {page > 1 ? (
        <Link
          href={buatHref(page - 1)}
          aria-label="Halaman sebelumnya"
          className={`${dasar} border border-outline-variant text-on-surface-variant hover:bg-surface-container-low`}
        >
          <Icon name="chevron_left" size={20} />
        </Link>
      ) : (
        <span className={mati} aria-hidden>
          <Icon name="chevron_left" size={20} />
        </span>
      )}

      {mulai > 1 && <span className="px-1 text-outline">…</span>}

      {halaman.map((p) => (
        <Link
          key={p}
          href={buatHref(p)}
          aria-current={p === page ? "page" : undefined}
          className={
            p === page
              ? `${dasar} bg-secondary-container text-on-secondary-container shadow-level1`
              : `${dasar} border border-outline-variant text-on-surface-variant hover:bg-surface-container-low`
          }
        >
          {p}
        </Link>
      ))}

      {akhir < totalPages && <span className="px-1 text-outline">…</span>}

      {page < totalPages ? (
        <Link
          href={buatHref(page + 1)}
          aria-label="Halaman berikutnya"
          className={`${dasar} border border-outline-variant text-on-surface-variant hover:bg-surface-container-low`}
        >
          <Icon name="chevron_right" size={20} />
        </Link>
      ) : (
        <span className={mati} aria-hidden>
          <Icon name="chevron_right" size={20} />
        </span>
      )}
    </nav>
  );
}
