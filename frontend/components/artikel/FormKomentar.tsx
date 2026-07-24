"use client";

import { useState } from "react";
import Icon from "@/components/ui/Icon";

/**
 * Form kirim komentar.
 *
 * CATATAN: endpoint `POST /api/v1/artikel/{id}/komentar` BELUM dibuat di backend
 * (OpenSID memakai `POST /add_comment/{id}` dengan captcha). Sampai endpoint itu
 * tersedia, form hanya menampilkan pesan — tidak berpura-pura berhasil terkirim.
 */
export default function FormKomentar({ idArtikel }: { idArtikel: number }) {
  const [pesan, setPesan] = useState<string | null>(null);

  const input =
    "w-full min-h-11 rounded-lg border border-outline-variant bg-surface p-3 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary-container";
  const label = "mb-1 block text-xs font-medium text-on-surface-variant";

  return (
    <form
      className="space-y-4"
      onSubmit={(e) => {
        e.preventDefault();
        setPesan(
          "Pengiriman komentar belum aktif — endpoint backend belum tersedia. Komentar Anda tidak terkirim.",
        );
      }}
    >
      <input type="hidden" name="id_artikel" value={idArtikel} />

      <div className="grid gap-4 md:grid-cols-2">
        <div>
          <label className={label} htmlFor="k-nama">
            Nama *
          </label>
          <input id="k-nama" name="nama" type="text" required className={input} />
        </div>
        <div>
          <label className={label} htmlFor="k-email">
            Email *
          </label>
          <input id="k-email" name="email" type="email" required className={input} />
        </div>
      </div>

      <div>
        <label className={label} htmlFor="k-isi">
          Komentar *
        </label>
        <textarea id="k-isi" name="isi" rows={4} required className={input} />
      </div>

      {pesan && (
        <p
          role="status"
          className="flex items-start gap-2 rounded-lg border border-outline-variant bg-surface p-3 text-sm text-on-surface-variant"
        >
          <Icon name="info" size={18} className="mt-0.5 shrink-0 text-secondary" />
          {pesan}
        </p>
      )}

      <button
        type="submit"
        className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-on-primary transition hover:bg-primary-container hover:text-on-primary-container"
      >
        <Icon name="send" size={18} />
        Kirim Komentar
      </button>

      <p className="text-xs text-outline">
        Komentar akan ditinjau admin nagari sebelum ditampilkan.
      </p>
    </form>
  );
}
