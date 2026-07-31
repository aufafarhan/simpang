"use client";

import { useEffect, useState, useCallback } from "react";
import Icon from "@/components/ui/Icon";
import type { FotoDetail } from "@/lib/types";

interface LightboxModalProps {
  isOpen: boolean;
  photos: FotoDetail[];
  initialIndex: number;
  onClose: () => void;
}

export default function LightboxModal({
  isOpen,
  photos,
  initialIndex,
  onClose,
}: LightboxModalProps) {
  const [currentIndex, setCurrentIndex] = useState(initialIndex);
  const [prevInitialIndex, setPrevInitialIndex] = useState(initialIndex);

  if (initialIndex !== prevInitialIndex) {
    setPrevInitialIndex(initialIndex);
    setCurrentIndex(initialIndex);
  }

  const handleNext = useCallback(() => {
    setCurrentIndex((prev) => (prev + 1) % photos.length);
  }, [photos.length]);

  const handlePrev = useCallback(() => {
    setCurrentIndex((prev) => (prev - 1 + photos.length) % photos.length);
  }, [photos.length]);

  useEffect(() => {
    if (!isOpen) return;

    const handleKeyDown = (e: KeyboardEvent) => {
      if (e.key === "Escape") {
        onClose();
      } else if (e.key === "ArrowRight") {
        handleNext();
      } else if (e.key === "ArrowLeft") {
        handlePrev();
      }
    };

    document.body.style.overflow = "hidden";
    window.addEventListener("keydown", handleKeyDown);

    return () => {
      document.body.style.overflow = "";
      window.removeEventListener("keydown", handleKeyDown);
    };
  }, [isOpen, onClose, handleNext, handlePrev]);

  if (!isOpen || !photos.length) return null;

  const currentItem = photos[currentIndex];

  return (
    <div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/90 p-4 backdrop-blur-md transition-opacity duration-300"
      role="dialog"
      aria-modal="true"
      aria-label="Galeri Layar Penuh"
    >
      {/* Close Button */}
      <button
        onClick={onClose}
        aria-label="Tutup modal"
        className="absolute right-4 top-4 z-10 rounded-full p-2.5 text-white/80 transition hover:bg-white/10 hover:text-white"
      >
        <Icon name="close" size={32} />
      </button>

      {/* Navigation - Prev */}
      {photos.length > 1 && (
        <button
          onClick={handlePrev}
          aria-label="Foto sebelumnya"
          className="absolute left-4 top-1/2 z-10 -translate-y-1/2 rounded-full p-3 text-white/80 transition hover:bg-white/10 hover:text-white"
        >
          <Icon name="chevron_left" size={40} />
        </button>
      )}

      {/* Navigation - Next */}
      {photos.length > 1 && (
        <button
          onClick={handleNext}
          aria-label="Foto selanjutnya"
          className="absolute right-4 top-1/2 z-10 -translate-y-1/2 rounded-full p-3 text-white/80 transition hover:bg-white/10 hover:text-white"
        >
          <Icon name="chevron_right" size={40} />
        </button>
      )}

      {/* Content */}
      <div className="relative flex max-h-[85vh] max-w-5xl flex-col items-center justify-center px-8">
        {currentItem.jenis === "video" ? (
          <div className="relative aspect-video w-full max-w-4xl overflow-hidden rounded-lg bg-black shadow-2xl">
            {currentItem.link ? (
              <iframe
                src={
                  currentItem.link.includes("youtube.com") || currentItem.link.includes("youtu.be")
                    ? currentItem.link.replace("watch?v=", "embed/")
                    : currentItem.link
                }
                title={currentItem.nama}
                className="h-full w-full border-0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowFullScreen
              />
            ) : (
              <div className="flex h-full w-full flex-col items-center justify-center gap-3 text-white/80">
                <Icon name="play_circle" size={64} filled className="animate-pulse text-secondary" />
                <p className="text-sm font-medium">{currentItem.nama} (Simulasi Pemutaran Video)</p>
              </div>
            )}
          </div>
        ) : (
          <div className="relative max-h-[75vh] overflow-hidden rounded-lg">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img
              src={currentItem.gambar_url || "/placeholder-gallery.jpg"}
              alt={currentItem.nama}
              className="max-h-[75vh] w-auto max-w-full rounded-lg object-contain shadow-2xl"
            />
          </div>
        )}

        {/* Caption & Counter */}
        <div className="mt-4 text-center text-white">
          <p className="font-heading text-lg font-semibold">{currentItem.nama}</p>
          {photos.length > 1 && (
            <p className="mt-1 text-xs text-white/60">
              {currentIndex + 1} dari {photos.length}
            </p>
          )}
        </div>
      </div>
    </div>
  );
}
