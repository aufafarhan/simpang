"use client";

import { useState } from "react";
import Icon from "@/components/ui/Icon";
import type { StatistikPenduduk } from "@/lib/types";

/**
 * Grafik batang / lingkaran + tabel, meniru halaman statistik OpenSID.
 *
 * Pie digambar sebagai SVG murni (path arc), BUKAN Highcharts seperti versi PHP —
 * satu grafik statis tidak sebanding dengan ±300 KB library. Kalau nanti butuh
 * tooltip interaktif, drill-down, atau ekspor gambar, barulah pertimbangkan
 * Recharts/Highcharts.
 */

/** Warna irisan — diambil dari palet Minangkabau di globals.css lalu diputar. */
const WARNA = [
  "#154212", // hijau hutan (primary)
  "#fed65b", // emas
  "#672221", // maroon
  "#2d5a27", // hijau muda
  "#a1d494",
  "#853836",
  "#735c00",
  "#e9c349",
  "#42493e",
  "#c2c9bb",
];

/** Irisan di bawah ambang ini digabung jadi "Lainnya" agar pie tetap terbaca. */
const AMBANG_LAINNYA = 0.02;

function Pie({ data }: { data: StatistikPenduduk }) {
  const total = data.kategori.reduce((s, k) => s + k.jumlah, 0);

  if (total === 0) return null;

  // Gabungkan irisan kecil (mis. Pekerjaan punya 89 kategori).
  const besar = data.kategori.filter((k) => k.jumlah / total >= AMBANG_LAINNYA);
  const sisa = total - besar.reduce((s, k) => s + k.jumlah, 0);
  const irisan = sisa > 0 ? [...besar, { label: "Lainnya", jumlah: sisa, persen: 0 }] : besar;

  const titik = (f: number): [number, number] => {
    const a = 2 * Math.PI * f - Math.PI / 2;
    return [50 + 42 * Math.cos(a), 50 + 42 * Math.sin(a)];
  };

  let jalan = 0;

  return (
    <div className="flex flex-col items-center gap-6 md:flex-row md:items-start md:gap-10">
      <svg viewBox="0 0 100 100" className="h-64 w-64 shrink-0" role="img"
        aria-label={`Diagram lingkaran ${data.judul}`}>
        {irisan.map((k, i) => {
          const frac = k.jumlah / total;
          const mulai = jalan;
          jalan += frac;

          // Satu irisan 100% tidak bisa digambar dengan arc — pakai lingkaran.
          if (frac >= 0.9999) {
            return <circle key={i} cx="50" cy="50" r="42" fill={WARNA[0]} />;
          }

          const [x1, y1] = titik(mulai);
          const [x2, y2] = titik(jalan);
          const besarBusur = frac > 0.5 ? 1 : 0;

          return (
            <path
              key={i}
              d={`M50,50 L${x1},${y1} A42,42 0 ${besarBusur} 1 ${x2},${y2} Z`}
              fill={WARNA[i % WARNA.length]}
              stroke="#faf9f6"
              strokeWidth="0.6"
            />
          );
        })}
      </svg>

      <ul className="flex-1 space-y-2 text-sm">
        {irisan.map((k, i) => (
          <li key={i} className="flex items-center gap-3">
            <span
              className="h-3 w-3 shrink-0 rounded-sm"
              style={{ backgroundColor: WARNA[i % WARNA.length] }}
              aria-hidden
            />
            <span className="flex-1 text-on-surface-variant">{k.label}</span>
            <span className="font-medium tabular-nums text-on-surface">
              {k.jumlah.toLocaleString("id-ID")} ({((k.jumlah / total) * 100).toFixed(1)}%)
            </span>
          </li>
        ))}
      </ul>
    </div>
  );
}

