/**
 * URL panel admin OpenSID.
 *
 * Diturunkan dari NEXT_PUBLIC_API_URL yang sudah ada — panel admin selalu
 * satu host dengan API, jadi tidak perlu variabel lingkungan baru yang bisa
 * lupa diisi saat deploy.
 *
 *   http://localhost:8000/index.php/api/v1  ->  http://localhost:8000/index.php/siteman
 *   https://domain/api/v1                   ->  https://domain/siteman
 */
export function urlPanelAdmin(): string | null {
  const api = process.env.NEXT_PUBLIC_API_URL;

  if (!api) return null;

  // Buang "/api/v1" di ujung; "/index.php" (server dev PHP) sengaja dibiarkan.
  return api.replace(/\/api\/v1\/?$/, "") + "/siteman";
}
