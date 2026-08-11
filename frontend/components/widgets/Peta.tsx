import { SidebarCard } from "@/components/beranda/SidebarWidgets";
import { EMBED_PETA_KANTOR, urlPetaBesar } from "@/lib/peta";
import type { PetaKoordinat } from "@/lib/types";

/**
 * Peta lokasi kantor nagari — embed Google Maps, sumbernya satu di lib/peta.ts
 * agar sama persis dengan halaman /peta. Tanpa library tambahan.
 */
export default function Peta({
  peta,
  namaDesa,
}: {
  peta?: PetaKoordinat;
  namaDesa: string;
}) {
  if (!peta?.lat || !peta?.lng) return null;

  const { lat, lng } = peta;

  return (
    <SidebarCard icon="location_on" judul="Peta Lokasi Kantor">
      <div className="overflow-hidden rounded-lg border border-outline-variant">
        <iframe
          src={EMBED_PETA_KANTOR}
          title={`Peta lokasi kantor ${namaDesa}`}
          className="h-56 w-full"
          style={{ border: 0 }}
          allowFullScreen
          loading="lazy"
          referrerPolicy="strict-origin-when-cross-origin"
        />
      </div>
      <a
        href={urlPetaBesar(lat, lng)}
        target="_blank"
        rel="noopener noreferrer"
        className="mt-3 inline-block text-sm font-medium text-primary hover:underline"
      >
        Buka peta lebih besar →
      </a>
    </SidebarCard>
  );
}
