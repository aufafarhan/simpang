import type { ProfilDesa } from "@/lib/types";

export default function Footer({ profil }: { profil: ProfilDesa }) {
  return (
    <footer className="mt-16 border-t border-outline-variant bg-surface-container-highest text-on-surface">
      <div className="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-2 md:grid-cols-3">
        <div>
          <h3 className="mb-2 font-heading text-lg font-bold text-primary">
            {profil.nama_desa}
          </h3>
          <p className="text-sm text-on-surface-variant">{profil.alamat}</p>
          <p className="text-sm text-on-surface-variant">
            {[profil.kecamatan, profil.kabupaten, profil.provinsi]
              .filter((x) => x && x !== "-")
              .join(", ")}
          </p>
        </div>

        <div>
          <h3 className="mb-2 font-heading text-base font-semibold text-on-surface">Kontak</h3>
          <p className="text-sm text-on-surface-variant">Email: {profil.email}</p>
          {profil.telepon && profil.telepon !== "-" && (
            <p className="text-sm text-on-surface-variant">Telp: {profil.telepon}</p>
          )}
        </div>

        <div>
          <h3 className="mb-2 font-heading text-base font-semibold text-on-surface">
            Ikuti Kami
          </h3>
          <div className="flex flex-wrap gap-3 text-sm">
            {profil.sosial_media.map((s) => (
              <a
                key={s.nama}
                href={s.link ?? s.url ?? "#"}
                target="_blank"
                rel="noopener noreferrer"
                className="capitalize text-on-surface-variant transition hover:text-primary"
              >
                {s.nama}
              </a>
            ))}
          </div>
        </div>
      </div>

      <div className="border-t border-outline-variant py-4 text-center text-xs text-on-surface-variant">
        © {new Date().getFullYear()} {profil.nama_desa}. Tradisi Bertemu Inovasi.
      </div>
    </footer>
  );
}
