import type { Metadata } from "next";
import Link from "next/link";
import FilterRegulasi from "@/components/regulasi/FilterRegulasi";
import Breadcrumb from "@/components/ui/Breadcrumb";
import Icon from "@/components/ui/Icon";
import { getRegulasi } from "@/lib/api";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  title: "Regulasi",
  description:
    "Produk hukum dan informasi publik Nagari Simpang — peraturan nagari, keputusan wali nagari, serta dokumen keterbukaan informasi.",
};

// Di Next.js 16 searchParams adalah Promise — wajib di-await.
type Search = Promise<{ jenis?: string; tahun?: string; kategori?: string }>;

const JENIS = [
  {
    slug: "produk-hukum" as const,
    label: "Produk Hukum",
    ikon: "gavel",
    keterangan:
      "Peraturan Nagari dan Keputusan Wali Nagari yang menjadi dasar hukum penyelenggaraan pemerintahan.",
  },
  {
    slug: "informasi-publik" as const,
    label: "Informasi Publik",
    ikon: "folder_open",
    keterangan:
      "Dokumen keterbukaan informasi publik: informasi berkala, serta-merta, setiap saat, dan yang dikecualikan.",
  },
];

function formatTanggal(iso: string | null): string {
  if (!iso) return "—";

  try {
    return new Date(iso).toLocaleDateString("id-ID", {
      day: "2-digit",
      month: "long",
      year: "numeric",
    });
  } catch {
    return "—";
  }
}

export default async function RegulasiPage({ searchParams }: { searchParams: Search }) {
  const sp = await searchParams;
  const aktif = JENIS.find((j) => j.slug === sp.jenis) ?? JENIS[0];
  const infoPublik = aktif.slug === "informasi-publik";

  const { items, tahunTersedia, jenisPeraturan } = await getRegulasi(aktif.slug, {
    tahun: sp.tahun,
    kategori: sp.kategori,
  });

  // Susunan kolom mengikuti halaman OpenSID: Produk Hukum dan Informasi Publik
  // memang berbeda — yang pertama tanpa tanggal, yang kedua memakainya.
  const kolom = infoPublik
    ? ["No", "Judul Informasi", "Tahun", "Kategori", "Tanggal Upload", "Aksi"]
    : ["No", "Judul Produk Hukum", "Jenis Peraturan", "Tahun", "Aksi"];

  return (
    <main className="mx-auto w-full max-w-6xl px-4 py-12 md:px-6 lg:px-8">
      <Breadcrumb
        items={[{ label: "Beranda", href: "/" }, { label: "Regulasi" }, { label: aktif.label }]}
      />

      <header className="mb-8">
        <h1 className="mb-2 font-heading text-3xl font-bold text-primary md:text-4xl">
          {aktif.label}
        </h1>
        <p className="max-w-3xl text-on-surface-variant">{aktif.keterangan}</p>
      </header>

      {/* --- Pilihan jenis --- */}
      <nav aria-label="Jenis regulasi" className="songket-border mb-6 pb-4">
        <ul className="flex flex-wrap gap-2">
          {JENIS.map((j) => {
            const ini = j.slug === aktif.slug;

            return (
              <li key={j.slug}>
                <Link
                  href={`/regulasi?jenis=${j.slug}`}
                  aria-current={ini ? "page" : undefined}
                  className={`flex min-h-11 items-center gap-2 rounded-lg px-4 text-sm font-semibold transition ${
                    ini
                      ? "bg-primary text-on-primary"
                      : "border border-outline-variant text-on-surface hover:bg-surface-container"
                  }`}
                >
                  <Icon name={j.ikon} size={18} />
                  {j.label}
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>

      <FilterRegulasi
        jenis={aktif.slug}
        tahun={sp.tahun}
        kategori={sp.kategori}
        tahunTersedia={tahunTersedia}
        jenisPeraturan={jenisPeraturan}
      />

      {/*
        Tabel SELALU dirender lengkap dengan kepalanya, meski datanya kosong —
        pengunjung tetap melihat kolom apa saja yang akan tersedia. Keadaan
        kosong disampaikan sebagai satu baris di dalam tabel, bukan dengan
        menyembunyikan tabelnya.
      */}
      <div className="overflow-x-auto rounded-xl border border-outline-variant shadow-level1">
        <table className="w-full min-w-[680px] border-collapse text-sm">
          <thead className="bg-primary text-on-primary">
            <tr>
              {kolom.map((k) => (
                <th
                  key={k}
                  className={`px-3 py-2.5 font-semibold ${
                    k === "No" || k === "Tahun" || k === "Aksi" ? "text-center" : "text-left"
                  } ${k === "No" ? "w-14" : ""} ${k === "Tahun" ? "w-20" : ""} ${
                    k === "Aksi" ? "w-28" : ""
                  }`}
                >
                  {k}
                </th>
              ))}
            </tr>
          </thead>

          <tbody>
            {items.length === 0 ? (
              <tr>
                <td colSpan={kolom.length} className="px-3 py-14 text-center">
                  <Icon name="inbox" size={40} className="text-outline" />
                  <p className="mt-3 font-medium text-on-surface">
                    Tidak ada data yang tersedia pada tabel ini
                  </p>
                  <p className="mx-auto mt-1 max-w-md text-xs text-on-surface-variant">
                    Dokumen {aktif.label.toLowerCase()} belum diunggah oleh perangkat nagari
                    melalui panel admin.
                  </p>
                </td>
              </tr>
            ) : (
              items.map((d, i) => {
                const unduh = d.berkas_url ? (
                  <a
                    href={d.berkas_url}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-1 font-medium text-primary hover:underline"
                  >
                    <Icon name="download" size={16} />
                    Unduh
                  </a>
                ) : (
                  <span className="text-outline">—</span>
                );

                const judul = (
                  <>
                    {d.nama}
                    {d.keterangan && (
                      <span className="mt-0.5 block text-xs font-normal text-on-surface-variant">
                        {d.keterangan}
                      </span>
                    )}
                  </>
                );

                return (
                  <tr
                    key={d.id}
                    className="border-t border-outline-variant odd:bg-surface even:bg-surface-container-low"
                  >
                    <td className="px-3 py-2.5 text-center tabular-nums text-on-surface-variant">
                      {i + 1}
                    </td>
                    <td className="px-3 py-2.5 font-medium text-on-surface">{judul}</td>

                    {infoPublik ? (
                      <>
                        <td className="px-3 py-2.5 text-center tabular-nums text-on-surface-variant">
                          {d.tahun ?? "—"}
                        </td>
                        <td className="px-3 py-2.5 text-on-surface-variant">
                          {d.klasifikasi ?? "—"}
                        </td>
                        <td className="px-3 py-2.5 text-on-surface-variant">
                          {formatTanggal(d.tanggal)}
                        </td>
                      </>
                    ) : (
                      <>
                        <td className="px-3 py-2.5 text-on-surface-variant">
                          {d.kategori ?? "—"}
                        </td>
                        <td className="px-3 py-2.5 text-center tabular-nums text-on-surface-variant">
                          {d.tahun ?? "—"}
                        </td>
                      </>
                    )}

                    <td className="px-3 py-2.5 text-center">{unduh}</td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>

      <p className="mt-3 text-xs text-on-surface-variant">
        Menampilkan {items.length} entri
        {(sp.tahun || sp.kategori) && " (tersaring)"}.
      </p>
    </main>
  );
}
