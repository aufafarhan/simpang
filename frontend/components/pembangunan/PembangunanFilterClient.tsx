"use client";

import { useRouter, useSearchParams } from "next/navigation";
import Icon from "@/components/ui/Icon";

const LIST_STATUS = [
  { value: "", label: "Semua Status" },
  { value: "Dalam Proses", label: "Dalam Proses" },
  { value: "Selesai", label: "Selesai" },
  { value: "Perencanaan", label: "Perencanaan" },
];

export default function PembangunanFilterClient({
  statusSaatIni,
}: {
  statusSaatIni: string;
}) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const handleStatusChange = (val: string) => {
    const params = new URLSearchParams(searchParams.toString());
    if (val) {
      params.set("status", val);
    } else {
      params.delete("status");
    }
    params.delete("page");
    router.push(`/pembangunan?${params.toString()}`);
  };

  return (
    <div className="flex flex-wrap items-center gap-2">
      <span className="mr-1 flex items-center gap-1 text-xs font-semibold text-on-surface-variant">
        <Icon name="filter_list" size={16} /> Filter:
      </span>
      {LIST_STATUS.map((st) => {
        const active = statusSaatIni === st.value;
        return (
          <button
            key={st.value}
            onClick={() => handleStatusChange(st.value)}
            className={`rounded-full px-3 py-1 text-xs font-semibold transition ${
              active
                ? "bg-primary text-on-primary shadow-sm"
                : "border border-outline-variant bg-surface-container text-on-surface-variant hover:bg-surface-container-high"
            }`}
          >
            {st.label}
          </button>
        );
      })}
    </div>
  );
}
