# DESIGN.md — Instruksi AI Agent untuk Generate UI/UX → Frontend Code
**Project:** INSYFEST 2026 — Tema: Pendidikan (Aplikasi Quiz seperti Quizizz)
**Role Tim:** UI/UX Designer & Frontend Engineer

---

## 0. WAJIB DIBACA SEBELUM MULAI (Tidak boleh dilewati)

Sebelum melakukan generate kode apapun dari screenshot/referensi UI yang diberikan pengguna, Agent **WAJIB**:

1. **Scan seluruh root folder project** (`ls -R` / baca struktur direktori) untuk memahami:
   - Framework & library yang sudah dipakai (cek `package.json`, `tsconfig.json`, `tailwind.config.*`)
   - Struktur folder yang sudah ada (`/components`, `/pages`, `/features`, `/hooks`, `/lib`, `/styles`, dll)
   - Konvensi penamaan file & komponen yang sudah dipakai tim (PascalCase? kebab-case?)
   - State management yang dipakai (Context API, Zustand, Redux, dll) — **jangan pasang library baru** kalau sudah ada solusi yang berjalan
   - Design token yang sudah ada (warna, spacing, font) di `tailwind.config.js` atau file CSS variabel
   - Komponen reusable yang sudah dibuat sebelumnya (Button, Input, Card, Modal, dll) — **gunakan ulang, jangan duplikat**
2. **Baca minimal 2–3 file komponen existing** untuk meniru gaya penulisan kode tim (indentasi, cara import, cara penulisan props/types, pola komentar).
3. Jika root folder kosong / project baru, baru boleh menentukan struktur folder awal (lihat Bagian 4).
4. **Laporkan hasil analisis singkat** sebelum generate kode (stack apa yang terdeteksi, komponen apa yang sudah ada, konvensi apa yang akan diikuti). Jangan langsung generate tanpa konfirmasi ini.

> Jika instruksi di bagian ini bertentangan dengan konvensi yang sudah ada di root folder, **konvensi existing project yang menang**, bukan default di file ini.

---

## 1. Konteks Project

- **Lomba:** INSYFEST 2026 (babak penyisihan)
- **Tema wajib:** Pendidikan
- **Produk:** Aplikasi kuis interaktif (referensi: Quizizz) — join kuis via kode/PIN, mengerjakan soal dengan timer, leaderboard, hasil skor.
- **Target user:** Guru (pembuat kuis) & Siswa (peserta kuis)

## 2. Kriteria Penilaian & Bobotnya (dari panitia)

| Kriteria | Bobot | Implikasi untuk Agent |
|---|---|---|
| Kualitas Kode & Struktur | 35% | Prioritas tertinggi. Kode harus modular, reusable, mudah dibaca manusia lain (juri kemungkinan cek source code) |
| Desain & UI/UX | 25% | UI harus konsisten, hierarki visual jelas, tidak generik/template basi |
| Inovasi & Orisinalitas | 20% | Hindari layout "copy-paste template AI" yang terlalu umum |
| Kesesuaian dengan Tema | 20% | Semua komponen (copy, ikon, ilustrasi, warna) harus terasa "edukasi", bukan generik e-commerce/dashboard SaaS |

**Kesimpulan untuk Agent:** 55% dari total nilai (35%+20%) ditentukan oleh kualitas & orisinalitas kode, bukan tampilan semata. Jangan korbankan struktur kode demi visual yang terlihat bagus tapi berantakan di balik layar.

---

## 3. Tugas Utama Agent

Agent akan menerima salah satu dari dua jenis input:
1. **Screenshot UI** (dari pengguna, referensi visual/inspirasi desain)
2. **Referensi halaman jadi** (link/file desain lain) untuk diadaptasi ke tema quiz pendidikan

Dari input tersebut, Agent harus:
1. Menganalisis layout, hierarki visual, spacing, tipografi, dan pola interaksi dari referensi.
2. **Tidak meniru mentah-mentah** — adaptasi ke konteks aplikasi kuis pendidikan (ganti konten, ikon, ilustrasi, copy agar sesuai tema).
3. Mengonversi hasil analisis menjadi kode frontend sesuai stack project (lihat Bagian 4).
4. Memastikan hasil kode lolos checklist Anti-AI-Slop (Bagian 6) sebelum dianggap selesai.

---

## 4. Tech Stack & Struktur Folder (default jika project baru)

> Default ini hanya dipakai **jika root folder masih kosong**. Jika sudah ada stack lain, ikuti yang sudah ada.

