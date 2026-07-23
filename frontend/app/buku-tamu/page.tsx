"use client";

import { useState } from "react";

// Halaman buku tamu — form kirim pesan.
// POST ke /api/v1/buku-tamu akan diaktifkan setelah endpoint backend siap (TDD §3.3).
export default function BukuTamuPage() {
  const [status, setStatus] = useState<"idle" | "sent">("idle");

  return (
    <div className="mx-auto max-w-xl px-4 py-8">
      <h1 className="mb-6 text-2xl font-bold text-slate-900">Buku Tamu</h1>

      {status === "sent" ? (
        <div className="rounded-lg bg-navy-50 p-4 text-navy-900">
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
            <label className="mb-1 block text-sm font-medium text-slate-700">Nama</label>
            <input
              required
              className="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-navy-500 focus:outline-none"
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input
              type="email"
              className="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-navy-500 focus:outline-none"
            />
          </div>
          <div>
            <label className="mb-1 block text-sm font-medium text-slate-700">Pesan</label>
            <textarea
              required
              rows={4}
              className="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-navy-500 focus:outline-none"
            />
          </div>
          <button
            type="submit"
            className="rounded-lg bg-navy-700 px-5 py-2 font-semibold text-white hover:bg-navy-900"
          >
            Kirim Pesan
          </button>
        </form>
      )}
    </div>
  );
}
