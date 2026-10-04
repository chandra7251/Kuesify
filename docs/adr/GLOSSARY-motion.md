# Glossary: Kuesify Motion System

Dokumen ini mendefinisikan istilah teknis yang dipakai dalam implementasi animasi GSAP di Kuesify.
Dibuat mengikuti pola grill-with-docs: setiap term punya definisi, konteks penggunaan, dan referensi file.

---

## Terms

### `data-motion`
**Tipe:** HTML attribute (string)
**Nilai contoh:** `"participant-dashboard"`, `"quiz-catalog"`
**Fungsi:** Menandai elemen root sebuah halaman sebagai "motion surface". Dipakai sebagai selector di Playwright E2E test untuk verifikasi bahwa halaman sudah ada animasi.
**Dipakai di:** `participant/dashboard.vue`, `participant/quizzes.vue`

---

### `data-motion-item`
**Tipe:** HTML attribute (boolean/presence)
**Fungsi:** Menandai elemen yang menjadi target GSAP stagger. GSAP query `[data-motion-item]` dalam scope `gsap.context(fn, rootRef)`.
**Dipakai di:** Section-level elements di `participant/dashboard.vue`, `participant/quizzes.vue`

---

### `gsap.context(fn, root)`
**Tipe:** GSAP API
**Fungsi:** Scope semua GSAP selector queries ke subtree DOM `root`. Mencegah selector bocor ke komponen lain di SPA.
**Cleanup:** Panggil `ctx.revert()` di `onUnmounted`.

---

### `gsap.matchMedia()`
**Tipe:** GSAP API
**Fungsi:** Conditional animation berdasarkan CSS media query. Dipakai untuk `prefers-reduced-motion: no-preference` -- animasi hanya jalan jika user tidak set reduced-motion di OS.
**Pattern:**
```
const mm = gsap.matchMedia();
mm.add('(prefers-reduced-motion: no-preference)', () => { ... });
```

---

### `clearProps: 'all'`
**Tipe:** GSAP tween option
**Fungsi:** Hapus semua inline style yang di-inject GSAP setelah animasi selesai. Penting agar Tailwind utility class tidak di-override oleh sisa transform/opacity inline.

---

### `motionRoot` / `catalogRoot`
**Tipe:** Vue `ref<HTMLElement | null>`
**Fungsi:** Template ref yang menunjuk ke root elemen halaman. Di-pass ke `gsap.context()` sebagai scope.
**File:** `motionRoot` di `participant/dashboard.vue`, `catalogRoot` di `participant/quizzes.vue`

---

### Motion Surface
**Tipe:** Konsep arsitektur
**Definisi:** Sebuah halaman atau region yang memiliki `data-motion` attribute dan dianimasi oleh GSAP. E2E test memverifikasi existence surface ini sebelum assertion lebih lanjut.

---

### Reduced-motion Fallback
**Definisi:** Jalur eksekusi ketika user OS set `prefers-reduced-motion: reduce`. Dalam implementasi ini, seluruh GSAP block tidak dieksekusi -- elemen langsung visible tanpa animasi.
**Standar:** WCAG 2.1 SC 2.3.3 (Animation from Interactions).