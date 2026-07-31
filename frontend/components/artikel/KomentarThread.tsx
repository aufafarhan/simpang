import Icon from "@/components/ui/Icon";
import type { Komentar } from "@/lib/types";

function Item({ k, level = 0 }: { k: Komentar; level?: number }) {
  return (
    <div className={level > 0 ? "ml-4 border-l-2 border-outline-variant pl-4 md:ml-8" : ""}>
      <article className="rounded-xl border border-outline-variant bg-surface p-4">
        <header className="mb-2 flex items-center gap-3">
          <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-container text-on-primary-container">
            <Icon name="person" size={20} />
          </span>
          <span className="min-w-0">
            <span className="block truncate text-sm font-semibold text-on-surface">
              {k.nama}
            </span>
            <time className="block text-xs text-outline">
              {new Date(k.tanggal).toLocaleDateString("id-ID", {
                day: "numeric",
                month: "long",
                year: "numeric",
              })}
            </time>
          </span>
        </header>
        <p className="text-sm leading-relaxed text-on-surface-variant">{k.isi}</p>
      </article>

      {k.children?.length > 0 && (
        <div className="mt-3 space-y-3">
          {k.children.map((c) => (
            <Item key={c.id} k={c} level={level + 1} />
          ))}
        </div>
      )}
    </div>
  );
}

export default function KomentarThread({ komentar }: { komentar: Komentar[] }) {
  if (komentar.length === 0) {
    return (
      <p className="rounded-xl border border-dashed border-outline-variant p-6 text-center text-sm text-on-surface-variant">
        Belum ada komentar. Jadilah yang pertama berkomentar.
      </p>
    );
  }

  return (
    <div className="space-y-4">
      {komentar.map((k) => (
        <Item key={k.id} k={k} />
      ))}
    </div>
  );
}
