import type { ReactNode } from "react";

/** Pembungkus seragam untuk semua widget beranda. */
export default function WidgetCard({
  judul,
  children,
  className = "",
}: {
  judul: string;
  children: ReactNode;
  className?: string;
}) {
  return (
    <section
      className={`flex flex-col overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-level1 ${className}`}
    >
      {/* Garis emas tipis = aksen "songket" dari design system */}
      <h2 className="border-b-2 border-secondary-container bg-surface-container px-4 py-3 font-heading text-sm font-semibold tracking-wide text-primary">
        {judul}
      </h2>
      <div className="flex-1 p-4">{children}</div>
    </section>
  );
}
