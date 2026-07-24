import Icon from "@/components/ui/Icon";
import type { Aparatur as TAparatur } from "@/lib/types";

/** Grid kartu aparatur nagari (dipakai sebagai section penuh di beranda). */
export default function Aparatur({ data }: { data: TAparatur[] }) {
  if (!data?.length) return null;

  return (
    <ul className="grid grid-cols-2 gap-6 sm:grid-cols-3 lg:grid-cols-4">
      {data.slice(0, 8).map((p, i) => (
        <li
          key={p.id ?? i}
          className="overflow-hidden rounded-xl border border-outline-variant bg-surface text-center shadow-level1 transition-shadow hover:shadow-level2"
        >
          <div className="aspect-square w-full overflow-hidden bg-surface-variant">
            {p.foto_url ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img
                src={p.foto_url}
                alt={`${p.jabatan ?? "Aparatur"} — ${p.nama ?? ""}`}
                className="h-full w-full object-cover object-top"
                loading="lazy"
              />
            ) : (
              <div className="flex h-full items-center justify-center text-outline">
                <Icon name="person" size={40} />
              </div>
            )}
          </div>
          <div className="p-3">
            <p className="line-clamp-2 text-sm font-semibold leading-tight text-on-surface">
              {p.nama}
            </p>
            <p className="mt-1 text-xs text-on-surface-variant">{p.jabatan}</p>
          </div>
        </li>
      ))}
    </ul>
  );
}
