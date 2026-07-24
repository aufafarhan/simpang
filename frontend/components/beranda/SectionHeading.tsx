import type { ReactNode } from "react";
import Icon from "@/components/ui/Icon";

/** Judul section dengan garis emas "songket" di bawahnya. */
export default function SectionHeading({
  icon,
  children,
  aksi,
}: {
  icon: string;
  children: ReactNode;
  aksi?: ReactNode;
}) {
  return (
    <div className="songket-border mb-6 flex items-end justify-between gap-4 pb-2">
      <h2 className="flex items-center gap-2 font-heading text-2xl font-semibold text-primary">
        <Icon name={icon} />
        {children}
      </h2>
      {aksi}
    </div>
  );
}
