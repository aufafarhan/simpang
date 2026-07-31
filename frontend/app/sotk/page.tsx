import type { Metadata } from "next";
import Link from "next/link";
import Icon from "@/components/ui/Icon";
import { getSotk } from "@/lib/api";
import type { SotkNode } from "@/lib/types";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  title: "Struktur Organisasi (SOTK)",
  description:
    "Bagan susunan pemerintahan Nagari Simpang, mencerminkan tata kerja dan pembagian tugas untuk pelayanan publik.",
};

function OrgCard({ node, highlight = false }: { node: SotkNode; highlight?: boolean }) {
  return (
    <div
      className={`group flex flex-col items-center rounded-xl border p-4 text-center shadow-sm transition-all duration-300 hover:shadow-md ${
        highlight
          ? "border-primary/30 bg-surface-container-lowest"
          : "border-outline-variant bg-surface"
      }`}
    >
      <div
        className={`relative mb-3 h-20 w-20 overflow-hidden rounded-full border-2 transition-transform group-hover:scale-105 ${
          highlight ? "border-primary" : "border-outline-variant"
        }`}
      >
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src={node.foto_url || "/placeholder-avatar.jpg"}
          alt={node.nama}
          className="h-full w-full object-cover"
        />
      </div>
      <h3 className="font-heading text-sm font-bold text-primary">{node.nama}</h3>
      <p className="text-xs text-on-surface-variant font-medium">{node.jabatan}</p>
    </div>
  );
}

/** Kumpulkan seluruh node di bawah akar menjadi daftar datar. */
function ratakan(node: SotkNode): SotkNode[] {
  return (node.bawahan ?? []).flatMap((n) => [n, ...ratakan(n)]);
}

export default async function SotkPage() {
  const root = await getSotk();
  const waliNagari = root;

  /*
   * Pengelompokan dibuat berdasarkan NAMA JABATAN, bukan kedalaman pohon.
   * Alasannya: kolom `atasan` di OpenSID sering belum dikonfigurasi operator,
   * sehingga seluruh perangkat bisa menggantung langsung di bawah Wali Nagari.
   * Cara ini tetap benar baik saat data datar maupun sudah berjenjang.
   */
  const semua = ratakan(root);
  const cocok = (n: SotkNode, kata: string) =>
    n.jabatan.toLowerCase().includes(kata.toLowerCase());

  const sekretaris = semua.find((n) => cocok(n, "sekretaris"));
  const kasiList = semua.filter((n) => cocok(n, "kasi") || cocok(n, "kepala seksi"));
  const kaurList = semua.filter((n) => cocok(n, "kaur") || cocok(n, "kepala urusan"));
  const jorongList = semua.filter((n) => cocok(n, "jorong") || cocok(n, "kadus"));
  const stafList = semua.filter((n) => cocok(n, "staf"));

  const sudahDikelompokkan = new Set(
    [sekretaris, ...kasiList, ...kaurList, ...jorongList, ...stafList]
      .filter(Boolean)
      .map((n) => (n as SotkNode).id),
  );
  const lainnyaList = semua.filter((n) => !sudahDikelompokkan.has(n.id));

  return (
    <main className="mx-auto flex-grow w-full max-w-6xl px-4 py-8 md:px-6 md:py-12">
      {/* Breadcrumbs */}
      <nav aria-label="Breadcrumb" className="mb-6 flex items-center text-xs font-semibold text-primary">
        <Link href="/" className="hover:underline">
          Beranda
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <Link href="/pemerintah-nagari" className="hover:underline text-on-surface-variant">
          Pemerintahan
        </Link>
        <Icon name="chevron_right" size={14} className="mx-1 text-on-surface-variant" />
        <span aria-current="page" className="text-secondary font-bold">
          SOTK
        </span>
      </nav>

      {/* Page Header */}
      <div className="mb-10 text-center">
        <h1 className="font-heading text-3xl font-bold text-primary md:text-5xl mb-3">
          Struktur Organisasi Nagari Simpang
        </h1>
        <p className="mx-auto max-w-2xl text-base text-on-surface-variant md:text-lg">
          Bagan susunan pemerintahan Nagari Simpang, mencerminkan tata kerja dan pembagian tugas untuk pelayanan optimal kepada masyarakat.
        </p>
      </div>

      {/* Organizational Tree View */}
      <div className="overflow-x-auto pb-12">
        <div className="flex min-w-[760px] flex-col items-center py-4">
          {/* Level 1: Wali Nagari */}
          <div className="w-56">
            <OrgCard node={waliNagari} highlight />
          </div>

          <div className="my-4 h-8 w-0.5 bg-primary/30" />

          {/* Level 2: BPRN / KAN / Sekretaris Nagari */}
          <div className="relative flex w-full max-w-2xl justify-between items-center px-4">
            <div className="w-48 text-center rounded-lg border border-dashed border-outline p-3 bg-surface-container-low text-xs">
              <span className="font-bold text-primary block">BPRN</span>
              <span className="text-on-surface-variant">Badan Permusyawaratan</span>
            </div>

            {sekretaris && (
              <div className="w-56">
                <OrgCard node={sekretaris} highlight />
              </div>
            )}

            <div className="w-48 text-center rounded-lg border border-dashed border-outline p-3 bg-surface-container-low text-xs">
              <span className="font-bold text-primary block">KAN</span>
              <span className="text-on-surface-variant">Kerapatan Adat Nagari</span>
            </div>
          </div>

          {/* Connector to Level 3 (Kaur & Kasi) */}
          <div className="my-4 h-8 w-0.5 bg-primary/30" />

          {/* Kelompok jabatan — hanya dirender bila ada anggotanya */}
          {(
            [
              ["Kepala Seksi (Kasi)", kasiList],
              ["Kepala Urusan (Kaur)", kaurList],
              ["Kepala Jorong", jorongList],
              ["Staf Nagari", stafList],
              ["Perangkat Lainnya", lainnyaList],
            ] as const
          )
            .filter(([, daftar]) => daftar.length > 0)
            .map(([judul, daftar], i) => (
              <div key={judul} className="w-full max-w-4xl">
                {i > 0 && <div className="mx-auto my-4 h-8 w-0.5 bg-primary/30" />}
                <div className="mb-2 text-center text-xs font-semibold uppercase tracking-wider text-tertiary">
                  {judul}
                </div>
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-3">
                  {daftar.map((n) => (
                    <OrgCard key={n.id} node={n} />
                  ))}
                </div>
              </div>
            ))}
        </div>
      </div>
    </main>
  );
}
