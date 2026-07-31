import type { NextConfig } from "next";

const nextConfig: NextConfig = {
  images: {
    // Izinkan gambar dari server OpenSID. Tambahkan domain produksi di sini,
    // mis. { protocol: "https", hostname: "simpang.desa.id" }
    remotePatterns: [
      { protocol: "http", hostname: "localhost", port: "8080" },
      { protocol: "http", hostname: "127.0.0.1", port: "8080" },
    ],
  },
  async redirects() {
    // Tempat memetakan URL lama OpenSID yang polanya berubah -> URL baru (301).
    // URL artikel lama (/artikel/thn/bln/hr/slug) sudah dipertahankan lewat routing,
    // jadi tidak perlu redirect. Lihat TDD §6.
    return [];
  },
};

export default nextConfig;