- Framework: React + TypeScript (Vite)
- Styling: TailwindCSS (utility-first, hindari inline style kecuali dynamic value)
- Struktur folder:
```
src/
 ├─ components/       # komponen reusable (Button, Input, Card, Modal, Timer, ProgressBar)
 ├─ features/         # per fitur: quiz-join, quiz-play, quiz-create, leaderboard
 ├─ layouts/          # layout wrapper (StudentLayout, TeacherLayout)
 ├─ pages/            # halaman/route
 ├─ hooks/            # custom hooks
 ├─ lib/ atau utils/  # helper function, konstanta, formatter
 ├─ types/            # type & interface TypeScript
 └─ styles/           # design token (jika tidak full pakai tailwind config)
```
- Ikon: satu library konsisten (misal `lucide-react`), jangan campur beberapa sumber ikon berbeda gaya.

---

## 5. Prinsip Desain yang Harus Diikuti

1. **Konsistensi token, bukan angka acak.** Semua warna, jarak, radius, font-size harus dari design token (Tailwind config / CSS variables), bukan nilai hardcode berulang seperti `padding: 13px`.
2. **Hierarki visual jelas**: judul, subjudul, body, caption punya skala yang konsisten di seluruh halaman.
3. **Nuansa "Pendidikan"**: gunakan palet warna hangat/ceria tapi tetap profesional (bukan warna korporat SaaS generik biru-abu-abu monoton), ilustrasi/ikon bertema belajar (buku, papan tulis, lencana pencapaian, dll).
4. **State harus lengkap**: setiap komponen interaktif (tombol jawab, timer, kartu soal) wajib punya state default, hover, active, disabled, loading, dan empty/error jika relevan — jangan hanya render 1 state statis.
5. **Responsive by default**: mobile-first, karena siswa kemungkinan besar akses dari HP.
6. **Motion secukupnya**: transisi halus (200–300ms) untuk feedback jawaban benar/salah, jangan animasi berlebihan yang mengganggu fokus mengerjakan soal.

---

## 6. Checklist Anti-"AI Slop" (WAJIB dicek sebelum output final)

Tolak hasil generate sendiri jika masih mengandung salah satu dari ini:

- [ ] Nama komponen/variabel generik seperti `Component1`, `Wrapper`, `Container2`, `handleClick2`
- [ ] Import library yang tidak dipakai, atau kode mati (dead code) yang dibiarkan
- [ ] Struktur JSX bersarang terlalu dalam (>4-5 level) tanpa dipecah jadi sub-komponen
- [ ] Komentar yang cuma menjelaskan hal yang sudah jelas dari kode itu sendiri (`// tombol submit` di atas `<button>Submit</button>`)
- [ ] Warna/spacing hardcode berulang di banyak file alih-alih pakai token
- [ ] Copy/teks placeholder generik ("Lorem ipsum", "Judul di sini", "Button") yang tidak diganti sesuai konteks kuis pendidikan
- [ ] Layout landing page/dashboard generik yang terasa template SaaS (hero besar + 3 kolom fitur + testimoni) tanpa penyesuaian ke use-case quiz
- [ ] Duplikasi komponen yang harusnya bisa reuse dari komponen yang sudah ada di project
- [ ] Tidak ada penanganan accessibility dasar (alt text, label form, kontras warna, focus state untuk keyboard navigation)
- [ ] Over-engineering: bikin abstraction/HOC/context rumit untuk kasus yang sebenarnya sederhana

---

## 7. Format Output yang Diharapkan

Setiap kali generate halaman/komponen baru, Agent harus menyertakan:
1. Ringkasan singkat: file apa saja yang dibuat/diubah dan kenapa.
2. Kode lengkap per file (bukan potongan setengah-setengah yang butuh disambung manual).
3. Catatan asumsi jika ada bagian dari referensi yang ambigu (misal: warna di screenshot tidak jelas kontrasnya, atau tidak ada state untuk kondisi tertentu).
4. Konfirmasi bahwa checklist Bagian 6 sudah dicek.

---

## 8. Catatan untuk Reviewer Manusia (Tim)

File ini mengarahkan *bagaimana* AI Agent bekerja, tapi tetap butuh review manual dari kamu sebagai UI/UX Designer & Frontend Engineer sebelum submit, terutama untuk:
- Menilai apakah "nuansa pendidikan" yang dihasilkan AI benar-benar terasa orisinal (bukan sekadar ganti warna dari template generik) — ini poin 20% Inovasi & Orisinalitas yang paling rawan disamakan otomatis.
- Mengecek ulang struktur kode secara manual, karena AI bisa saja lolos checklist di atas tapi tetap punya masalah desain arsitektur yang tidak tertangkap checklist sederhana ini.
