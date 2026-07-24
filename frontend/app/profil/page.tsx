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
      <h1 className="mb-6 text-2xl font-bold text-on-surface">Profil {p.nama_desa}</h1>
      <dl className="divide-y divide-outline-variant rounded-xl border border-outline-variant">
        {baris.map(([k, v]) => (
          <div key={k} className="grid grid-cols-3 gap-4 px-4 py-3">
            <dt className="font-medium text-outline">{k}</dt>
            <dd className="col-span-2 text-on-surface">{v}</dd>
          </div>
        ))}
      </dl>
    </div>
  );
}
