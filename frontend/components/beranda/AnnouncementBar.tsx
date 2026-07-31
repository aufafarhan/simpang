/**
 * Bar pengumuman berjalan. Data dari `teks_berjalan` OpenSID.
 * Tidak dirender bila tidak ada pengumuman aktif.
 */
export default function AnnouncementBar({ items }: { items: unknown[] }) {
  const teks = (items ?? [])
    .map((t) => {
      const o = t as Record<string, unknown>;

      return String(o.teks ?? o.isi ?? o.judul ?? "").trim();
    })
    .filter(Boolean);

  if (teks.length === 0) return null;

  return (
    <div className="w-full overflow-hidden bg-primary py-1.5 text-on-primary">
      <div className="marquee-container mx-auto max-w-7xl px-4">
        <div className="marquee-content inline-flex items-center gap-10 text-xs font-medium tracking-wide">
          {teks.map((t, i) => (
            <span key={i} className="inline-flex items-center gap-10">
              {t}
              {i < teks.length - 1 && <span aria-hidden>•</span>}
            </span>
          ))}
        </div>
      </div>
    </div>
  );
}
