import type { Metadata } from "next";
import Breadcrumb from "@/components/ui/Breadcrumb";
import Icon from "@/components/ui/Icon";
import { getProfilDesa } from "@/lib/api";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  title: "Peta Wilayah",
  description:
    "Lokasi Kantor Wali Nagari Simpang, Kecamatan Simpang Alahan Mati, Kabupaten Pasaman, Sumatera Barat.",
};

/**
 * Peta memakai embed OpenStreetMap — tanpa Leaflet/MapLibre, karena data yang
 * tersedia hanya SATU titik (koordinat kantor nagari di tabel `config`).
 *
 * Design Stitch (design/stitch/peta_wilayah_nagari_simpang_desktop) menampilkan
 * lapisan Batas Jorong, Fasilitas Umum, dan legenda sekolah/puskesmas/masjid.
 * Semuanya SENGAJA TIDAK dibuat: tabel sumbernya kosong —
 * `lokasi`, `garis`, `area`, `polygon` = 0 baris, dan
 * `tweb_wil_clusterdesa.lat/lng` seluruhnya NULL. Menampilkannya berarti
 * mengarang data. Begitu perangkat nagari mengisi Peta di panel admin,
 * barulah lapisan itu layak ditambahkan (butuh Leaflet + endpoint baru).
 */
export default async function PetaPage() {
  const profil = await getProfilDesa();
  const { lat, lng } = profil.peta ?? { lat: null, lng: null, zoom: 15 };

  const alamatLengkap = [
    profil.alamat,
    profil.kecamatan && `Kec. ${profil.kecamatan}`,
    profil.kabupaten && `Kab. ${profil.kabupaten}`,
    profil.provinsi,
    profil.kode_pos,
  ]
    .filter(Boolean)
    .join(", ");

  return (
    <main className="mx-auto w-full max-w-6xl px-4 py-12 md:px-6 lg:px-8">
      <Breadcrumb items={[{ label: "Beranda", href: "/" }, { label: "Peta Wilayah" }]} />

      <header className="mb-8">
        <h1 className="mb-2 font-heading text-3xl font-bold text-primary md:text-4xl">
          Peta Wilayah
        </h1>
        <p className="max-w-3xl text-on-surface-variant">
          Lokasi Kantor Wali {profil.nama_desa}, {profil.kecamatan}, {profil.kabupaten},{" "}
          {profil.provinsi}.
        </p>
      </header>

      {lat === null || lng === null ? (
        <div className="rounded-2xl border border-dashed border-outline-variant p-12 text-center">
          <Icon name="location_off" size={48} className="text-outline" />
          <p className="mt-4 font-heading text-xl text-on-surface">Koordinat belum diatur</p>
          <p className="mx-auto mt-2 max-w-md text-sm text-on-surface-variant">
            Titik lokasi kantor nagari belum diisi pada pengaturan OpenSID.
          </p>
        </div>
      ) : (
        <div className="grid gap-6 lg:grid-cols-3">
          <section className="lg:col-span-2">
            <div className="songket-border overflow-hidden rounded-2xl border border-outline-variant shadow-level1">
              <iframe
                src={`https://www.openstreetmap.org/export/embed.html?bbox=${[
                  lng - 0.02,
                  lat - 0.02,
                  lng + 0.02,
                  lat + 0.02,
                ].join("%2C")}&layer=mapnik&marker=${lat}%2C${lng}`}
                title={`Peta lokasi Kantor Wali ${profil.nama_desa}`}
                className="h-[420px] w-full md:h-[560px]"
                loading="lazy"
              />
            </div>

            <div className="mt-4 flex flex-wrap gap-3">
              <a
                href={`https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=16/${lat}/${lng}`}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-medium text-on-primary transition hover:opacity-90"
              >
                <Icon name="open_in_new" size={18} />
                Buka peta besar
              </a>
              <a
                href={`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`}
                target="_blank"
                rel="noopener noreferrer"
                className="inline-flex items-center gap-2 rounded-lg border border-outline-variant px-4 py-2.5 text-sm font-medium text-on-surface transition hover:bg-surface-container"
              >
                <Icon name="directions" size={18} />
                Petunjuk arah
              </a>
            </div>
          </section>

          <aside className="space-y-4">
            <div className="rounded-2xl border border-outline-variant bg-surface-container-low p-6 shadow-level1">
              <h2 className="mb-4 flex items-center gap-2 font-heading text-lg font-semibold text-primary">
                <Icon name="account_balance" size={20} />
                Kantor Wali {profil.nama_desa}
              </h2>

              <dl className="space-y-4 text-sm">
                <div>
                  <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                    Alamat
                  </dt>
                  <dd className="mt-1 text-on-surface">{alamatLengkap || "—"}</dd>
                </div>

                <div>
                  <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                    Koordinat
                  </dt>
                  <dd className="mt-1 font-mono tabular-nums text-on-surface">
                    {lat}, {lng}
                  </dd>
                </div>

                {profil.telepon && (
                  <div>
                    <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                      Telepon
                    </dt>
                    <dd className="mt-1 text-on-surface">{profil.telepon}</dd>
                  </div>
                )}

                {profil.email && (
                  <div>
                    <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                      Email
                    </dt>
                    <dd className="mt-1 break-all text-on-surface">{profil.email}</dd>
                  </div>
                )}
              </dl>
            </div>

            <p className="flex gap-2 rounded-xl border border-dashed border-outline-variant p-4 text-xs text-outline">
              <Icon name="info" size={16} className="shrink-0" />
              <span>
                Batas jorong dan titik fasilitas umum belum tersedia — datanya belum diisi
                pada menu Peta di panel admin OpenSID.
              </span>
            </p>
          </aside>
        </div>
      )}
    </main>
  );
}
