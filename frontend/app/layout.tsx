import type { Metadata } from "next";
import { Playfair_Display, Plus_Jakarta_Sans } from "next/font/google";
import "./globals.css";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { getProfilDesa } from "@/lib/api";

// Font sesuai DESIGN.md: Playfair Display (judul, serif) + Plus Jakarta Sans (isi).
const playfair = Playfair_Display({
  variable: "--font-heading",
  subsets: ["latin"],
  weight: ["400", "600", "700"],
});

const jakarta = Plus_Jakarta_Sans({
  variable: "--font-body",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
});

export async function generateMetadata(): Promise<Metadata> {
  const profil = await getProfilDesa();
  return {
    title: {
      default: `${profil.tema.judul_web}`,
      template: `%s — ${profil.nama_desa}`,
    },
    description: `Website resmi ${profil.sebutan_desa} ${profil.nama_desa}.`,
    metadataBase: new URL(process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000"),
  };
}

export default async function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  const profil = await getProfilDesa();

  return (
    <html
      lang="id"
      className={`${playfair.variable} ${jakarta.variable} h-full antialiased`}
    >
      <head>
        {/* Ikon Material Symbols — dipakai design system Stitch */}
        <link
          rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap"
        />
      </head>
      <body className="min-h-full flex flex-col bg-background text-on-surface">
        <Header profil={profil} />
        <main className="flex-1 w-full">{children}</main>
        <Footer profil={profil} />
      </body>
    </html>
  );
}
