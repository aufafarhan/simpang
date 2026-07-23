import type { Komentar } from "@/lib/types";

function Item({ k, level = 0 }: { k: Komentar; level?: number }) {
  return (
    <div className={level > 0 ? "ml-6 border-l border-slate-200 pl-4" : ""}>
      <div className="rounded-lg bg-slate-50 p-3">
        <div className="mb-1 flex items-center justify-between">
          <span className="text-sm font-semibold text-slate-800">{k.nama}</span>
          <span className="text-xs text-slate-400">
            {new Date(k.tanggal).toLocaleDateString("id-ID")}
          </span>
        </div>
        <p className="text-sm text-slate-600">{k.isi}</p>
      </div>
      {k.children?.length > 0 && (
        <div className="mt-2 space-y-2">
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
    return <p className="text-sm text-slate-500">Belum ada komentar.</p>;
  }
  return (
    <div className="space-y-3">
      {komentar.map((k) => (
        <Item key={k.id} k={k} />
      ))}
    </div>
  );
}
