import Link from "next/link";
import type { ProfilDesa } from "@/lib/types";

export default function Header({ profil }: { profil: ProfilDesa }) {
  const menu = [...profil.menu].sort((a, b) => a.urut - b.urut);

  return (
    <header className="sticky top-0 z-40 bg-navy-900 text-white shadow">
      <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
        <Link href="/" className="flex items-center gap-3">
          <div className="flex h-10 w-10 items-center justify-center rounded-full bg-white/20 text-lg font-bold">
            {profil.nama_desa.charAt(0)}
          </div>
          <div className="leading-tight">
            <div className="text-sm uppercase tracking-wide opacity-80">
              {profil.sebutan_desa}
            </div>
            <div className="text-lg font-bold">{profil.nama_desa}</div>
          </div>
        </Link>

        <nav className="hidden gap-1 md:flex">
          {menu.map((m) => (
            <Link
              key={m.url}
              href={m.url}
              className="rounded px-3 py-2 text-sm font-medium hover:bg-white/15"
            >
              {m.judul}
            </Link>
          ))}
        </nav>
      </div>

      {/* menu mobile sederhana */}
      <nav className="flex gap-1 overflow-x-auto border-t border-white/15 px-4 py-2 md:hidden">
        {menu.map((m) => (
          <Link
            key={m.url}
            href={m.url}
            className="whitespace-nowrap rounded px-3 py-1 text-sm hover:bg-white/15"
          >
            {m.judul}
          </Link>
        ))}
      </nav>
    </header>
  );
}
