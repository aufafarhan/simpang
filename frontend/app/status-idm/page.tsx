import type { Metadata } from "next";
import { Fragment } from "react";
import PilihTahunIdm from "@/components/status/PilihTahunIdm";
import Breadcrumb from "@/components/ui/Breadcrumb";
import Icon from "@/components/ui/Icon";
import { getStatusIdm } from "@/lib/api";
import type { IndikatorIdm } from "@/lib/types";

export const metadata: Metadata = {
  // Nama nagari ditambahkan otomatis oleh template judul di app/layout.tsx.
  title: "Status IDM",
  description:
    "Indeks Desa Membangun (IDM) Nagari Simpang — skor, status, dan rincian indikator dari Kementerian Desa.",
};

// Di Next.js 16 searchParams adalah Promise — wajib di-await.
type Search = Promise<{ tahun?: string }>;

/** Warna badge mengikuti tingkatan status IDM resmi Kemendesa. */
function gayaStatus(status: string | null): string {
  const s = (status ?? "").toUpperCase();
  if (s.includes("MANDIRI")) return "bg-primary text-on-primary";
  if (s.includes("MAJU")) return "bg-primary-container text-on-primary-container";
  if (s.includes("BERKEMBANG")) return "bg-secondary-container text-on-secondary-container";
  if (s.includes("TERTINGGAL")) return "bg-tertiary-container text-on-tertiary";

  return "bg-surface-container text-on-surface-variant";
}

/**
 * Nomor indikator IDM BERULANG karena dibagi per dimensi — `no` kembali ke 1
 * setiap masuk dimensi baru (55 indikator, hanya 36 nomor unik). Titik reset itu
 * dipakai untuk memisahkan kelompok.
 *
 * Urutan dimensi IDM baku: Sosial (IKS) -> Ekonomi (IKE) -> Lingkungan (IKL),
 * dan cocok dengan isi data. Label hanya dipasang bila jumlah kelompoknya
 * benar-benar 3; kalau tidak, tampilkan tanpa label agar tidak salah menamai.
 */
const NAMA_DIMENSI = [
  "Indeks Ketahanan Sosial",
  "Indeks Ketahanan Ekonomi",
  "Indeks Ketahanan Lingkungan",
];

function kelompokkanIndikator(items: IndikatorIdm[]): { label: string | null; isi: IndikatorIdm[] }[] {
  const kelompok: IndikatorIdm[][] = [];

  for (const it of items) {
    if (it.no === 1 || kelompok.length === 0) kelompok.push([it]);
    else kelompok[kelompok.length - 1].push(it);
  }

  return kelompok.map((isi, i) => ({
    label: kelompok.length === NAMA_DIMENSI.length ? NAMA_DIMENSI[i] : null,
    isi,
  }));
}

/**
 * Tabel indikator mengikuti struktur tema OpenSID lama
 * (vendor/themes/esensi/partials/idm/index.php): 12 kolom dengan header
 * bertingkat — enam kolom terakhir dikelompokkan di bawah
 * "Yang Dapat Melaksanakan Kegiatan".
 */
const PELAKSANA: { kunci: keyof IndikatorIdm["pelaksana"]; label: string }[] = [
  { kunci: "pusat", label: "Pusat" },
  { kunci: "provinsi", label: "Provinsi" },
  { kunci: "kabupaten", label: "Kabupaten" },
  { kunci: "desa", label: "Desa" },
  { kunci: "csr", label: "CSR" },
  { kunci: "lainnya", label: "Lainnya" },
];

