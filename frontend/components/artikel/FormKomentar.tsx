"use client";

import { useState, useEffect } from "react";
import Icon from "@/components/ui/Icon";
import { getCaptcha, postKomentar, type CaptchaData } from "@/lib/api";

/**
 * Form kirim komentar.
 */
export default function FormKomentar({ idArtikel }: { idArtikel: number }) {
  const [pesan, setPesan] = useState<{ text: string; isError: boolean } | null>(null);
  const [isLoading, setIsLoading] = useState(false);
  const [captcha, setCaptcha] = useState<CaptchaData | null>(null);

  const fetchCaptcha = async () => {
    const data = await getCaptcha();
    setCaptcha(data);
  };

  useEffect(() => {
    fetchCaptcha();
  }, []);

  const input =
    "w-full min-h-11 rounded-lg border border-outline-variant bg-surface p-3 text-sm text-on-surface focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary-container disabled:opacity-50";
  const label = "mb-1 block text-xs font-medium text-on-surface-variant";

  const handleSubmit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    if (!captcha) return;

    setIsLoading(true);
    setPesan(null);

    const form = new FormData(e.currentTarget);
    const data = {
      nama: form.get("nama") as string,
      email: form.get("email") as string,
      isi: form.get("isi") as string,
      captcha_jawaban: form.get("captcha_jawaban") as string,
      captcha_token: captcha.token,
    };

    const res = await postKomentar(idArtikel, data);
    
    if (res.success) {
      setPesan({ text: res.message, isError: false });
      (e.target as HTMLFormElement).reset();
      fetchCaptcha(); // Refresh captcha setelah berhasil
    } else {
      setPesan({ text: res.message, isError: true });
      fetchCaptcha(); // Refresh captcha jika gagal
    }
    
    setIsLoading(false);
  };

  return (
    <form className="space-y-4" onSubmit={handleSubmit}>
      <input type="hidden" name="id_artikel" value={idArtikel} />

      <div className="grid gap-4 md:grid-cols-2">
        <div>
          <label className={label} htmlFor="k-nama">
            Nama *
          </label>
          <input id="k-nama" name="nama" type="text" required disabled={isLoading} className={input} />
        </div>
        <div>
          <label className={label} htmlFor="k-email">
            Email *
          </label>
          <input id="k-email" name="email" type="email" required disabled={isLoading} className={input} />
        </div>
      </div>

      <div>
        <label className={label} htmlFor="k-isi">
          Komentar *
        </label>
        <textarea id="k-isi" name="isi" rows={4} required disabled={isLoading} className={input} />
      </div>

      {captcha && (
        <div>
          <label className={label} htmlFor="k-captcha">
            Jawab pertanyaan ini: {captcha.pertanyaan} *
          </label>
          <div className="flex gap-2 items-center">
            <input 
              id="k-captcha" 
              name="captcha_jawaban" 
              type="text" 
              required 
              disabled={isLoading}
              className={`${input} max-w-[150px]`} 
            />
            <button 
              type="button" 
              title="Ganti Pertanyaan"
              onClick={fetchCaptcha}
              disabled={isLoading}
              className="p-2 text-on-surface-variant hover:text-primary transition cursor-pointer disabled:cursor-not-allowed"
            >
              <Icon name="refresh" size={20} />
            </button>
          </div>
        </div>
      )}

      {pesan && (
        <p
          role="status"
          className={`flex items-start gap-2 rounded-lg border p-3 text-sm ${
            pesan.isError 
              ? "border-error bg-error-container text-on-error-container" 
              : "border-primary bg-primary-container text-on-primary-container"
          }`}
        >
          <Icon name={pesan.isError ? "warning" : "check_circle"} size={18} className="mt-0.5 shrink-0" />
          {pesan.text}
        </p>
      )}

      <button
        type="submit"
        disabled={isLoading || !captcha}
        className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-primary px-6 py-2 text-sm font-semibold text-on-primary transition hover:bg-primary-container hover:text-on-primary-container disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <Icon name="send" size={18} />
        {isLoading ? "Mengirim..." : "Kirim Komentar"}
      </button>

      <p className="text-xs text-outline">
        Komentar akan ditinjau admin nagari sebelum ditampilkan.
      </p>
    </form>
  );
}
