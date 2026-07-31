import Link from "next/link";
import Icon from "@/components/ui/Icon";
import SectionHeading from "./SectionHeading";

/** Pintasan layanan — hanya menautkan halaman yang benar-benar ada. */
const PINTASAN = [
  { label: "Statistik", icon: "monitoring", href: "/statistik" },
  { label: "Berita", icon: "newspaper", href: "/berita" },
  { label: "Profil", icon: "assured_workload", href: "/profil" },
  { label: "Buku Tamu", icon: "forum", href: "/buku-tamu" },
];

export default function QuickShortcuts() {
  return (
    <section>
      <SectionHeading icon="widgets">Layanan Cepat</SectionHeading>

      <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
        {PINTASAN.map((p) => (
          <Link
            key={p.label}
            href={p.href}
            className="group flex flex-col items-center justify-center gap-2 rounded-xl border border-outline-variant bg-surface p-4 text-center transition-all hover:border-primary hover:shadow-level2"
          >
            <span className="rounded-full bg-surface-container p-4 text-primary transition-colors group-hover:bg-primary group-hover:text-on-primary">
              <Icon name={p.icon} size={28} filled />
            </span>
            <span className="text-sm font-semibold text-on-surface">{p.label}</span>
          </Link>
        ))}
      </div>
    </section>
  );
}
