"use client";

import { useRouter } from "next/navigation";
import { useTransition } from "react";
import Icon from "@/components/ui/Icon";

/**
 * Dropdown Tahun & Jenis Peraturan, meniru halaman Produk Hukum OpenSID.
 *
 * Dibungkus <form method="GET"> supaya tetap berfungsi tanpa JavaScript —
 * tombol Terapkan muncul hanya di <noscript>. Dengan JS, memilih dropdown
 * langsung menavigasi.
 */
export default function FilterRegulasi({
  jenis,
  tahun,
  kategori,
  tahunTersedia,
  jenisPeraturan,
}: {
  jenis: string;
  tahun?: string;
  kategori?: string;
  tahunTersedia: number[];
  jenisPeraturan: { id: number; nama: string }[];
}) {
  const router = useRouter();
  const [menunggu, mulai] = useTransition();

  function pindah(ubah: { tahun?: string; kategori?: string }) {
    const q = new URLSearchParams({ jenis });
    const t = ubah.tahun ?? tahun ?? "";
    const k = ubah.kategori ?? kategori ?? "";

    if (t) q.set("tahun", t);
    if (k) q.set("kategori", k);

    mulai(() => router.push(`/regulasi?${q}`));
  }

  const kelas =
    "min-h-11 w-full rounded-lg border border-outline-variant bg-surface px-3 text-sm text-on-surface transition focus:border-primary";

  return (
    <form method="GET" action="/regulasi" className="mb-6 grid gap-4 sm:grid-cols-2 lg:max-w-2xl">
      <input type="hidden" name="jenis" value={jenis} />

      <label className="block">
        <span className="mb-1.5 block text-sm font-medium text-on-surface">Tahun</span>
        <select
          name="tahun"
          defaultValue={tahun ?? ""}
          onChange={(e) => pindah({ tahun: e.target.value })}
          disabled={menunggu}
          className={kelas}
        >
          <option value="">Semua</option>
          {tahunTersedia.map((t) => (
            <option key={t} value={String(t)}>
              {t}
            </option>
          ))}
        </select>
      </label>

      {jenisPeraturan.length > 0 && (
        <label className="block">
          <span className="mb-1.5 block text-sm font-medium text-on-surface">
            Jenis Peraturan
          </span>
          <select
            name="kategori"
            defaultValue={kategori ?? ""}
            onChange={(e) => pindah({ kategori: e.target.value })}
            disabled={menunggu}
            className={kelas}
          >
            <option value="">Semua</option>
            {jenisPeraturan.map((j) => (
              <option key={j.id} value={String(j.id)}>
                {j.nama}
              </option>
            ))}
          </select>
        </label>
      )}

      <noscript>
        <button
          type="submit"
          className="inline-flex min-h-11 items-center gap-2 rounded-lg bg-primary px-4 text-sm font-semibold text-on-primary"
        >
          <Icon name="filter_alt" size={18} />
          Terapkan
        </button>
      </noscript>
    </form>
  );
}
