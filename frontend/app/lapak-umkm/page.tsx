import type { Metadata } from "next";
import Breadcrumb from "@/components/ui/Breadcrumb";
import LapakUmkmClient from "@/components/lapak/LapakUmkmClient";
import { getProdukLapak } from "@/lib/api";

export const metadata: Metadata = { title: "Lapak UMKM", description: "Produk unggulan karya warga Nagari Simpang." };

export default async function LapakUmkmPage() {
  const produk = await getProdukLapak();
  return <main className="mx-auto w-full max-w-6xl px-4 py-8 md:px-6 md:py-12"><Breadcrumb items={[{ label: "Beranda", href: "/" }, { label: "Potensi Desa" }, { label: "Lapak UMKM" }]} /><header className="mb-10 max-w-3xl"><p className="mb-3 text-sm font-bold uppercase tracking-widest text-secondary">Ekonomi warga</p><h1 className="mb-3 text-4xl font-bold text-primary md:text-5xl">Lapak UMKM Nagari</h1><p className="text-base leading-relaxed text-on-surface-variant md:text-lg">Dukung pertumbuhan ekonomi lokal dengan berbelanja produk-produk unggulan karya warga Nagari Simpang. Dari kerajinan tangan hingga kuliner otentik Minangkabau.</p></header><LapakUmkmClient items={produk} /></main>;
}
