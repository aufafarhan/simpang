import WidgetCard from "./WidgetCard";
import type { StatistikPenduduk as TStat } from "@/lib/types";

/** Grafik batang berbasis CSS — tanpa library eksternal. */
export default function StatistikPenduduk({ data }: { data: TStat }) {
  if (!data?.kategori?.length) return null;

  const maks = Math.max(...data.kategori.map((k) => k.jumlah), 1);

  return (
    <WidgetCard judul={data.judul ?? "Statistik Penduduk"}>
      <p className="mb-4 text-sm text-outline">
        Total{" "}
        <span className="font-semibold tabular-nums text-primary">
          {data.total.toLocaleString("id-ID")}
        </span>{" "}
        jiwa
      </p>

      <div className="space-y-3">
        {data.kategori.map((k) => (
          <div key={k.label}>
            <div className="mb-1 flex items-baseline justify-between gap-2 text-sm">
              <span className="truncate text-on-surface-variant">{k.label}</span>
              <span className="shrink-0 font-medium tabular-nums text-primary">
                {k.jumlah.toLocaleString("id-ID")}
                {k.persen ? (
                  <span className="ml-1 font-normal text-outline">({k.persen})</span>
                ) : null}
              </span>
            </div>
            <div
              className="h-2.5 w-full overflow-hidden rounded-full bg-surface-container"
              role="img"
              aria-label={`${k.label}: ${k.jumlah} jiwa`}
            >
              <div
                className="h-full rounded-full bg-primary transition-all"
                style={{ width: `${(k.jumlah / maks) * 100}%` }}
              />
            </div>
          </div>
        ))}
      </div>
    </WidgetCard>
  );
}
