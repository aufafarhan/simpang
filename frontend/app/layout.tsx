import type { Metadata } from "next";
import { Lexend, Source_Sans_3 } from "next/font/google";
import "./globals.css";
import Header from "@/components/layout/Header";
import Footer from "@/components/layout/Footer";
import { getProfilDesa } from "@/lib/api";

// Font sesuai rekomendasi design system: Lexend (judul) + Source Sans 3 (isi).
const lexend = Lexend({
  variable: "--font-heading",
  subsets: ["latin"],
  weight: ["400", "500", "600", "700"],
});

const sourceSans = Source_Sans_3({
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
      className={`${lexend.variable} ${sourceSans.variable} h-full antialiased`}
    >
      <body className="min-h-full flex flex-col bg-white text-slate-800">
        <Header profil={profil} />
        <main className="flex-1 w-full">{children}</main>
        <Footer profil={profil} />
      </body>
    </html>
  );
}
