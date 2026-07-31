"use client";

import Link from "next/link";
import { useCallback, useEffect, useState } from "react";
import type { SliderItem } from "@/lib/types";

export default function Slider({ items }: { items: SliderItem[] }) {
  const slides = (items ?? []).filter((s) => s.gambar_url);
  const [aktif, setAktif] = useState(0);

  const maju = useCallback(() => {
    setAktif((i) => (i + 1) % slides.length);
  }, [slides.length]);

  useEffect(() => {
    if (slides.length < 2) return;
    const t = setInterval(maju, 6000);

    return () => clearInterval(t);
  }, [maju, slides.length]);

  if (!slides.length) return null;

  const s = slides[aktif];

  return (
    <section aria-label="Sorotan" className="relative overflow-hidden rounded-2xl bg-primary">
      <div className="relative aspect-[16/9] sm:aspect-[21/9]">
        {/* eslint-disable-next-line @next/next/no-img-element */}
        <img
          src={s.gambar_url as string}
          alt={s.judul ?? "Sorotan"}
          className="h-full w-full object-cover"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-primary/90 via-primary/30 to-transparent" />

        <div className="absolute inset-x-0 bottom-0 p-4 sm:p-6">
          {s.url ? (
            <Link href={s.url} className="group">
              <h2 className="max-w-3xl text-lg font-bold leading-snug text-white group-hover:underline sm:text-2xl">
                {s.judul}
              </h2>
            </Link>
          ) : (
            <h2 className="max-w-3xl text-lg font-bold text-white sm:text-2xl">{s.judul}</h2>
          )}
        </div>
      </div>

      {slides.length > 1 && (
        <div className="absolute right-4 top-4 flex gap-1.5">
          {slides.map((_, i) => (
            <button
              key={i}
              onClick={() => setAktif(i)}
              aria-label={`Tampilkan sorotan ${i + 1}`}
              aria-current={i === aktif}
              className={`h-2 cursor-pointer rounded-full transition-all ${
                i === aktif ? "w-6 bg-surface-container-lowest" : "w-2 bg-surface-container-lowest/50 hover:bg-surface-container-lowest/80"
              }`}
            />
          ))}
        </div>
      )}
    </section>
  );
}
