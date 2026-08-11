/**
 * Sumber peta untuk seluruh situs.
 *
 * Embed Google Maps yang menunjuk tempat terdaftar "Kantor Wali Nagari Simpang"
 * — penanda dan namanya sudah benar, bukan sekadar titik koordinat.
 *
 * ⚠️ Parameter `pb` MENGUNCI lokasi di dalam URL, jadi peta ini tidak mengikuti
 * lat/lng di tabel `config`. Kalau kantor nagari pindah, ambil URL baru dari
 * Google Maps (Bagikan → Sematkan peta) lalu ganti konstanta di bawah.
 * Koordinat dari database tetap dipakai untuk tautan Petunjuk Arah.
 */
export const EMBED_PETA_KANTOR =
  "https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1051.7040016173466!2d100.15603636022733!3d0.00585424423984973!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x302a9df123ed085b%3A0xa753c8bb0f306f0b!2sKantor%20wali%20nagari%20simpang!5e0!3m2!1sid!2sid!4v1786421798321!5m2!1sid!2sid";

/** Buka lokasi di Google Maps (tab baru). */
export function urlPetaBesar(lat: number, lng: number): string {
  return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
}

/** Rute menuju lokasi dari posisi pengguna. */
export function urlPetunjukArah(lat: number, lng: number): string {
  return `https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`;
}
