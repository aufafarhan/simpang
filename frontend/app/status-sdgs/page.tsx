import type { Metadata } from "next";
import Breadcrumb from "@/components/ui/Breadcrumb";
import Icon from "@/components/ui/Icon";
import { getStatusSdgs } from "@/lib/api";
import type { TujuanSdgs } from "@/lib/types";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  title: "Status SDGs Desa",
  description:
    "Capaian 18 tujuan SDGs Desa di Nagari Simpang beserta skor masing-masing, bersumber dari Kementerian Desa.",
};

/** Warna bilah skor: makin tinggi makin hijau, rendah ke maroon. */
function gayaSkor(skor: number | null): string {
  if (skor === null) return "bg-surface-container";
  if (skor >= 75) return "bg-primary";
  if (skor >= 50) return "bg-primary-container";
  if (skor >= 25) return "bg-secondary-container";

  return "bg-tertiary-container";
}

function KartuTujuan({ t }: { t: TujuanSdgs }) {
  return (
    <li className="flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface shadow-level1 transition-shadow hover:shadow-level2">
      <div className="aspect-square w-full overflow-hidden bg-surface-variant">
        {t.gambar_url ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={t.gambar_url}
            alt={`Tujuan ${t.nomor}: ${t.judul}`}
            className="h-full w-full object-cover"
            loading="lazy"
          />
        ) : (
          <div className="flex h-full items-center justify-center text-outline">
            <Icon name="flag" size={36} />
          </div>
        )}
      </div>

      <div className="flex flex-1 flex-col p-4">
        <span className="text-xs font-semibold uppercase tracking-wider text-outline">
          Tujuan {t.nomor}
        </span>
        <h3 className="mb-3 mt-0.5 line-clamp-2 flex-1 text-sm font-semibold leading-snug text-on-surface">
          {t.judul}
        </h3>

        <div className="mt-auto">
          <div className="mb-1 flex items-baseline justify-between">
            <span className="text-xs text-on-surface-variant">Skor</span>
            <span className="font-heading text-lg font-bold tabular-nums text-primary">
              {t.skor !== null ? t.skor.toFixed(2) : "—"}
            </span>
          </div>
          <div
            className="h-2 w-full overflow-hidden rounded-full bg-surface-container"
            role="img"
            aria-label={`Skor tujuan ${t.nomor}: ${t.skor ?? "tidak tersedia"} dari 100`}
          >
            <div
              className={`h-full rounded-full transition-all ${gayaSkor(t.skor)}`}
              style={{ width: `${Math.min(100, Math.max(0, t.skor ?? 0))}%` }}
            />
          </div>
        </div>
      </div>
    </li>
  );
}

export default async function StatusSdgsPage() {
  const { data, pesan } = await getStatusSdgs();

  return (
    <main className="mx-auto w-full max-w-6xl px-4 py-12 md:px-6 lg:px-8">
      <Breadcrumb
        items={[
          { label: "Beranda", href: "/" },
          { label: "Status Desa" },
          { label: "Status SDGs" },
        ]}
      />

      <header className="mb-8">
        <h1 className="mb-2 font-heading text-3xl font-bold text-primary md:text-4xl">
          SDGs Desa
        </h1>
        <p className="max-w-3xl text-on-surface-variant">
          SDGs Desa adalah 18 tujuan pembangunan berkelanjutan yang disesuaikan untuk tingkat
          desa. Skor di bawah bersumber dari{" "}
          <span className="font-medium text-on-surface">Kementerian Desa</span>.
        </p>
      </header>

      {!data ? (
        <div className="rounded-2xl border border-dashed border-outline-variant p-12 text-center">
          <Icon name="cloud_off" size={48} className="text-outline" />
          <p className="mt-4 font-heading text-xl text-on-surface">Data SDGs belum tersedia</p>
          <p className="mx-auto mt-2 max-w-md text-sm text-on-surface-variant">
            {pesan ?? "Data tidak dapat dimuat saat ini."}
          </p>
          <p className="mt-4 text-xs text-outline">
            Data SDGs diambil dari server Kementerian Desa dan memerlukan koneksi internet.
          </p>
        </div>
      ) : (
        <>
          {/* --- Skor rata-rata --- */}
          {data.skor_rata_rata !== null && (
            <section className="songket-border mb-10 flex flex-col items-center gap-2 rounded-2xl border border-outline-variant bg-surface-container-low p-8 text-center shadow-level1">
              <span className="text-xs uppercase tracking-wider text-on-surface-variant">
                Skor Rata-rata SDGs Desa
              </span>
              <div className="font-heading text-6xl font-bold tabular-nums text-primary">
                {data.skor_rata_rata.toFixed(2)}
              </div>
              <span className="text-sm text-on-surface-variant">
                dari maksimal 100 · mencakup {data.jumlah_tujuan} tujuan
              </span>
            </section>
          )}

          {/* --- Grid 18 tujuan --- */}
          <section>
            <h2 className="songket-border mb-5 flex items-center gap-2 pb-2 font-heading text-2xl font-semibold text-primary">
              <Icon name="flag" />
              Capaian per Tujuan
            </h2>

            <ul className="grid grid-cols-2 gap-5 sm:grid-cols-3 lg:grid-cols-4">
              {data.tujuan.map((t) => (
                <KartuTujuan key={t.nomor} t={t} />
              ))}
            </ul>
          </section>

          <p className="mt-6 text-xs text-outline">
            Sumber: Kementerian Desa (sid.kemendesa.go.id). Data disimpan sementara di server
            nagari dan diperbarui berkala.
          </p>
        </>
      )}
    </main>
  );
}
