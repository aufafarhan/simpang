import Link from "next/link";
import type { MenuAtas, ProfilDesa } from "@/lib/types";

/**
 * Tautan menu OpenSID bisa berupa URL absolut ke halaman PHP lama.
 * Yang sudah punya padanan di frontend baru dipetakan ke rute JS.
 */
const PETA_RUTE: Record<string, string> = {
  Beranda: "/",
  "Data Statistik": "/statistik",
  Statistik: "/statistik",
  Galeri: "/galeri",
  "Galeri Foto": "/galeri",
  Pemerintahan: "/pemerintah-nagari",
  "Pemerintah Nagari": "/pemerintah-nagari",
  "Pemerintah Desa": "/pemerintah-nagari",
  Pemerintah: "/pemerintah-nagari",
  SOTK: "/sotk",
  "Struktur Organisasi": "/sotk",
  Pembangunan: "/pembangunan",
  "Pembangunan Desa": "/pembangunan",
  "Lapak UMKM": "/lapak-umkm",
  Lapak: "/lapak-umkm",
  UMKM: "/lapak-umkm",
  Pengaduan: "/pengaduan",
  "Buku Tamu": "/buku-tamu",
  Profil: "/profil",
  "Profil Desa": "/profil",
  "Profil Nagari": "/profil",
};

function hrefMenu(m: MenuAtas): string {
  if (PETA_RUTE[m.nama]) return PETA_RUTE[m.nama];

  const namaLower = (m.nama || "").toLowerCase().trim();
  const linkLower = (m.link || "").toLowerCase().trim();

  if (namaLower.includes("pemerintah")) return "/pemerintah-nagari";
  if (namaLower.includes("sotk") || namaLower.includes("struktur organisasi")) return "/sotk";
  if (namaLower.includes("pembangunan")) return "/pembangunan";
  if (namaLower.includes("galeri")) return "/galeri";
  if (namaLower.includes("statistik")) return "/statistik";
  if (namaLower.includes("umkm") || namaLower === "lapak") return "/lapak-umkm";
  if (namaLower.includes("pengaduan")) return "/pengaduan";

  if (linkLower.includes("/pemerintah")) return "/pemerintah-nagari";
  if (linkLower.includes("/sotk")) return "/sotk";
  if (linkLower.includes("/pembangunan")) return "/pembangunan";
  if (linkLower.includes("/galeri")) return "/galeri";
  if (linkLower.includes("/statistik")) return "/statistik";
  if (linkLower.includes("/lapak")) return "/lapak-umkm";

  if (!m.link || m.link === "#") return "#";

  return m.link;
}

function isEksternal(href: string): boolean {
  return href.startsWith("http");
}

function ItemMenu({ m }: { m: MenuAtas }) {
  const href = hrefMenu(m);
  const punyaAnak = !!m.submenu?.length;

  const kelas =
    "rounded-lg px-3 py-2 text-sm font-medium text-on-surface-variant transition hover:bg-surface-container hover:text-primary";

  return (
    <li className="group relative">
      {isEksternal(href) ? (
        <a href={href} className={kelas}>
          {m.nama}
          {punyaAnak && <span aria-hidden> ▾</span>}
        </a>
      ) : (
        <Link href={href} className={kelas}>
          {m.nama}
          {punyaAnak && <span aria-hidden> ▾</span>}
        </Link>
      )}

      {punyaAnak && (
        <ul className="invisible absolute left-0 top-full z-50 min-w-52 rounded-lg border border-outline-variant bg-surface-container-lowest py-1 opacity-0 shadow-level2 transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
          {m.submenu!.map((s) => {
            const sh = hrefMenu(s);
            const subKelas =
              "block px-4 py-2 text-sm text-on-surface-variant transition hover:bg-surface-container hover:text-primary";

            return (
              <li key={s.id}>
                {isEksternal(sh) ? (
                  <a href={sh} className={subKelas}>
                    {s.nama}
                  </a>
                ) : (
                  <Link href={sh} className={subKelas}>
                    {s.nama}
                  </Link>
                )}
              </li>
            );
          })}
        </ul>
      )}
    </li>
  );
}

export default function Header({ profil }: { profil: ProfilDesa }) {
  const menu = profil.menu_atas?.length
    ? profil.menu_atas
    : profil.menu.map((m, i) => ({ id: String(i), nama: m.judul, link: m.url }));

  return (
    <header className="sticky top-0 z-50 border-b border-outline-variant bg-background">
      <div className="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
        <Link href="/" className="flex items-center gap-3">
          {profil.logo_url ? (
            // eslint-disable-next-line @next/next/no-img-element
            <img
              src={profil.logo_url}
              alt={`Logo ${profil.nama_desa}`}
              className="h-11 w-11 object-contain"
            />
          ) : (
            <span className="flex h-11 w-11 items-center justify-center rounded-full bg-primary text-lg font-bold text-on-primary">
              {profil.nama_desa.charAt(0)}
            </span>
          )}
          <span className="leading-tight">
            <span className="block text-[11px] uppercase tracking-widest text-on-surface-variant">
              {profil.sebutan_desa}
            </span>
            <span className="block font-heading text-lg font-bold text-primary">
              {profil.nama_desa}
            </span>
          </span>
        </Link>

        <nav aria-label="Menu utama" className="hidden lg:block">
          <ul className="flex items-center gap-0.5">
            {menu.map((m) => (
              <ItemMenu key={m.id} m={m} />
            ))}
          </ul>
        </nav>
      </div>

      {/* Menu ringkas untuk layar kecil */}
      <nav
        aria-label="Menu ringkas"
        className="border-t border-outline-variant bg-surface-container-low lg:hidden"
      >
        <ul className="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-4 py-2">
          {menu.map((m) => {
            const href = hrefMenu(m);
            const kelas =
              "block whitespace-nowrap rounded-lg px-3 py-1.5 text-sm text-on-surface-variant hover:bg-surface-container hover:text-primary";

            return (
              <li key={m.id} className="shrink-0">
                {isEksternal(href) ? (
                  <a href={href} className={kelas}>
                    {m.nama}
                  </a>
                ) : (
                  <Link href={href} className={kelas}>
                    {m.nama}
                  </Link>
                )}
              </li>
            );
          })}
        </ul>
      </nav>
    </header>
  );
}
