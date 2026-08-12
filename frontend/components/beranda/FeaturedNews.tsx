"use client";

import Link from "next/link";
import { useState } from "react";
import Icon from "@/components/ui/Icon";
import type { ArtikelRingkas } from "@/lib/types";

/**
 * Sorotan utama — gambar besar dengan gradasi & judul di atasnya.
 *
 * Menerima BEBERAPA artikel dan bisa digeser maju-mundur. Tombol navigasi
 * hanya muncul saat kursor berada di area sorotan (group-hover), dan tetap
 * tampil saat difokus keyboard agar pengguna tanpa tetikus tidak terkunci.
 *
 * Tombol sengaja diletakkan DI LUAR <Link> pembungkus gambar — kalau di dalam,
 * mengklik panah ikut membuka artikelnya.
 */
export default function FeaturedNews({ artikel }: { artikel: ArtikelRingkas[] }) {
  const [i, setI] = useState(0);

  if (artikel.length === 0) return null;

  const a = artikel[i];
  const banyak = artikel.length > 1;

  const geser = (arah: number) =>
    setI((n) => (n + arah + artikel.length) % artikel.length);

  const tombol =
    "absolute top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-surface/90 text-on-surface shadow-level2 opacity-0 transition group-hover/sorotan:opacity-100 focus-visible:opacity-100 hover:bg-surface";

  return (
    <section className="group/sorotan relative overflow-hidden rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-level1">
      <Link href={a.url} className="group relative block h-64 w-full bg-surface-variant md:h-80">
        {a.gambar_url ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img src={a.gambar_url} alt={a.judul} className="h-full w-full object-cover" />
        ) : (
          <div className="flex h-full items-center justify-center text-outline">
            <Icon name="image" size={56} />
          </div>
        )}

        <div
          aria-hidden
          className="absolute inset-0 bg-gradient-to-t from-on-surface/90 via-on-surface/40 to-transparent"
        />

        <div className="absolute bottom-0 left-0 w-full p-6">
          <div className="mb-2 flex flex-wrap items-center gap-2">
            {a.kategori?.nama && (
              <span className="rounded bg-secondary px-2 py-1 text-xs font-medium text-on-secondary">
                {a.kategori.nama}
              </span>
            )}
            <span className="flex items-center gap-1 text-xs text-surface-container-highest">
              <Icon name="calendar_today" size={14} />
              {new Date(a.tanggal).toLocaleDateString("id-ID", {
                day: "numeric",
                month: "long",
                year: "numeric",
              })}
            </span>
          </div>

          <h3 className="line-clamp-2 font-heading text-xl font-semibold text-on-primary transition-colors group-hover:text-secondary-container md:text-2xl">
            {a.judul}
          </h3>
        </div>
      </Link>

      {banyak && (
        <>
          <button
            type="button"
            onClick={() => geser(-1)}
            aria-label="Berita sebelumnya"
            className={`${tombol} left-3`}
          >
            <Icon name="chevron_left" size={24} />
          </button>

          <button
            type="button"
            onClick={() => geser(1)}
            aria-label="Berita berikutnya"
            className={`${tombol} right-3`}
          >
            <Icon name="chevron_right" size={24} />
          </button>

          {/* Penanda posisi — ikut muncul hanya saat hover, agar tidak ramai. */}
          <div className="absolute right-4 top-4 z-10 rounded-full bg-surface/90 px-2.5 py-1 text-xs font-medium tabular-nums text-on-surface opacity-0 transition group-hover/sorotan:opacity-100">
            {i + 1}/{artikel.length}
          </div>
        </>
      )}
    </section>
  );
}
