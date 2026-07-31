"use client";

import type { StatistikPenduduk } from "@/lib/types";

// Grafik batang sederhana berbasis CSS (tanpa library eksternal).
// Bisa diganti Recharts/Chart.js nanti bila perlu (lihat TDD §5.1).
export default function ChartPenduduk({ data }: { data: StatistikPenduduk }) {
  const maks = Math.max(...data.kategori.map((k) => k.jumlah), 1);

  return (
    <div className="rounded-xl border border-outline-variant p-6">
      <h3 className="mb-1 font-bold text-on-surface">{data.judul}</h3>
      <p className="mb-4 text-sm text-outline">
        Total: {data.total.toLocaleString("id-ID")} jiwa
      </p>
      <div className="space-y-3">
        {data.kategori.map((k) => (
          <div key={k.label}>
            <div className="mb-1 flex justify-between text-sm">
              <span className="text-on-surface-variant">{k.label}</span>
              <span className="font-medium text-on-surface">
                {k.jumlah.toLocaleString("id-ID")} ({k.persen}%)
              </span>
            </div>
            <div className="h-3 w-full overflow-hidden rounded-full bg-surface-container">
              <div
                className="h-full rounded-full bg-primary transition-all"
                style={{ width: `${(k.jumlah / maks) * 100}%` }}
              />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
