/** Ikon Material Symbols Outlined (sesuai design system Stitch). */
export default function Icon({
  name,
  className = "",
  filled = false,
  size,
}: {
  name: string;
  className?: string;
  filled?: boolean;
  size?: number;
}) {
  return (
    <span
      aria-hidden="true"
      className={`material-symbols-outlined leading-none ${className}`}
      style={{
        fontSize: size ? `${size}px` : undefined,
        fontVariationSettings: filled ? "'FILL' 1" : undefined,
      }}
    >
      {name}
    </span>
  );
}