function TabelIndikator({
  kelompok,
}: {
  kelompok: { label: string | null; isi: IndikatorIdm[] }[];
}) {
  const th = "px-3 py-2 text-left font-semibold";
  const td = "px-3 py-2 align-top text-on-surface-variant";

  return (
    // Tabel ini lebar (12 kolom) — digulir di dalam wadahnya sendiri
    // agar body halaman tidak ikut menggulir mendatar.
    <div className="overflow-x-auto rounded-xl border border-outline-variant shadow-level1">
      <table className="w-full min-w-[1100px] border-collapse text-sm">
        <thead className="bg-primary text-on-primary">
          <tr>
            <th rowSpan={2} className={`${th} w-12 text-center`}>
              No
            </th>
            <th rowSpan={2} className={`${th} min-w-[190px]`}>
              Indikator IDM
            </th>
            <th rowSpan={2} className={`${th} w-16 text-center`}>
              Skor
            </th>
            <th rowSpan={2} className={`${th} min-w-[240px]`}>
              Keterangan
            </th>
            <th rowSpan={2} className={`${th} min-w-[180px]`}>
              Kegiatan yang Dapat Dilakukan
            </th>
            <th rowSpan={2} className={`${th} w-20 text-center`}>
              +Nilai
            </th>
            <th
              colSpan={PELAKSANA.length}
              className="border-l border-on-primary/20 px-3 py-2 text-center font-semibold"
            >
              Yang Dapat Melaksanakan Kegiatan
            </th>
          </tr>
          <tr className="bg-primary-container text-on-primary-container">
            {PELAKSANA.map((p) => (
              <th
                key={p.kunci}
                className="border-l border-on-primary/20 px-3 py-1.5 text-left text-xs font-semibold"
              >
                {p.label}
              </th>
            ))}
          </tr>
        </thead>

        <tbody>
          {kelompok.map((k, iK) => (
            <Fragment key={iK}>
              {k.label && (
                <tr className="bg-secondary-container">
                  <td
                    colSpan={6 + PELAKSANA.length}
                    className="px-3 py-2 text-xs font-bold uppercase tracking-wider text-on-secondary-container"
                  >
                    {k.label}{" "}
                    <span className="font-normal normal-case tracking-normal">
                      ({k.isi.length} indikator)
                    </span>
                  </td>
                </tr>
              )}

              {k.isi.map((i, iI) => (
                // `no` berulang antar dimensi -> gabungkan dengan indeks agar key unik
                <tr
                  key={`${iK}-${i.no}-${iI}`}
                  className="border-t border-outline-variant odd:bg-surface even:bg-surface-container-low"
                >
                  <td className={`${td} text-center tabular-nums`}>{i.no}</td>
                  <td className={`${td} font-semibold text-on-surface`}>{i.indikator}</td>
                  <td className={`${td} text-center font-bold tabular-nums text-primary`}>
                    {i.skor ?? "—"}
                  </td>
                  <td className={td}>{i.keterangan || "—"}</td>
                  <td className={td}>{i.kegiatan || "—"}</td>
                  <td className={`${td} text-center tabular-nums`}>{i.nilai ?? "—"}</td>

                  {PELAKSANA.map((p) => (
                    <td
                      key={p.kunci}
                      className={`${td} border-l border-outline-variant text-xs`}
                    >
                      {i.pelaksana?.[p.kunci] ?? "—"}
                    </td>
                  ))}
                </tr>
              ))}
            </Fragment>
          ))}
        </tbody>
      </table>
    </div>
  );
}

