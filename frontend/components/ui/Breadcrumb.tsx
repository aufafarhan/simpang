import Link from "next/link";
import Icon from "@/components/ui/Icon";

export interface JejakItem {
  label: string;
  href?: string;
}

export default function Breadcrumb({ items }: { items: JejakItem[] }) {
  return (
    <nav aria-label="Breadcrumb" className="mb-6">
      <ol className="flex flex-wrap items-center gap-2 text-xs font-medium text-primary">
        {items.map((it, i) => (
          <li key={i} className="flex items-center gap-2">
            {it.href ? (
              <Link href={it.href} className="hover:underline">
                {it.label}
              </Link>
            ) : (
              <span className="text-on-surface-variant">{it.label}</span>
            )}
            {i < items.length - 1 && <Icon name="chevron_right" size={14} />}
          </li>
        ))}
      </ol>
    </nav>
  );
}
