"use client";

import { useMemo, useState } from "react";
import Icon from "@/components/ui/Icon";
import type { ProdukLapak } from "@/lib/types";

function formatRupiah(harga: number): string { return new Intl.NumberFormat("id-ID", { style: "currency", currency: "IDR", maximumFractionDigits: 0 }).format(harga); }
function waUrl(nomor: string | null): string | null { const digit = (nomor || "").replace(/\D/g, ""); if (!digit) return null; return `https://wa.me/${digit.startsWith("0") ? `62${digit.slice(1)}` : digit}`; }

export default function LapakUmkmClient({ items }: { items: ProdukLapak[] }) {
  const [cari, setCari] = useState("");
  const [kategori, setKategori] = useState("Semua");
  const kategoriTersedia = ["Semua", ...Array.from(new Set(items.map((item) => item.kategori).filter(Boolean)))];
  const produk = useMemo(() => items.filter((item) => (kategori === "Semua" || item.kategori === kategori) && item.nama.toLowerCase().includes(cari.toLowerCase())), [cari, kategori, items]);

  return <>
    <div className="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div className="relative w-full max-w-xl"><Icon name="search" size={20} className="absolute left-3 top-1/2 -translate-y-1/2 text-on-surface-variant" /><input value={cari} onChange={(e) => setCari(e.target.value)} className="min-h-11 w-full rounded-lg border border-outline-variant bg-surface py-3 pl-10 pr-4 text-sm text-on-surface placeholder:text-outline" placeholder="Cari produk (mis. Rendang, Songket)…" aria-label="Cari produk UMKM" /></div>
      <div className="flex gap-2 overflow-x-auto pb-1">{kategoriTersedia.map((item) => <button key={item} onClick={() => setKategori(item)} className={`min-h-11 shrink-0 rounded-lg px-4 text-sm font-semibold transition ${kategori === item ? "bg-primary text-on-primary" : "border border-outline-variant bg-surface text-on-surface-variant hover:bg-surface-container"}`}>{item}</button>)}</div>
    </div>
    {produk.length ? <div className="grid gap-6 md:grid-cols-2 xl:grid-cols-3">{produk.map((item) => { const whatsapp = waUrl(item.telepon); return <article key={item.id} className="group flex overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-level1 transition duration-300 hover:-translate-y-1 hover:shadow-level2"><div className="flex w-full flex-col"><div className="relative m-1 h-60 overflow-hidden bg-surface-container gonjong-clip">{item.foto_url ? <img src={item.foto_url} alt={item.nama} className="h-full w-full object-cover transition duration-500 group-hover:scale-105" /> : <div className="flex h-full items-center justify-center text-outline"><Icon name="image" size={46} /></div>}<span className="absolute left-3 top-3 rounded-md bg-secondary-container px-2 py-1 text-xs font-bold text-on-secondary-container">{item.kategori}</span></div><div className="flex flex-1 flex-col p-5"><p className="mb-2 text-sm text-on-surface-variant">{item.pelapak}</p><h2 className="mb-2 text-xl font-bold text-primary">{item.nama}</h2>{item.deskripsi && <p className="mb-5 line-clamp-2 text-sm text-on-surface-variant">{item.deskripsi}</p>}<div className="mt-auto flex items-center justify-between border-t-2 border-secondary-container pt-4"><strong className="font-heading text-2xl text-primary">{formatRupiah(item.harga)}{item.satuan ? <span className="ml-1 text-sm font-medium">/{item.satuan}</span> : null}</strong>{whatsapp ? <a href={whatsapp} target="_blank" rel="noreferrer" className="inline-flex min-h-11 items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-on-primary transition hover:bg-primary-container"><Icon name="forum" size={18} />Hubungi</a> : <span className="text-sm text-on-surface-variant">Kontak belum tersedia</span>}</div></div></div></article>; })}</div> : <div className="rounded-xl border border-outline-variant bg-surface-container-low p-12 text-center"><Icon name="inventory_2" size={48} className="mb-3 text-outline" /><p className="font-semibold text-on-surface">{items.length ? "Produk tidak ditemukan." : "Belum ada produk UMKM yang tersedia."}</p>{items.length ? <button onClick={() => { setCari(""); setKategori("Semua"); }} className="mt-4 min-h-11 text-sm font-semibold text-primary underline">Tampilkan semua produk</button> : null}</div>}
  </>;
}
