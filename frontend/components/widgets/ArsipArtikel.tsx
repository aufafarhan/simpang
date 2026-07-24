"use client";

import Link from "next/link";
import { useState } from "react";
import { SidebarCard } from "@/components/beranda/SidebarWidgets";
import type { ArsipItem } from "@/lib/types";

type Tab = "terkini" | "populer" | "acak";

const LABEL: Record<Tab, string> = {
  terkini: "Terkini",
  populer: "Populer",
  acak: "Acak",
};

export default function ArsipArtikel({
  data,
}: {
  data: { terkini: ArsipItem[]; populer: ArsipItem[]; acak: ArsipItem[] };
}) {
  const [tab, setTab] = useState<Tab>("terkini");
  const items = data?.[tab] ?? [];

  return (
    <SidebarCard icon="folder_open" judul="Arsip Artikel">
      <div className="mb-3 flex gap-1 border-b border-outline-variant" role="tablist">
        {(Object.keys(LABEL) as Tab[]).map((t) => (
          <button
            key={t}
            role="tab"
            aria-selected={tab === t}
            onClick={() => setTab(t)}
            className={`-mb-px cursor-pointer border-b-2 px-3 py-2 text-sm font-medium transition ${
              tab === t
                ? "border-primary text-primary"
                : "border-transparent text-outline hover:text-primary"
            }`}
          >
            {LABEL[t]}
          </button>
        ))}
      </div>

      {items.length === 0 ? (
        <p className="text-sm text-outline">Belum ada artikel.</p>
      ) : (
        <ul className="divide-y divide-outline-variant">
          {items.slice(0, 7).map((a, i) => (
            <li key={a.id ?? i} className="py-2">
              <Link
                href={a.url ?? "#"}
                className="block text-sm leading-snug text-on-surface-variant transition hover:text-primary"
              >
                {a.judul}
              </Link>
              {a.tanggal && (
                <span className="text-xs text-outline">
                  {new Date(a.tanggal).toLocaleDateString("id-ID", {
                    day: "numeric",
                    month: "short",
                    year: "numeric",
                  })}
                </span>
              )}
            </li>
          ))}
        </ul>
      )}
    </SidebarCard>
  );
}
