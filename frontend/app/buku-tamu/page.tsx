"use client";

import { useState } from "react";

// Halaman buku tamu — form kirim pesan.
// POST ke /api/v1/buku-tamu akan diaktifkan setelah endpoint backend siap (TDD §3.3).
export default function BukuTamuPage() {
  const [status, setStatus] = useState<"idle" | "sent">("idle");

  return (
    <div className="mx-auto max-w-xl px-4 py-8">
      <h1 className="mb-6 text-2xl font-bold text-on-surface">Buku Tamu</h1>

      {status === "sent" ? (
        <div className="rounded-lg bg-surface-container p-4 text-primary">
          Terima kasih! Pesan Anda akan ditinjau oleh admin desa sebelum ditampilkan.
        </div>
      ) : (
        <form
          className="space-y-4"
          onSubmit={(e) => {
            e.preventDefault();
            // TODO: kirim ke API OpenSID: POST ${NEXT_PUBLIC_API_URL}/buku-tamu
            setStatus("sent");
          }}
        >
          <div>
            <label className="mb-1 block text-sm font-medium text-on-surface-variant">Nama</label>
            <input
              required
              className="w-full rounded-lg border border-outline-variant px-3 py-2 focus:border-primary focus:outline-none"
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-on-surface-variant">Email</label>
            <input
              type="email"
              className="w-full rounded-lg border border-outline-variant px-3 py-2 focus:border-primary focus:outline-none"
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-on-surface-variant">Pesan</label>
            <textarea
              required
              rows={4}
              className="w-full rounded-lg border border-outline-variant px-3 py-2 focus:border-primary focus:outline-none"
            />
          </div>
          <button
            type="submit"
            className="min-h-11 cursor-pointer rounded-lg bg-primary px-5 py-2 font-semibold text-on-primary transition hover:bg-primary-container"
          >
            Kirim Pesan
          </button>
        </form>
      )}
    </div>
  );
}
