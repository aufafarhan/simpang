import type { ProfilDesa } from "@/lib/types";

export default function Footer({ profil }: { profil: ProfilDesa }) {
  return (
    <footer className="mt-12 bg-navy-900 text-slate-300">
      <div className="mx-auto grid max-w-6xl gap-6 px-4 py-8 sm:grid-cols-2 md:grid-cols-3">
        <div>
          <h3 className="mb-2 font-bold text-white">{profil.nama_desa}</h3>
          <p className="text-sm">{profil.alamat}</p>
          <p className="text-sm">
            {[profil.kecamatan, profil.kabupaten, profil.provinsi]
              .filter((x) => x && x !== "-")
              .join(", ")}
          </p>
        </div>
        <div>
          <h3 className="mb-2 font-bold text-white">Kontak</h3>
          <p className="text-sm">Email: {profil.email}</p>
          {profil.telepon !== "-" && <p className="text-sm">Telp: {profil.telepon}</p>}
        </div>
        <div>
          <h3 className="mb-2 font-bold text-white">Ikuti Kami</h3>
          <div className="flex gap-3 text-sm">
            {profil.sosial_media.map((s) => (
              <a key={s.nama} href={s.url} className="capitalize hover:text-white">
                {s.nama}
              </a>
            ))}
          </div>
        </div>
      </div>
      <div className="border-t border-white/10 py-4 text-center text-xs text-slate-400">
        © {new Date().getFullYear()} {profil.nama_desa}. Dibuat dengan OpenSID + Next.js.
      </div>
    </footer>
  );
}
