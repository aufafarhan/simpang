import type { Metadata } from "next";
import Link from "next/link";
import Icon from "@/components/ui/Icon";
import PembangunanFilterClient from "@/components/pembangunan/PembangunanFilterClient";
import { getPembangunan } from "@/lib/api";

export const metadata: Metadata = {
  title: "Data Pembangunan - Nagari Simpang",
  description:
    "Transparansi dan informasi terkini mengenai proyek infrastruktur dan pembangunan berkelanjutan di wilayah Nagari Simpang.",
};

function formatRupiah(val: number): string {
  return new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(val);
}

export default async function PembangunanPage({
  searchParams,
}: {
  searchParams: Promise<{ page?: string; status?: string }>;
}) {
  const params = await searchParams;
  const page = Math.max(1, parseInt(params.page || "1", 10));
  const statusFilter = params.status || "";

  const { items: projects, total_pages } = await getPembangunan(page, statusFilter);

  return (
    <main className="mx-auto flex-grow w-full max-w-6xl px-4 py-8 md:px-6 md:py-12">
      {/* Breadcrumbs */}
      <nav aria-label="Breadcrumb" className="mb-6 flex items-center text-xs font-semibold text-primary">
        <Link href="/" className="hover:underline">
          Beranda
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <span aria-current="page" className="text-secondary font-bold">
          Pembangunan
        </span>
      </nav>

      {/* Header Section */}
      <header className="mb-8 relative overflow-hidden rounded-xl border border-outline-variant bg-surface-container-low p-6 md:p-8 shadow-sm">
        <div className="relative z-10 max-w-2xl">
          <h1 className="font-heading text-3xl font-bold text-primary md:text-5xl mb-3">
            Data Pembangunan
          </h1>
          <p className="text-base text-on-surface-variant md:text-lg leading-relaxed">
            Transparansi dan informasi terkini mengenai proyek infrastruktur dan pembangunan berkelanjutan di wilayah Nagari Simpang, mewujudkan kesejahteraan masyarakat.
          </p>
        </div>
      </header>

      {/* Filter Section */}
      <div className="mb-8 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 rounded-lg border border-outline-variant bg-surface p-4 shadow-sm">
        <PembangunanFilterClient statusSaatIni={statusFilter} />
      </div>

      {/* Project Grid */}
      {projects.length === 0 ? (
        <div className="rounded-xl border border-outline-variant bg-surface-container-low p-12 text-center text-on-surface-variant">
          <Icon name="construction" size={48} className="mx-auto mb-3 text-outline" />
          <p className="text-lg font-medium">Belum ada proyek pembangunan untuk kriteria ini.</p>
        </div>
      ) : (
        <div className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
          {projects.map((proyek) => {
            const isSelesai = proyek.status === "Selesai";
            const isProses = proyek.status === "Dalam Proses";

            return (
              <div
                key={proyek.id}
                className="group flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
              >
                <div className="relative h-48 w-full overflow-hidden bg-surface-container">
                  {/* eslint-disable-next-line @next/next/no-img-element */}
                  <img
                    src={proyek.foto_cover || "/placeholder-gallery.jpg"}
                    alt={proyek.judul}
                    className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                  <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent" />

                  {/* Status Badge */}
                  <span
                    className={`absolute right-3 top-3 rounded-full px-3 py-1 text-xs font-semibold shadow-sm backdrop-blur-sm ${
                      isSelesai
                        ? "bg-emerald-100 text-emerald-900 border border-emerald-300"
                        : isProses
                        ? "bg-amber-100 text-amber-900 border border-amber-300"
                        : "bg-blue-100 text-blue-900 border border-blue-300"
                    }`}
                  >
                    {proyek.status}
                  </span>
                </div>

                <div className="flex flex-col flex-grow p-5">
                  <h2 className="font-heading text-lg font-bold text-primary line-clamp-2 mb-3 group-hover:text-tertiary transition-colors">
                    {proyek.judul}
                  </h2>

                  <div className="mb-4 space-y-2 text-xs text-on-surface-variant">
                    <p className="flex items-center gap-1.5">
                      <Icon name="location_on" size={16} className="text-secondary" />
                      <span className="font-medium text-on-surface">{proyek.lokasi}</span>
                    </p>
                    <p className="flex items-center gap-1.5">
                      <Icon name="payments" size={16} className="text-emerald-700" />
                      <span className="font-semibold text-primary">
                        {formatRupiah(proyek.anggaran)}
                      </span>
                      <span className="text-[10px] text-on-surface-variant/80">({proyek.sumber_dana})</span>
                    </p>
                  </div>

                  {/* Progress Bar */}
                  <div className="mt-auto pt-3 border-t border-outline-variant/40">
                    <div className="mb-1.5 flex justify-between text-xs font-semibold">
                      <span className="text-on-surface-variant">Progres Pengerjaan</span>
                      <span className="text-primary">{proyek.progres_persen}%</span>
                    </div>
                    <div className="h-2.5 w-full overflow-hidden rounded-full bg-surface-container-high">
                      <div
                        className="h-full rounded-full bg-primary transition-all duration-500"
                        style={{ width: `${proyek.progres_persen}%` }}
                      />
                    </div>

                    <Link
                      href={`/pembangunan/${proyek.id}`}
                      className="mt-4 flex items-center justify-center gap-1.5 w-full rounded-lg border border-outline-variant py-2 text-xs font-semibold text-primary transition hover:bg-primary hover:text-on-primary"
                    >
                      <span>Lihat Detail Proyek</span>
                      <Icon name="arrow_forward" size={14} />
                    </Link>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}

      {/* Pagination */}
      {total_pages > 1 && (
        <div className="mt-12 flex justify-center gap-2">
          {page > 1 && (
            <Link
              href={`/pembangunan?page=${page - 1}${statusFilter ? `&status=${statusFilter}` : ""}`}
              className="flex h-10 w-10 items-center justify-center rounded border border-outline-variant text-on-surface-variant transition hover:bg-surface-container"
              aria-label="Halaman sebelumnya"
            >
              <Icon name="chevron_left" size={20} />
            </Link>
          )}

          {Array.from({ length: total_pages }, (_, i) => i + 1).map((p) => (
            <Link
              key={p}
              href={`/pembangunan?page=${p}${statusFilter ? `&status=${statusFilter}` : ""}`}
              className={`flex h-10 w-10 items-center justify-center rounded font-semibold transition ${
                p === page
                  ? "bg-secondary text-on-secondary-container shadow-sm"
                  : "border border-outline-variant text-on-surface-variant hover:bg-surface-container"
              }`}
            >
              {p}
            </Link>
          ))}

          {page < total_pages && (
            <Link
              href={`/pembangunan?page=${page + 1}${statusFilter ? `&status=${statusFilter}` : ""}`}
              className="flex h-10 w-10 items-center justify-center rounded border border-outline-variant text-on-surface-variant transition hover:bg-surface-container"
              aria-label="Halaman selanjutnya"
            >
              <Icon name="chevron_right" size={20} />
            </Link>
          )}
        </div>
      )}
    </main>
  );
}
