import type { ReactNode } from "react";
import Icon from "@/components/ui/Icon";
import type { AgendaItem, JamKerjaHari } from "@/lib/types";

/** Kartu sidebar bergaya Stitch: border tipis, judul kecil dengan ikon. */
export function SidebarCard({
  icon,
  judul,
  children,
}: {
  icon: string;
  judul: string;
  children: ReactNode;
}) {
  return (
    <div className="rounded-xl border border-outline-variant bg-surface p-4 shadow-level1">
      <h3 className="mb-4 flex items-center gap-2 border-b border-outline-variant pb-2 text-sm font-semibold text-primary">
        <Icon name={icon} size={18} />
        {judul}
      </h3>
      {children}
    </div>
  );
}

export function JamLayanan({ data }: { data: JamKerjaHari[] }) {
  if (!data?.length) return null;

  return (
    <SidebarCard icon="schedule" judul="Jam Layanan Kantor">
      <ul className="space-y-2 text-sm">
        {data.map((h) => (
          <li
            key={h.id}
            className={`flex items-center justify-between ${
              h.status ? "text-on-surface" : "font-medium text-error"
            }`}
          >
            <span>{h.nama_hari}</span>
            <span className="font-medium tabular-nums">
              {h.status ? `${h.jam_masuk} – ${h.jam_keluar}` : "Tutup"}
            </span>
          </li>
        ))}
      </ul>
    </SidebarCard>
  );
}

export function AgendaMendatang({
  data,
}: {
  data: { hari_ini: AgendaItem[]; yad: AgendaItem[]; lama: AgendaItem[] };
}) {
  const items = [...(data?.hari_ini ?? []), ...(data?.yad ?? [])].slice(0, 5);

  return (
    <SidebarCard icon="event" judul="Agenda Mendatang">
      {items.length === 0 ? (
        <p className="text-sm text-on-surface-variant">Belum ada agenda terjadwal.</p>
      ) : (
        <div className="flex flex-col gap-2">
          {items.map((a, i) => {
            const tgl = a.tgl_mulai ? new Date(String(a.tgl_mulai)) : null;

            return (
              <div
                key={i}
                className="group flex gap-4 rounded p-1 transition-colors hover:bg-surface-container-low"
              >
                <div className="flex min-w-[50px] flex-col items-center justify-center rounded border border-outline-variant bg-surface-container p-1 transition-colors group-hover:border-primary group-hover:bg-primary group-hover:text-on-primary">
                  <span className="text-[10px] uppercase">
                    {tgl
                      ? tgl.toLocaleDateString("id-ID", { month: "short" })
                      : "—"}
                  </span>
                  <span className="font-heading text-lg leading-none">
                    {tgl ? tgl.getDate() : "–"}
                  </span>
                </div>
                <div>
                  <h4 className="line-clamp-1 text-sm font-semibold text-on-surface group-hover:text-primary">
                    {String(a.judul ?? a.nama ?? "Agenda")}
                  </h4>
                  {!!a.lokasi && (
                    <p className="mt-0.5 flex items-center gap-1 text-xs text-on-surface-variant">
                      <Icon name="location_on" size={14} />
                      {String(a.lokasi)}
                    </p>
                  )}
                </div>
              </div>
            );
          })}
        </div>
      )}
    </SidebarCard>
  );
}
