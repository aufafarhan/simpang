import type { StatistikPenduduk, StatistikPengunjung } from "@/lib/types";

/**
 * Blok data wilayah (hijau, bermotif garis diagonal).
 * Hanya menampilkan angka yang benar-benar tersedia dari API —
 * tidak ada angka karangan.
 */
export default function StatsBlock({
  penduduk,
  pengunjung,
}: {
  penduduk: StatistikPenduduk;
  pengunjung: StatistikPengunjung;
}) {
  const total = penduduk?.total ?? 0;
  const laki = penduduk?.kategori?.reduce((s, k) => s + (k.laki ?? 0), 0) ?? 0;
  const perempuan = penduduk?.kategori?.reduce((s, k) => s + (k.perempuan ?? 0), 0) ?? 0;

  return (
    <div className="relative overflow-hidden rounded-xl bg-primary p-6 text-on-primary shadow-level2">
      <div
        aria-hidden
        className="absolute inset-0 opacity-10"
        style={{
          backgroundImage:
            "repeating-linear-gradient(45deg, transparent, transparent 10px, #ffffff 10px, #ffffff 20px)",
        }}
      />

      <div className="relative z-10">
        <h3 className="mb-4 font-heading text-xl font-semibold">Data Wilayah</h3>

        <div className="border-b border-primary-container pb-3">
          <span className="mb-1 block text-xs uppercase tracking-wider text-primary-fixed-dim">
            Total Penduduk
          </span>
          <div className="flex items-baseline gap-2 text-4xl font-bold tabular-nums">
            {total.toLocaleString("id-ID")}
            <span className="text-sm font-normal opacity-80">Jiwa</span>
          </div>
        </div>

        <div className="mt-3 grid grid-cols-2 gap-3">
          <div className="border-r border-primary-container pr-3">
            <span className="mb-1 block text-xs uppercase tracking-wider text-primary-fixed-dim">
              Jorong
            </span>
            <div className="font-heading text-2xl tabular-nums">
              5
            </div>
          </div>
          <div className="pl-1">
            <span className="mb-1 block text-xs uppercase tracking-wider text-primary-fixed-dim">
              Luas Wilayah
            </span>
            <div className="font-heading text-2xl tabular-nums">
              44,96<span className="text-sm font-normal opacity-80"> km&sup2;</span>
            </div>
          </div>
        </div>

        {pengunjung && (
          <div className="mt-4 border-t border-primary-container pt-3">
            <span className="mb-1 block text-xs uppercase tracking-wider text-primary-fixed-dim">
              Total Pengunjung Situs
            </span>
            <div className="font-heading text-2xl tabular-nums">
              {pengunjung.total.toLocaleString("id-ID")}
            </div>
          </div>
        )}
      </div>
    </div>
  );
}
