import type { Metadata } from "next";
import { getProfilDesa } from "@/lib/api";

export const metadata: Metadata = { title: "Profil Desa" };

export default async function ProfilPage() {
  const p = await getProfilDesa();

  const baris = [
    ["Nama", p.nama_desa],
    ["Kepala", p.kepala_desa],
    ["Alamat", p.alamat],
    ["Kecamatan", p.kecamatan],
    ["Kabupaten", p.kabupaten],
    ["Provinsi", p.provinsi],
    ["Kode Pos", p.kode_pos],
    ["Email", p.email],
    ["Telepon", p.telepon],
  ].filter(([, v]) => v && v !== "-");

  return (
    <div className="mx-auto max-w-3xl px-4 py-8">
      <h1 className="mb-6 text-2xl font-bold text-slate-900">Profil {p.nama_desa}</h1>
      <dl className="divide-y divide-slate-200 rounded-xl border border-slate-200">
        {baris.map(([k, v]) => (
          <div key={k} className="grid grid-cols-3 gap-4 px-4 py-3">
            <dt className="font-medium text-slate-500">{k}</dt>
            <dd className="col-span-2 text-slate-800">{v}</dd>
          </div>
        ))}
      </dl>
    </div>
  );
}
