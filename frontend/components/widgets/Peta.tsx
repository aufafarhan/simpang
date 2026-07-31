import { SidebarCard } from "@/components/beranda/SidebarWidgets";
import type { PetaKoordinat } from "@/lib/types";

/**
 * Peta lokasi kantor desa memakai embed OpenStreetMap (tanpa library tambahan).
 * Bisa diganti Leaflet/MapLibre bila nanti butuh layer & marker kustom.
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
  const d = 0.01; // luas area yang ditampilkan
  const bbox = [lng - d, lat - d, lng + d, lat + d].join("%2C");
  const src = `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${lat}%2C${lng}`;

  return (
    <SidebarCard icon="location_on" judul="Peta Lokasi Kantor">
      <div className="overflow-hidden rounded-lg border border-outline-variant">
        <iframe
          src={src}
          title={`Peta lokasi kantor ${namaDesa}`}
          className="h-56 w-full"
          loading="lazy"
        />
      </div>
      <a
        href={`https://www.openstreetmap.org/?mlat=${lat}&mlon=${lng}#map=16/${lat}/${lng}`}
        target="_blank"
        rel="noopener noreferrer"
        className="mt-3 inline-block text-sm font-medium text-primary hover:underline"
      >
        Buka peta lebih besar →
      </a>
    </SidebarCard>
  );
}
