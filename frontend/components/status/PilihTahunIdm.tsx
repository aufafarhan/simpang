"use client";

import { useRouter } from "next/navigation";
import { useState, useTransition } from "react";
import Icon from "@/components/ui/Icon";

/**
 * Pemilih tahun IDM berbentuk dropdown.
 *
 * Dibungkus <form method="GET"> supaya tetap berfungsi walau JavaScript mati:
 * tombol "Tampilkan" akan mengirim form secara biasa. Bila JS aktif, perubahan
 * pilihan langsung memicu navigasi dan tombolnya disembunyikan.
 */
export default function PilihTahunIdm({
  tahun,
  tahunTersedia,
}: {
  tahun: number;
  tahunTersedia: number[];
}) {
  const router = useRouter();
  const [memuat, mulaiTransisi] = useTransition();
  const [nilai, setNilai] = useState(tahun);

  if (tahunTersedia.length < 2) return null;

  function pindah(t: number) {
    setNilai(t);
    mulaiTransisi(() => router.push(`/status-idm?tahun=${t}`));
  }

  return (
    <form
      action="/status-idm"
      method="GET"
      className="mb-8 flex flex-wrap items-center gap-3"
      onSubmit={(e) => {
        e.preventDefault();
        pindah(nilai);
      }}
    >
      <label
        htmlFor="pilih-tahun-idm"
        className="flex items-center gap-1.5 text-sm font-medium text-on-surface-variant"
      >
        <Icon name="calendar_today" size={16} />
        Tahun penilaian
      </label>

      <div className="relative">
        <select
          id="pilih-tahun-idm"
          name="tahun"
          value={nilai}
          disabled={memuat}
          onChange={(e) => pindah(Number(e.target.value))}
          className="min-h-11 cursor-pointer appearance-none rounded-lg border border-outline-variant bg-surface py-2 pl-4 pr-10 text-sm font-semibold text-on-surface transition focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary-container disabled:opacity-60"
        >
          {tahunTersedia
            .slice()
            .sort((a, b) => b - a)
            .map((t) => (
              <option key={t} value={t}>
                {t}
              </option>
            ))}
        </select>

        <Icon
          name={memuat ? "progress_activity" : "expand_more"}
          size={20}
          className={`pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 text-on-surface-variant ${
            memuat ? "animate-spin" : ""
          }`}
        />
      </div>

      {/* Hanya dirender browser bila JavaScript mati */}
      <noscript>
        <button
          type="submit"
          className="min-h-11 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary"
        >
          Tampilkan
        </button>
      </noscript>

      <span aria-live="polite" className="sr-only">
        {memuat ? "Memuat data tahun " + nilai : ""}
      </span>
    </form>
  );
}
