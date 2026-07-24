import Link from "next/link";
import WidgetCard from "./WidgetCard";
import type {
  AgendaItem,
  JamKerjaHari,
  KategoriMenu,
  SosialMedia,
  StatistikPengunjung as TStatPengunjung,
} from "@/lib/types";

export function JamKerja({ data }: { data: JamKerjaHari[] }) {
  if (!data?.length) return null;

  return (
    <WidgetCard judul="Jam Kerja">
      <table className="w-full text-sm">
        <thead>
          <tr className="text-left text-outline">
            <th className="pb-2 font-medium">Hari</th>
            <th className="pb-2 font-medium">Mulai</th>
            <th className="pb-2 font-medium">Selesai</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-outline-variant">
          {data.map((h) => (
            <tr key={h.id}>
              <td className="py-2 text-on-surface-variant">{h.nama_hari}</td>
              {h.status ? (
                <>
                  <td className="py-2 tabular-nums text-on-surface-variant">{h.jam_masuk}</td>
                  <td className="py-2 tabular-nums text-on-surface-variant">{h.jam_keluar}</td>
                </>
              ) : (
                <td className="py-2 text-outline" colSpan={2}>
                  Libur
                </td>
              )}
            </tr>
          ))}
        </tbody>
      </table>
    </WidgetCard>
  );
}

export function StatistikPengunjung({ data }: { data: TStatPengunjung }) {
  if (!data) return null;

  const baris = [
    ["Hari ini", data.hari_ini],
    ["Kemarin", data.kemarin],
    ["Total pengunjung", data.total],
  ] as const;

  return (
    <WidgetCard judul="Statistik Pengunjung">
      <dl className="divide-y divide-outline-variant text-sm">
        {baris.map(([label, nilai]) => (
          <div key={label} className="flex justify-between py-2">
            <dt className="text-on-surface-variant">{label}</dt>
            <dd className="font-semibold tabular-nums text-primary">
              {nilai.toLocaleString("id-ID")}
            </dd>
          </div>
        ))}
      </dl>
    </WidgetCard>
  );
}

export function MediaSosial({ data }: { data: SosialMedia[] }) {
  const items = (data ?? []).filter((s) => s.link);
  if (!items.length) return null;

  return (
    <WidgetCard judul="Media Sosial">
      <div className="flex flex-wrap gap-3">
        {items.map((s) => (
          <a
            key={s.nama}
            href={s.link}
            target="_blank"
            rel="noopener noreferrer"
            title={s.nama}
            className="rounded-lg p-1 transition hover:bg-surface-container"
          >
            {s.icon ? (
              // eslint-disable-next-line @next/next/no-img-element
              <img src={s.icon} alt={s.nama} className="h-10 w-10 object-contain" />
            ) : (
              <span className="text-sm text-primary">{s.nama}</span>
            )}
          </a>
        ))}
      </div>
    </WidgetCard>
  );
}

export function MenuKategori({ data }: { data: KategoriMenu[] }) {
  if (!data?.length) return null;

  return (
    <WidgetCard judul="Kategori">
      <ul className="divide-y divide-outline-variant text-sm">
        {data.map((k) => (
          <li key={k.id}>
            <Link
              href={`/berita?kategori=${k.slug}`}
              className="block py-2 text-on-surface-variant transition hover:text-primary"
            >
              {k.kategori}
            </Link>
            {!!k.submenu?.length && (
              <ul className="ml-4 border-l border-outline-variant pl-3">
                {k.submenu.map((s) => (
                  <li key={s.id}>
                    <Link
                      href={`/berita?kategori=${s.slug}`}
                      className="block py-1.5 text-outline transition hover:text-primary"
                    >
                      {s.kategori}
                    </Link>
                  </li>
                ))}
              </ul>
            )}
          </li>
        ))}
      </ul>
    </WidgetCard>
  );
}

export function Agenda({
  data,
}: {
  data: { hari_ini: AgendaItem[]; yad: AgendaItem[]; lama: AgendaItem[] };
}) {
  const mendatang = [...(data?.hari_ini ?? []), ...(data?.yad ?? [])];

  return (
    <WidgetCard judul="Agenda">
      {mendatang.length === 0 ? (
        <p className="text-sm text-outline">Belum ada agenda terjadwal.</p>
      ) : (
        <ul className="space-y-3 text-sm">
          {mendatang.slice(0, 5).map((a, i) => (
            <li key={i} className="border-l-2 border-primary pl-3">
              <p className="font-medium text-on-surface">
                {String(a.judul ?? a.nama ?? "Agenda")}
              </p>
              {!!a.tgl_mulai && (
                <p className="text-outline">
                  {new Date(String(a.tgl_mulai)).toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "long",
                    year: "numeric",
                  })}
                </p>
              )}
            </li>
          ))}
        </ul>
      )}
    </WidgetCard>
  );
}