function Batang({ data }: { data: StatistikPenduduk }) {
  const maks = Math.max(...data.kategori.map((k) => k.jumlah), 1);

  return (
    <div className="space-y-3">
      {data.kategori.map((k, i) => (
        <div key={i}>
          <div className="mb-1 flex justify-between gap-4 text-sm">
            <span className="text-on-surface-variant">{k.label}</span>
            <span className="shrink-0 font-medium tabular-nums text-on-surface">
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
  );
}

export default function GrafikStatistik({ data }: { data: StatistikPenduduk }) {
  const [mode, setMode] = useState<"bar" | "pie">("bar");

  const tombol = (aktif: boolean) =>
    `inline-flex min-h-11 items-center gap-2 rounded-lg px-4 text-sm font-semibold transition ${
      aktif
        ? "bg-secondary-container text-on-secondary-container"
        : "border border-outline-variant text-on-surface-variant hover:bg-surface-container"
    }`;

  return (
    <section className="rounded-2xl border border-outline-variant p-6 shadow-level1">
      <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h2 className="font-heading text-xl font-bold text-primary">
          Grafik {data.judul}
        </h2>

        <div className="flex gap-2 print:hidden">
          <button type="button" onClick={() => setMode("bar")} className={tombol(mode === "bar")}>
            <Icon name="bar_chart" size={18} />
            Bar Graph
          </button>
          <button type="button" onClick={() => setMode("pie")} className={tombol(mode === "pie")}>
            <Icon name="pie_chart" size={18} />
            Pie Graph
          </button>
        </div>
      </div>

      <p className="mb-6 text-sm text-outline">
        Total: {data.total.toLocaleString("id-ID")} jiwa
      </p>

      {mode === "bar" ? <Batang data={data} /> : <Pie data={data} />}

      {/* --- Tabel --- */}
      <h3 className="mb-3 mt-10 font-heading text-lg font-bold text-primary">
        Tabel {data.judul}
      </h3>

      <div className="overflow-x-auto rounded-xl border border-outline-variant">
        <table className="w-full min-w-[560px] border-collapse text-sm">
          <thead className="bg-primary text-on-primary">
            <tr>
              <th className="w-12 px-3 py-2 text-center font-semibold">No</th>
              <th className="px-3 py-2 text-left font-semibold">Kategori</th>
              <th className="w-24 px-3 py-2 text-right font-semibold">Laki-laki</th>
              <th className="w-24 px-3 py-2 text-right font-semibold">Perempuan</th>
              <th className="w-24 px-3 py-2 text-right font-semibold">Jumlah</th>
              <th className="w-20 px-3 py-2 text-right font-semibold">Persen</th>
            </tr>
          </thead>
          <tbody>
            {data.kategori.map((k, i) => (
              <tr
                key={i}
                className="border-t border-outline-variant odd:bg-surface even:bg-surface-container-low"
              >
                <td className="px-3 py-2 text-center tabular-nums text-on-surface-variant">
                  {i + 1}
                </td>
                <td className="px-3 py-2 text-on-surface">{k.label}</td>
                <td className="px-3 py-2 text-right tabular-nums text-on-surface-variant">
                  {(k.laki ?? 0).toLocaleString("id-ID")}
                </td>
                <td className="px-3 py-2 text-right tabular-nums text-on-surface-variant">
                  {(k.perempuan ?? 0).toLocaleString("id-ID")}
                </td>
                <td className="px-3 py-2 text-right font-medium tabular-nums text-on-surface">
                  {k.jumlah.toLocaleString("id-ID")}
                </td>
                <td className="px-3 py-2 text-right tabular-nums text-on-surface-variant">
                  {k.persen}%
                </td>
              </tr>
            ))}

            <tr className="border-t-2 border-primary bg-secondary-container font-bold">
              <td className="px-3 py-2" />
              <td className="px-3 py-2 text-on-secondary-container">JUMLAH</td>
              <td className="px-3 py-2 text-right tabular-nums text-on-secondary-container">
                {data.kategori
                  .reduce((s, k) => s + (k.laki ?? 0), 0)
                  .toLocaleString("id-ID")}
              </td>
              <td className="px-3 py-2 text-right tabular-nums text-on-secondary-container">
                {data.kategori
                  .reduce((s, k) => s + (k.perempuan ?? 0), 0)
                  .toLocaleString("id-ID")}
              </td>
              <td className="px-3 py-2 text-right tabular-nums text-on-secondary-container">
                {data.total.toLocaleString("id-ID")}
              </td>
              <td className="px-3 py-2 text-right tabular-nums text-on-secondary-container">
                100%
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  );
}
