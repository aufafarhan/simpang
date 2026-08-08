import Icon from "@/components/ui/Icon";

/**
 * Misi nagari — dikutip apa adanya dari dokumen resmi nagari.
 *
 * Belum ada di database OpenSID (tidak ditemukan di `artikel`,
 * `setting_aplikasi`, maupun `teks_berjalan`), berbeda dengan Visi yang
 * tersimpan di `teks_berjalan`. Begitu perangkat nagari memasukkannya sebagai
 * artikel tipe statis lewat panel admin, daftar ini sebaiknya diganti dengan
 * data dari API lewat prop `misi` agar bisa disunting tanpa mengubah kode.
 */
const MISI_NAGARI = [
  "Menyelenggarakan Tata Pemerintah Nagari Yang Bersih, Transparan Dan Bebas Dari Korupsi, Kolusi Dan Nepotisme.",
  "Memberikan Pelayanan Yang Prima Terhadap Seluruh Lapisan Masyarakat Kapanpun Dan Dimanapun, Termasuk Upaya Penyelesaian Permasalahan Hukum.",
  "Penegakan Hukum Dan Tindakan Tegas Terhadap Perangkat Nagari Yang Melakukan Tindakan Atau Perbuatan Yang Merugikan Masyarakat Sesuai Dengan Ketentuan Dan Peraturan Yang Berlaku.",
  "System Transparansi dan Keterbukaan Publik Terhadap Pengelolaan Dana Desa Dan Dana Nagari Serta Informasi Kegiatan Nagari.",
  "Penyusunan Base Data Nagari Yang Tepat Terukur Dan Akurat Sehingga Tepat Guna Dan Tepat Sasaran.",
  "Menjalin Keharmonisan Dan Kerja Sama Yang Erat Dengan Seluruh Lembaga Yang Ada di Nagari Serta Seluruh Lapisan Masyarakat Dalam Rangka Menata Kehidupan Sosial, Adat Dan Budaya Yang Ada Ditengah Masyarakat Berdasarkan Prinsip Keadilan Dan Kebersamaan.",
];

interface VideoProfilVisiProps {
  videoUrl?: string | null;
  /** Override daftar misi, mis. bila nanti sudah diambil dari API. */
  misi?: string[];
}

/**
 * Visi & Misi memakai <details>/<summary> bawaan HTML, bukan state React —
 * tidak perlu client component, tetap bisa dibuka tanpa JS, dan aksesibilitas
 * (keyboard, screen reader) sudah ditangani browser.
 */
function Lipatan({
  ikon,
  judul,
  children,
  terbukaAwal = false,
}: {
  ikon: string;
  judul: string;
  children: React.ReactNode;
  terbukaAwal?: boolean;
}) {
  return (
    <details
      open={terbukaAwal}
      className="group rounded-2xl border border-primary-container/20 bg-primary-container/10 [&_summary::-webkit-details-marker]:hidden"
    >
      <summary className="flex min-h-11 cursor-pointer list-none items-center gap-2 p-6 md:px-8">
        <Icon name={ikon} size={20} className="shrink-0 text-secondary" />
        <h3 className="flex-1 font-heading text-xl font-bold text-primary">{judul}</h3>
        <Icon
          name="expand_more"
          size={22}
          className="shrink-0 text-primary transition-transform group-open:rotate-180"
        />
      </summary>

      <div className="px-6 pb-6 md:px-8 md:pb-8">{children}</div>
    </details>
  );
}

export default function VideoProfilVisi({
  videoUrl,
  misi = MISI_NAGARI,
}: VideoProfilVisiProps) {
  return (
    <section className="w-full border-b border-outline-variant bg-surface py-12 md:py-16">
      <div className="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">
        <div className="flex flex-col gap-8">
          {/* Header Section */}
          <div className="mx-auto max-w-2xl text-center">
            <h2 className="font-heading text-2xl font-bold text-primary md:text-3xl mb-2">
              Video Profil Nagari
            </h2>
            <p className="text-sm text-on-surface-variant md:text-base">
              Kenali lebih dekat potensi, keindahan alam, dan budaya Nagari Simpang melalui video singkat ini.
            </p>
          </div>

          {/* Grid Layout: Video (8 cols) + Visi (4 cols) */}
          <div className="grid grid-cols-1 items-center gap-6 lg:grid-cols-12">
            {/* Left: Video Player / Placeholder */}
            <div className="lg:col-span-8">
              <div className="group relative aspect-video w-full overflow-hidden rounded-2xl border border-outline-variant bg-surface-variant shadow-lg">
                {videoUrl ? (
                  <iframe
                    src={
                      videoUrl.includes("youtube.com") || videoUrl.includes("youtu.be")
                        ? videoUrl.replace("watch?v=", "embed/")
                        : videoUrl
                    }
                    title="Video Profil Nagari Simpang"
                    className="h-full w-full border-0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowFullScreen
                  />
                ) : (
                  <div className="relative flex h-full w-full items-center justify-center bg-gradient-to-br from-primary/20 via-surface-container-high to-primary/10">
                    {/* Background Pattern Overlay */}
                    <div className="absolute inset-0 bg-[radial-gradient(#154212_1px,transparent_1px)] [background-size:16px_16px] opacity-10" />

                    <div className="relative z-10 flex flex-col items-center gap-3 p-6 text-center">
                      <div className="flex h-20 w-20 items-center justify-center rounded-full bg-surface/90 text-primary shadow-xl transition-transform duration-300 group-hover:scale-110">
                        <Icon name="play_arrow" size={48} filled />
                      </div>
                      <span className="text-xs font-semibold uppercase tracking-wider text-primary/80">
                        Video Profil Nagari Simpang
                      </span>
                    </div>
                  </div>
                )}
              </div>
            </div>

            {/* Right: Visi & Misi — bisa dibuka-tutup */}
            <div className="lg:col-span-4 flex flex-col gap-4">
              <Lipatan ikon="auto_awesome" judul="Visi" terbukaAwal>
                <p className="mb-3 font-heading text-lg font-semibold italic text-on-surface">
                  &ldquo;Terwujudnya Masyarakat Nagari Simpang yang Sejahtera, Berkeadilan,
                  Berdaulat, Agamis, dan Berbudaya&rdquo;
                </p>
              </Lipatan>

              {misi && misi.length > 0 && (
                <Lipatan ikon="flag" judul="Misi">
                  <ol className="space-y-3 text-sm leading-relaxed text-on-surface-variant">
                    {misi.map((butir, i) => (
                      <li key={i} className="flex gap-3">
                        <span className="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary text-xs font-bold text-on-primary">
                          {i + 1}
                        </span>
                        <span>{butir}</span>
                      </li>
                    ))}
                  </ol>
                </Lipatan>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
