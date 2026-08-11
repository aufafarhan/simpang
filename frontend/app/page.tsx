import Link from "next/link";
import ArtikelCard from "@/components/artikel/ArtikelCard";
import AnnouncementBar from "@/components/beranda/AnnouncementBar";
import FeaturedNews from "@/components/beranda/FeaturedNews";
import Hero from "@/components/beranda/Hero";
import QuickShortcuts from "@/components/beranda/QuickShortcuts";
import SectionHeading from "@/components/beranda/SectionHeading";
import StatsBlock from "@/components/beranda/StatsBlock";
import VideoProfilVisi from "@/components/beranda/VideoProfilVisi";
import { AgendaMendatang, JamLayanan } from "@/components/beranda/SidebarWidgets";
import Aparatur from "@/components/widgets/Aparatur";
import ArsipArtikel from "@/components/widgets/ArsipArtikel";
import Galeri from "@/components/widgets/Galeri";
import Peta from "@/components/widgets/Peta";
import Icon from "@/components/ui/Icon";
import { getBeranda, getProfilDesa } from "@/lib/api";

export default async function Beranda() {
  const [profil, beranda] = await Promise.all([getProfilDesa(), getBeranda(1)]);

  const artikel = beranda?.artikel.items ?? [];
  const headline = beranda?.headline ?? null;
  const w = beranda?.widgets;
  const kepala = w?.aparatur?.[0] ?? null;

  return (
    <>
      <AnnouncementBar items={beranda?.teks_berjalan ?? []} />

      <Hero profil={profil} kepala={kepala} latar={beranda?.latar_website ?? null} />

      <VideoProfilVisi
      videoUrl="" />

      {/* GRID UTAMA 12 KOLOM */}
      <div className="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-16 md:px-6 lg:grid-cols-12 lg:px-8">
        {/* KOLOM KIRI */}
        <div className="flex flex-col gap-16 lg:col-span-8">
          <QuickShortcuts />

          {headline && <FeaturedNews artikel={headline} />}

          {/* Berita terbaru */}
          <section>
            <SectionHeading
              icon="newspaper"
              aksi={
                <Link
                  href="/berita"
                  className="flex items-center gap-1 text-sm font-semibold text-secondary transition-colors hover:text-primary"
                >
                  Lihat Semua
                  <Icon name="arrow_forward" size={16} />
                </Link>
              }
            >
              Berita Terbaru
            </SectionHeading>

            {artikel.length === 0 ? (
              <p className="rounded-xl border border-dashed border-outline-variant p-8 text-center text-on-surface-variant">
                Belum ada berita untuk ditampilkan.
              </p>
            ) : (
              <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                {artikel.map((a) => (
                  <ArtikelCard key={a.id} artikel={a} />
                ))}
              </div>
            )}
          </section>

          {/* Aparatur & Galeri */}
          {w && (
            <section>
              <SectionHeading icon="groups">Pemerintah Nagari</SectionHeading>
              <Aparatur data={w.aparatur} />
            </section>
          )}

          {w && !!w.galeri?.length && (
            <section>
              <SectionHeading icon="photo_library">Galeri Kegiatan</SectionHeading>
              <Galeri data={w.galeri} />
            </section>
          )}
        </div>

        {/* SIDEBAR */}
        <aside className="flex flex-col gap-6 lg:col-span-4">
          {w && (
            <>
              <StatsBlock
                penduduk={w.statistik_penduduk}
                pengunjung={w.statistik_pengunjung}
              />
              <JamLayanan data={w.jam_kerja} />
              <AgendaMendatang data={w.agenda} />
              <Peta peta={profil.peta} namaDesa={profil.nama_desa} />
              <ArsipArtikel data={w.arsip} />
            </>
          )}
        </aside>
      </div>
    </>
  );
}
