import type { Metadata } from "next";
import Link from "next/link";
import Icon from "@/components/ui/Icon";
import { getAparatur } from "@/lib/api";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx —
  // jangan diulang di sini agar tidak jadi "… — Simpang — Simpang".
  title: "Pemerintah Nagari",
  description:
    "Aparatur dan perangkat Nagari Simpang yang melayani masyarakat dan mewujudkan tata kelola desa yang transparan dan modern.",
};

export default async function PemerintahNagariPage() {
  const { wali_nagari, perangkat } = await getAparatur();

  return (
    <main className="mx-auto flex-grow w-full max-w-6xl px-4 py-8 md:px-6 md:py-12">
      {/* Breadcrumbs */}
      <nav aria-label="Breadcrumb" className="mb-6 flex items-center text-xs font-semibold text-primary">
        <Link href="/" className="hover:underline">
          Beranda
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <span className="text-on-surface-variant">Pemerintahan</span>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <span aria-current="page" className="text-secondary font-bold">
          Pemerintah Nagari
        </span>
      </nav>

      {/* Page Header */}
      <div className="mb-10 max-w-3xl">
        <h1 className="font-heading text-3xl font-bold text-primary md:text-5xl mb-4">
          Pemerintah Nagari
        </h1>
        <p className="border-l-4 border-secondary pl-4 text-base text-on-surface-variant md:text-lg">
          Mengenal lebih dekat jajaran aparatur Nagari Simpang yang berdedikasi tinggi dalam melayani masyarakat, menjaga tradisi, dan mewujudkan tata kelola desa yang transparan, modern, dan berlandaskan adat budaya Minangkabau.
        </p>
      </div>

      {/* Featured Section: Wali Nagari */}
      {wali_nagari && (
        <section className="mb-14 relative">
          <div className="group relative flex flex-col overflow-hidden rounded-2xl border border-outline-variant bg-surface-container-lowest shadow-sm transition-shadow duration-300 hover:shadow-md md:flex-row">
            <div className="h-2 w-full bg-primary md:h-auto md:w-2" />
            <div className="relative aspect-square w-full shrink-0 overflow-hidden bg-surface-container-low md:w-1/3">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={wali_nagari.foto_url || "/placeholder-avatar.jpg"}
                alt={wali_nagari.nama}
                className="h-full w-full object-cover"
              />
              <div className="absolute bottom-0 left-0 flex w-full justify-end bg-gradient-to-t from-black/70 to-transparent p-4 md:hidden">
                <span className="rounded-full bg-primary/90 px-3 py-1 text-xs font-semibold text-white shadow-sm backdrop-blur-sm">
                  {wali_nagari.jabatan}
                </span>
              </div>
            </div>

            <div className="flex flex-col justify-center flex-grow p-6 md:p-10">
              <div className="mb-3 hidden self-start rounded-full border border-primary/10 bg-primary-container px-4 py-1.5 text-xs font-semibold text-on-primary-container shadow-sm md:inline-block">
                {wali_nagari.jabatan}
              </div>

              <h2 className="font-heading text-2xl font-bold text-primary md:text-4xl mb-2">
                {wali_nagari.nama}
              </h2>
              <div className="mb-6 h-[2px] w-16 bg-secondary" />

              <div className="mb-6 flex-grow space-y-4">
                {wali_nagari.nip_niap && (
                  <div className="flex items-start gap-3">
                    <div className="flex shrink-0 items-center justify-center rounded-full bg-surface-container p-2 text-tertiary">
                      <Icon name="badge" size={20} />
                    </div>
                    <div>
                      <p className="text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">
                        NIAP / NIP
                      </p>
                      <p className="text-sm font-medium text-on-surface">
                        {wali_nagari.nip_niap}
                      </p>
                    </div>
                  </div>
                )}

                {wali_nagari.masa_jabatan && (
                  <div className="flex items-start gap-3">
                    <div className="flex shrink-0 items-center justify-center rounded-full bg-surface-container p-2 text-tertiary">
                      <Icon name="assignment_ind" size={20} />
                    </div>
                    <div>
                      <p className="text-[11px] uppercase tracking-wider text-on-surface-variant font-semibold">
                        Masa Jabatan
                      </p>
                      <p className="text-sm font-medium text-on-surface">
                        {wali_nagari.masa_jabatan}
                      </p>
                    </div>
                  </div>
                )}

                {wali_nagari.deskripsi && (
                  <p className="mt-4 text-sm text-on-surface-variant leading-relaxed">
                    {wali_nagari.deskripsi}
                  </p>
                )}
              </div>
            </div>
          </div>
        </section>
      )}

      {/* Grid Section: Perangkat Nagari */}
      <section>
        <div className="mb-8 flex items-center justify-between border-b-2 border-surface-variant pb-4">
          <h3 className="relative font-heading text-2xl font-bold text-primary">
            Jajaran Perangkat Nagari
            <span className="absolute -bottom-[18px] left-0 h-[4px] w-1/2 rounded-t-sm bg-secondary" />
          </h3>
        </div>

        {perangkat.length === 0 ? (
          <div className="rounded-xl border border-outline-variant bg-surface-container-low p-8 text-center text-on-surface-variant">
            <p className="text-base">Data perangkat nagari lainnya belum tersedia.</p>
          </div>
        ) : (
          <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            {perangkat.map((item) => (
              <div
                key={item.id}
                className="group flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
              >
                <div className="relative aspect-square w-full overflow-hidden bg-surface-container">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={item.foto_url || "/placeholder-avatar.jpg"}
                    alt={item.nama}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100" />
                </div>

                <div className="relative flex flex-col flex-grow p-5">
                  <div className="absolute -top-6 right-4 flex h-12 w-12 items-center justify-center rounded-full border-2 border-surface-container-lowest bg-primary text-on-primary shadow-lg">
                    <Icon name="person" size={20} />
                  </div>

                  <span className="text-[11px] font-semibold uppercase tracking-widest text-tertiary mb-1">
                    {item.jabatan}
                  </span>
                  <h4 className="font-heading text-lg font-bold text-primary mb-1">
                    {item.nama}
                  </h4>
                  {item.nip_niap && (
                    <p className="mb-4 text-xs text-on-surface-variant flex items-center gap-1 opacity-80">
                      <Icon name="pin" size={14} />
                      <span>{item.nip_niap}</span>
                    </p>
                  )}
                </div>
              </div>
            ))}
          </div>
        )}
      </section>
    </main>
  );
}
