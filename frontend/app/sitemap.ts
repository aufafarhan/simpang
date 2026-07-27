import type { MetadataRoute } from "next";
import { getArtikel } from "@/lib/api";

// Sitemap dinamis untuk SEO (TDD §6). Menyertakan halaman statis + artikel.
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const base = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";

  const statis: MetadataRoute.Sitemap = [
    "",
    "/profil",
    "/statistik",
    "/berita",
    "/galeri",
    "/pembangunan",
    "/lapak-umkm",
    "/pengaduan",
    "/dokumen",
    "/buku-tamu",
  ].map((path) => ({ url: `${base}${path}`, changeFrequency: "weekly", priority: 0.7 }));

  const { items } = await getArtikel(1);
  const artikel: MetadataRoute.Sitemap = items.map((a) => ({
    url: `${base}${a.url}`,
    lastModified: new Date(a.tanggal),
    changeFrequency: "monthly",
    priority: 0.6,
  }));

  return [...statis, ...artikel];
}
