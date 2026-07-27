"use client";

import { useState } from "react";
import Icon from "@/components/ui/Icon";
import LightboxModal from "./LightboxModal";
import type { AlbumDetail } from "@/lib/types";

export default function GaleriDetailClient({ album }: { album: AlbumDetail }) {
  const [lightboxOpen, setLightboxOpen] = useState(false);
  const [selectedIndex, setSelectedIndex] = useState(0);

  const openLightbox = (index: number) => {
    setSelectedIndex(index);
    setLightboxOpen(true);
  };

  return (
    <>
      <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
        {album.foto.map((item, index) => (
          <div
            key={item.id}
            onClick={() => openLightbox(index)}
            className="group relative cursor-pointer overflow-hidden rounded-lg border border-outline-variant bg-surface-container-lowest shadow-sm transition-all duration-300 hover:shadow-md"
          >
            <div className="relative aspect-[4/3] w-full overflow-hidden">
              {/* eslint-disable-next-line @next/next/no-img-element */}
              <img
                src={item.gambar_url || "/placeholder-gallery.jpg"}
                alt={item.nama}
                className="h-full w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105"
              />

              {/* Overlay Video Play */}
              {item.jenis === "video" && (
                <div className="absolute inset-0 flex items-center justify-center bg-primary/20 transition-colors duration-300 group-hover:bg-primary/40">
                  <div className="flex h-14 w-14 items-center justify-center rounded-full bg-surface/90 text-primary shadow-lg transition-transform duration-300 group-hover:scale-110">
                    <Icon name="play_arrow" size={36} filled />
                  </div>
                </div>
              )}

              {/* Caption Overlay */}
              <div className="absolute inset-0 flex items-end bg-gradient-to-t from-primary/80 via-transparent to-transparent p-4 opacity-0 transition-opacity duration-300 group-hover:opacity-100">
                <p className="font-medium text-sm text-on-primary line-clamp-2">
                  {item.nama}
                </p>
              </div>
            </div>
          </div>
        ))}
      </div>

      <LightboxModal
        isOpen={lightboxOpen}
        photos={album.foto}
        initialIndex={selectedIndex}
        onClose={() => setLightboxOpen(false)}
      />
    </>
  );
}
