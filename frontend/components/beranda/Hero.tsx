import Link from "next/link";
import Icon from "@/components/ui/Icon";
import type { Aparatur, ProfilDesa } from "@/lib/types";

/**
 * Hero beranda — mengikuti layout Stitch: kolom kiri narasi + CTA,
 * kolom kanan potret kepala nagari dengan kartu info melayang.
 */
export default function Hero({
  profil,
  kepala,
  latar,
}: {
  profil: ProfilDesa;
  kepala: Aparatur | null;
  /** Latar website dari pengaturan OpenSID; null bila belum diunggah. */
  latar?: string | null;
}) {
  const wilayah = [profil.kecamatan, profil.kabupaten, profil.provinsi]
    .filter((x) => x && x !== "-")
    .join(", ");

  return (
    <section
      className={`relative w-full overflow-hidden border-b border-outline-variant pb-20 pt-12 ${
        latar ? "" : "bg-surface-container-low"
      }`}
    >
      {latar && (
        <>
          {/*
            Latar dipasang sebagai div ber-background, bukan <Image>, karena
            fungsinya dekoratif dan ukurannya mengikuti tinggi section.
            Dipakai <img> tersembunyi? Tidak perlu — aria-hidden sudah cukup.
          */}
          <div
            aria-hidden
            className="absolute inset-0 -z-20 bg-cover bg-center"
            style={{ backgroundImage: `url("${latar}")` }}
          />
          {/*
            Lapisan peneduh: tanpa ini teks hijau tua di atas foto berwarna
            jadi tidak terbaca. Gradien ke kanan agar sisi potret tetap terang.
          */}
          <div
            aria-hidden
            className="absolute inset-0 -z-10 bg-gradient-to-r from-background via-background/90 to-background/60"
          />
        </>
      )}

      {/* Elemen dekoratif — hanya bila tidak ada latar, agar tidak bertumpuk */}
      {!latar && (
        <div
          aria-hidden
          className="absolute right-0 top-0 -z-10 h-full w-1/2 rounded-bl-[100px] bg-primary/5"
        />
      )}

      <div className="mx-auto grid max-w-7xl grid-cols-1 items-center gap-6 px-4 md:px-6 lg:grid-cols-12 lg:px-8">
        {/* --- Kolom narasi --- */}
        <div className="z-10 flex flex-col gap-6 lg:col-span-7">
          <span className="inline-flex w-fit items-center gap-2 rounded-full bg-secondary-container px-3 py-1 text-xs font-medium text-on-secondary-container">
            <Icon name="campaign" size={16} filled />
            Portal Resmi Pemerintahan
          </span>

          <h1 className="font-heading text-3xl font-bold leading-tight text-primary md:text-5xl">
            {profil.sebutan_desa} {profil.nama_desa} dalam{" "}
            <span className="italic text-secondary">Transparansi</span> dan{" "}
            <span className="italic text-secondary">Inovasi</span>
          </h1>

          <p className="max-w-2xl text-lg text-on-surface-variant">
            Selamat datang di portal resmi {profil.sebutan_desa} {profil.nama_desa}
            {wilayah && `, ${wilayah}`}. Kami berkomitmen memberikan layanan publik yang
            akuntabel berlandaskan nilai &ldquo;Alam Takambang Jadi Guru&rdquo;.
          </p>

          <div className="mt-2 flex flex-wrap items-center gap-4">
            <Link
              href="/statistik"
              className="inline-flex min-h-11 items-center gap-2 rounded-full bg-primary px-6 py-3 text-sm font-semibold text-on-primary shadow-level1 transition hover:bg-primary-container"
            >
              <Icon name="monitoring" size={20} />
              Lihat Data Statistik
            </Link>
            <Link
              href="/buku-tamu"
              className="inline-flex min-h-11 items-center gap-2 rounded-full border border-primary px-6 py-3 text-sm font-semibold text-primary transition hover:bg-surface-container-highest"
            >
              <Icon name="forum" size={20} />
              Kirim Pesan
            </Link>
          </div>
        </div>

        {/* --- Potret kepala nagari --- */}
        {kepala && (
          <div className="relative mt-10 flex justify-center lg:col-span-5 lg:mt-0 lg:justify-end">
            <div className="relative h-[400px] w-[300px] md:h-[450px] md:w-[350px]">
              <div
                aria-hidden
                className="absolute inset-0 translate-x-4 translate-y-4 rounded-b-3xl rounded-t-full bg-secondary/10"
              />
              <div className="absolute inset-0 overflow-hidden rounded-b-3xl rounded-t-full border border-outline-variant bg-surface shadow-level2">
                {kepala.foto_url ? (
                  // eslint-disable-next-line @next/next/no-img-element
                  <img
                    src={kepala.foto_url}
                    alt={`${kepala.jabatan} — ${kepala.nama}`}
                    className="h-full w-full object-cover object-top"
                  />
                ) : (
                  <div className="flex h-full items-center justify-center text-outline">
                    <Icon name="person" size={64} />
                  </div>
                )}
              </div>

              <div className="absolute -left-4 bottom-4 flex items-center gap-4 rounded-lg border border-outline-variant bg-surface p-4 shadow-level2 md:-left-8">
                <span className="rounded-full bg-primary-container p-2 text-on-primary-container">
                  <Icon name="person_apron" filled />
                </span>
                <span className="block">
                  <span className="mb-0.5 block text-sm font-semibold text-primary">
                    {kepala.nama}
                  </span>
                  <span className="block text-xs text-on-surface-variant">
                    {kepala.jabatan}
                  </span>
                </span>
              </div>
            </div>
          </div>
        )}
      </div>
    </section>
  );
}
