import Link from "next/link";
import type { ArtikelRingkas } from "@/lib/types";

function formatTanggal(iso: string): string {
  try {
    return new Date(iso).toLocaleDateString("id-ID", {
      day: "numeric",
      month: "long",
      year: "numeric",
    });
  } catch {
    return iso;
  }
}

export default function ArtikelCard({ artikel }: { artikel: ArtikelRingkas }) {
  return (
    <article className="flex flex-col overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
      <div className="flex aspect-video items-center justify-center bg-slate-100 text-slate-400">
        {artikel.gambar_url ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={artikel.gambar_url}
            alt={artikel.judul}
            className="h-full w-full object-cover"
          />
        ) : (
          <span className="text-sm">Tanpa gambar</span>
        )}
      </div>
      <div className="flex flex-1 flex-col p-4">
        {artikel.kategori && (
          <span className="mb-2 w-fit rounded bg-navy-50 px-2 py-0.5 text-xs font-medium text-navy-700">
            {artikel.kategori.nama}
          </span>
        )}
        <h3 className="mb-1 font-bold leading-snug text-slate-900">
          <Link href={artikel.url} className="hover:text-navy-500">
            {artikel.judul}
          </Link>
        </h3>
        <p className="mb-3 line-clamp-2 text-sm text-slate-600">{artikel.ringkasan}</p>
        <div className="mt-auto flex items-center justify-between text-xs text-slate-400">
          <span>{formatTanggal(artikel.tanggal)}</span>
          <span>{artikel.dilihat}× dilihat</span>
        </div>
      </div>
    </article>
  );
}
