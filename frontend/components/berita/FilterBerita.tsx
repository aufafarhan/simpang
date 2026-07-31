"use client";

import Link from "next/link";
import { useRouter, useSearchParams } from "next/navigation";
import { useState } from "react";
import Icon from "@/components/ui/Icon";
import type { KategoriRingkas } from "@/lib/types";

/** Kolom pencarian + chip kategori. Mengubah query string halaman /berita. */
export default function FilterBerita({
  kategori,
  aktif,
  cari,
}: {
  kategori: KategoriRingkas[];
  aktif?: string;
  cari?: string;
}) {
  const router = useRouter();
  const params = useSearchParams();
  const [nilai, setNilai] = useState(cari ?? "");

  function submit(e: React.FormEvent) {
    e.preventDefault();
    const q = new URLSearchParams(params.toString());
    q.delete("page");
    if (nilai.trim()) q.set("cari", nilai.trim());
    else q.delete("cari");
    router.push(`/berita?${q}`);
  }

  function hrefKategori(slug?: string): string {
    const q = new URLSearchParams();
    if (cari) q.set("cari", cari);
    if (slug) q.set("kategori", slug);
    const s = q.toString();

    return s ? `/berita?${s}` : "/berita";
  }

  const chip =
    "whitespace-nowrap rounded-full px-6 py-2 text-sm font-semibold transition-all active:scale-95";

  return (
    <>
      <form onSubmit={submit} className="relative w-full md:w-72" role="search">
        <label htmlFor="cari-berita" className="sr-only">
          Cari berita
        </label>
        <input
          id="cari-berita"
          type="search"
          value={nilai}
          onChange={(e) => setNilai(e.target.value)}
          placeholder="Cari berita..."
          className="w-full rounded-full border border-outline-variant bg-surface-container-lowest py-3 pl-10 pr-4 text-base text-on-surface transition-shadow placeholder:text-on-surface-variant focus:outline-none focus:ring-2 focus:ring-primary-container"
        />
        <Icon
          name="search"
          className="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant"
        />
      </form>

      <div className="mt-8 flex gap-2 overflow-x-auto pb-4">
        <Link
          href={hrefKategori()}
          className={`${chip} ${
            !aktif
              ? "bg-primary text-on-primary shadow-level1"
              : "border border-outline-variant text-on-surface-variant hover:bg-surface-container-low"
          }`}
        >
          Semua
        </Link>

        {kategori.map((k) => (
          <Link
            key={k.slug}
            href={hrefKategori(k.slug)}
            className={`${chip} ${
              aktif === k.slug
                ? "bg-primary text-on-primary shadow-level1"
                : "border border-outline-variant text-on-surface-variant hover:bg-surface-container-low"
            }`}
          >
            {k.nama}
          </Link>
        ))}
      </div>
    </>
  );
}
