import type { Metadata } from "next";
import ChartPenduduk from "@/components/statistik/ChartPenduduk";
import { getStatistikPenduduk } from "@/lib/api";

export const metadata: Metadata = { title: "Statistik Penduduk" };

export default async function StatistikPage() {
  const jenisKelamin = await getStatistikPenduduk("jenis_kelamin");

  return (
    <div className="mx-auto max-w-4xl px-4 py-8">
      <h1 className="mb-6 text-2xl font-bold text-on-surface">Statistik Penduduk</h1>
      <div className="grid gap-6">
        <ChartPenduduk data={jenisKelamin} />
        {/* Tambahkan kategori lain (usia, pendidikan, pekerjaan) dengan
            memanggil getStatistikPenduduk("usia"), dst. */}
      </div>
    </div>
  );
}
