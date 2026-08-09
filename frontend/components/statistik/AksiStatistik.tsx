"use client";

import Icon from "@/components/ui/Icon";
import type { StatistikPenduduk } from "@/lib/types";

/**
 * Tombol Cetak & Unduh untuk tabel statistik.
 *
 * Cetak memakai window.print() bawaan browser — gaya cetaknya diatur lewat
 * @media print di globals.css, tanpa library. Unduh membuat CSV dari data yang
 * sudah ada di halaman (Blob + URL.createObjectURL), jadi tidak perlu endpoint
 * ekspor maupun dependensi tambahan.
 */
export default function AksiStatistik({ data }: { data: StatistikPenduduk }) {
  function unduhCsv() {
    // Titik koma sebagai pemisah + BOM UTF-8: Excel versi Indonesia membaca
    // koma sebagai desimal, sehingga CSV berpemisah koma jadi berantakan.
    const baris = [
      ["No", "Kategori", "Laki-laki", "Perempuan", "Jumlah", "Persen"],
      ...data.kategori.map((k, i) => [
        String(i + 1),
        k.label ?? "",
        String(k.laki ?? 0),
        String(k.perempuan ?? 0),
        String(k.jumlah),
        `${k.persen}%`,
      ]),
      ["", "JUMLAH", "", "", String(data.total), "100%"],
    ];

    const csv = baris
      .map((r) => r.map((sel) => `"${String(sel).replace(/"/g, '""')}"`).join(";"))
      .join("\r\n");

    const url = URL.createObjectURL(
      new Blob(["﻿" + csv], { type: "text/csv;charset=utf-8;" }),
    );

    const a = document.createElement("a");
    a.href = url;
    a.download = `statistik-${data.judul.toLowerCase().replace(/[^a-z0-9]+/g, "-")}.csv`;
    a.click();
    URL.revokeObjectURL(url);
  }

  return (
    <div className="mb-4 flex flex-wrap gap-2 print:hidden">
      <button
        type="button"
        onClick={() => window.print()}
        className="inline-flex min-h-11 items-center gap-2 rounded-lg border border-outline-variant px-4 text-sm font-medium text-on-surface transition hover:bg-surface-container"
      >
        <Icon name="print" size={18} />
        Cetak
      </button>

      <button
        type="button"
        onClick={unduhCsv}
        className="inline-flex min-h-11 items-center gap-2 rounded-lg border border-outline-variant px-4 text-sm font-medium text-on-surface transition hover:bg-surface-container"
      >
        <Icon name="download" size={18} />
        Unduh CSV
      </button>
    </div>
  );
}
