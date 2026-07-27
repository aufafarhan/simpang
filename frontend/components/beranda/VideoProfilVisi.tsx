import Icon from "@/components/ui/Icon";

interface VideoProfilVisiProps {
  videoUrl?: string | null;
}

export default function VideoProfilVisi({ videoUrl }: VideoProfilVisiProps) {
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

            {/* Right: Visi Nagari Card */}
            <div className="lg:col-span-4 flex flex-col gap-4">
              <div className="rounded-2xl border border-primary-container/20 bg-primary-container/10 p-6 md:p-8">
                <div className="mb-3 flex items-center gap-2">
                  <Icon name="auto_awesome" size={20} className="text-secondary" />
                  <h3 className="font-heading text-xl font-bold text-primary">
                    Visi Nagari
                  </h3>
                </div>

                <p className="font-heading text-lg font-semibold italic text-on-surface mb-3">
                  &ldquo;Alam Takambang Jadi Guru&rdquo;
                </p>

                <p className="text-sm text-on-surface-variant leading-relaxed">
                  Filosofi ini menjadi landasan kami dalam membangun Nagari Simpang, di mana alam dan kearifan lokal menjadi sumber inspirasi utama dalam inovasi dan pembangunan berkelanjutan.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
