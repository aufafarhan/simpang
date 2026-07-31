import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import Icon from "@/components/ui/Icon";
import { getPembangunanDetail } from "@/lib/api";

type Params = Promise<{ id: string }>;

export async function generateMetadata({
  params,
}: {
  params: Params;
}): Promise<Metadata> {
  const { id } = await params;
  const proyek = await getPembangunanDetail(id);
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx —
  // jangan diulang di sini agar tidak jadi "… — Simpang — Simpang".
  const title = proyek?.judul || "Proyek Pembangunan";

  return {
    title,
    description: proyek?.deskripsi || "Detail proyek pembangunan di Nagari Simpang.",
  };
}

function formatRupiah(val: number): string {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(val);
}

export default async function DetailProyekPage({
  params,
}: {
  params: Params;
}) {
  const { id } = await params;
  const proyek = await getPembangunanDetail(id);

  if (!proyek) {
    notFound();
  }

  const isSelesai = proyek.status === "Selesai";
  const isProses = proyek.status === "Dalam Proses";

  return (
    <main className="mx-auto flex-grow w-full max-w-6xl px-4 py-8 md:px-6 md:py-12">
      {/* Breadcrumbs */}
      <nav aria-label="Breadcrumb" className="mb-6 flex items-center text-xs font-semibold text-primary">
        <Link href="/" className="hover:underline">
          Beranda
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <Link href="/pembangunan" className="hover:underline text-on-surface-variant">
          Pembangunan
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <span aria-current="page" className="text-secondary font-bold truncate max-w-[200px] md:max-w-xs">
          {proyek.judul}
        </span>
      </nav>

      {/* Main Header Card */}
      <div className="mb-8 rounded-2xl border border-outline-variant bg-surface p-6 md:p-8 shadow-sm">
        <div className="flex flex-wrap items-center justify-between gap-4 mb-4">
          <span
            className={`rounded-full px-3 py-1 text-xs font-semibold shadow-sm ${
              isSelesai
                ? "bg-emerald-100 text-emerald-900 border border-emerald-300"
                : isProses
                ? "bg-amber-100 text-amber-900 border border-amber-300"
                : "bg-blue-100 text-blue-900 border border-blue-300"
            }`}
          >
            {proyek.status}
          </span>
          <span className="text-xs font-medium text-on-surface-variant">
            Tahun Anggaran: <strong className="text-primary">{proyek.tahun_anggaran}</strong>
          </span>
        </div>

        <h1 className="font-heading text-2xl font-bold text-primary md:text-4xl mb-6">
          {proyek.judul}
        </h1>

        {/* Highlight Stats Grid */}
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
          <div className="rounded-xl border border-outline-variant/60 bg-surface-container-low p-4">
            <p className="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">
              Total Anggaran
            </p>
            <p className="font-heading text-lg font-bold text-primary">
              {formatRupiah(proyek.anggaran)}
            </p>
          </div>

          <div className="rounded-xl border border-outline-variant/60 bg-surface-container-low p-4">
            <p className="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">
              Sumber Dana
            </p>
            <p className="font-heading text-lg font-bold text-primary">
              {proyek.sumber_dana}
            </p>
          </div>

          <div className="rounded-xl border border-outline-variant/60 bg-surface-container-low p-4">
            <p className="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">
              Lokasi Proyek
            </p>
            <p className="font-heading text-lg font-bold text-primary">
              {proyek.lokasi}
            </p>
          </div>

          <div className="rounded-xl border border-outline-variant/60 bg-surface-container-low p-4">
            <p className="text-[11px] font-semibold uppercase tracking-wider text-on-surface-variant">
              Pelaksana Kegiatan
            </p>
            <p className="font-heading text-lg font-bold text-primary truncate">
              {proyek.pelaksana || "-"}
            </p>
          </div>
        </div>

        {/* Progress Bar */}
        <div className="rounded-xl border border-outline-variant/60 bg-surface-container-low p-5">
          <div className="mb-2 flex justify-between text-sm font-semibold">
            <span className="text-on-surface-variant">Progres Pengerjaan</span>
            <span className="text-primary font-bold">{proyek.progres_persen}%</span>
          </div>
          <div className="h-3 w-full overflow-hidden rounded-full bg-surface-container-high">
            <div
              className="h-full rounded-full bg-primary transition-all duration-500"
              style={{ width: `${proyek.progres_persen}%` }}
            />
          </div>
        </div>
      </div>

      {/* Description & Details */}
      <section className="mb-10 rounded-2xl border border-outline-variant bg-surface p-6 md:p-8 shadow-sm">
        <h2 className="font-heading text-xl font-bold text-primary mb-3">
          Deskripsi Proyek
        </h2>
        <div className="prose prose-stone max-w-none text-on-surface-variant leading-relaxed">
          <p>{proyek.deskripsi}</p>
        </div>
      </section>

      {/* Documentation Gallery */}
      {proyek.foto_dokumentasi && proyek.foto_dokumentasi.length > 0 && (
        <section className="rounded-2xl border border-outline-variant bg-surface p-6 md:p-8 shadow-sm">
          <h2 className="font-heading text-xl font-bold text-primary mb-6 flex items-center gap-2">
            <Icon name="photo_library" size={24} className="text-secondary" />
            <span>Dokumentasi Progres Proyek</span>
          </h2>

          <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {proyek.foto_dokumentasi.map((doc) => (
              <div
                key={doc.id}
                className="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-sm"
              >
                <div className="relative aspect-[4/3] w-full overflow-hidden bg-surface-container">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={doc.gambar_url || "/placeholder-gallery.jpg"}
                    alt={doc.keterangan || "Dokumentasi proyek"}
                    className="h-full w-full object-cover"
                  />
                  <div className="absolute right-3 top-3 rounded-full bg-primary/90 px-3 py-1 text-xs font-semibold text-on-primary shadow-sm backdrop-blur-sm">
                    {doc.persentase}% Progres
                  </div>
                </div>
                {doc.keterangan && (
                  <div className="p-4">
                    <p className="text-xs text-on-surface-variant">{doc.keterangan}</p>
                  </div>
                )}
              </div>
            ))}
          </div>
        </section>
      )}
    </main>
  );
}