export default async function StatusIdmPage({ searchParams }: { searchParams: Search }) {
  const sp = await searchParams;
  const tahunDiminta = sp.tahun ? Number(sp.tahun) : undefined;

  const { data, pesan } = await getStatusIdm(tahunDiminta);

  return (
    <main className="mx-auto w-full max-w-6xl px-4 py-12 md:px-6 lg:px-8">
      <Breadcrumb
        items={[
          { label: "Beranda", href: "/" },
          { label: "Status Desa" },
          { label: "Status IDM" },
        ]}
      />

      <header className="mb-8">
        <h1 className="mb-2 font-heading text-3xl font-bold text-primary md:text-4xl">
          Indeks Desa Membangun (IDM)
        </h1>
        <p className="max-w-3xl text-on-surface-variant">
          IDM mengukur tingkat kemajuan dan kemandirian nagari dari tiga dimensi: sosial,
          ekonomi, dan lingkungan. Angka di bawah bersumber langsung dari{" "}
          <span className="font-medium text-on-surface">Kementerian Desa</span>.
        </p>
      </header>

      {/* --- Data tidak tersedia: tampilkan apa adanya, tanpa angka karangan --- */}
      {!data ? (
        <div className="rounded-2xl border border-dashed border-outline-variant p-12 text-center">
          <Icon name="cloud_off" size={48} className="text-outline" />
          <p className="mt-4 font-heading text-xl text-on-surface">Data IDM belum tersedia</p>
          <p className="mx-auto mt-2 max-w-md text-sm text-on-surface-variant">
            {pesan ?? "Data tidak dapat dimuat saat ini."}
          </p>
          <p className="mt-4 text-xs text-outline">
            Data IDM diambil dari server Kementerian Desa dan memerlukan koneksi internet.
          </p>
        </div>
      ) : (
        <>
          {/* --- Pemilih tahun (dropdown) --- */}
          <PilihTahunIdm tahun={data.tahun} tahunTersedia={data.tahun_tersedia} />

          {/* --- Ringkasan skor --- */}
          <section className="songket-border mb-10 grid gap-6 rounded-2xl border border-outline-variant bg-surface-container-low p-6 shadow-level1 md:grid-cols-3 md:p-8">
            <div className="md:col-span-1">
              <span className="text-xs uppercase tracking-wider text-on-surface-variant">
                Skor IDM {data.tahun}
              </span>
              <div className="mt-1 font-heading text-5xl font-bold tabular-nums text-primary">
                {data.skor.toFixed(4)}
              </div>
              {data.status && (
                <span
                  className={`mt-3 inline-block rounded-full px-4 py-1.5 text-sm font-bold ${gayaStatus(data.status)}`}
                >
                  {data.status}
                </span>
              )}
            </div>

            <dl className="grid grid-cols-2 gap-4 md:col-span-2 md:grid-cols-3">
              {data.target_status && (
                <div>
                  <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                    Target Status
                  </dt>
                  <dd className="mt-1 font-heading text-xl font-semibold text-on-surface">
                    {data.target_status}
                  </dd>
                </div>
              )}
              {data.skor_minimal !== null && (
                <div>
                  <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                    Skor Minimal Target
                  </dt>
                  <dd className="mt-1 font-heading text-xl font-semibold tabular-nums text-on-surface">
                    {data.skor_minimal.toFixed(4)}
                  </dd>
                </div>
              )}
              {data.penambahan !== null && (
                <div>
                  <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                    Perlu Ditingkatkan
                  </dt>
                  <dd className="mt-1 font-heading text-xl font-semibold tabular-nums text-secondary">
                    +{data.penambahan.toFixed(4)}
                  </dd>
                </div>
              )}
              <div className="col-span-2 md:col-span-3">
                <dt className="text-xs uppercase tracking-wider text-on-surface-variant">
                  Wilayah
                </dt>
                <dd className="mt-1 text-sm text-on-surface">
                  {[
                    data.wilayah.desa,
                    data.wilayah.kecamatan,
                    data.wilayah.kabupaten,
                    data.wilayah.provinsi,
                  ]
                    .filter(Boolean)
                    .join(" · ")}
                </dd>
              </div>
            </dl>
          </section>

          {/* --- Rincian indikator --- */}
          <section>
            <h2 className="songket-border mb-5 flex items-center gap-2 pb-2 font-heading text-2xl font-semibold text-primary">
              <Icon name="checklist" />
              Rincian Indikator
              <span className="ml-1 text-base font-normal text-on-surface-variant">
                ({data.indikator.length})
              </span>
            </h2>

            {data.indikator.length === 0 ? (
              <p className="rounded-xl border border-dashed border-outline-variant p-8 text-center text-on-surface-variant">
                Rincian indikator tidak tersedia untuk tahun ini.
              </p>
            ) : (
              <>
                <TabelIndikator kelompok={kelompokkanIndikator(data.indikator)} />
                <p className="mt-2 flex items-center gap-1 text-xs text-outline md:hidden">
                  <Icon name="swipe" size={14} />
                  Geser tabel ke samping untuk melihat seluruh kolom.
                </p>
              </>
            )}
          </section>

          <p className="mt-6 text-xs text-outline">
            Sumber: Kementerian Desa (idm.kemendesa.go.id). Data disimpan sementara di server
            nagari dan diperbarui berkala.
          </p>
        </>
      )}
    </main>
  );
}
