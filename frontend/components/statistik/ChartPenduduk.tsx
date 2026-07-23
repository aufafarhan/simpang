"use client";

import type { StatistikPenduduk } from "@/lib/types";

// Grafik batang sederhana berbasis CSS (tanpa library eksternal).
// Bisa diganti Recharts/Chart.js nanti bila perlu (lihat TDD §5.1).
export default function ChartPenduduk({ data }: { data: StatistikPenduduk }) {
  const maks = Math.max(...data.kategori.map((k) => k.jumlah), 1);

  return (
    <div className="rounded-xl border border-slate-200 p-6">
      <h3 className="mb-1 font-bold text-slate-900">{data.judul}</h3>
      <p className="mb-4 text-sm text-slate-500">
        Total: {data.total.toLocaleString("id-ID")} jiwa
      </p>
      <div className="space-y-3">
        {data.kategori.map((k) => (
          <div key={k.label}>
            <div className="mb-1 flex justify-between text-sm">
              <span className="text-slate-700">{k.label}</span>
              <span className="font-medium text-slate-900">
                {k.jumlah.toLocaleString("id-ID")} ({k.persen}%)
              </span>
            </div>
            <div className="h-3 w-full overflow-hidden rounded-full bg-slate-100">
              <div
                className="h-full rounded-full bg-navy-700 transition-all"
                style={{ width: `${(k.jumlah / maks) * 100}%` }}
              />
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
