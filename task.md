# Kuesify MVP Task Tracker

Sumber: `PRD_v2.2_Merged_Draft.md` **v2.5 (26 Sep 2026)** — sinkron dengan `FRONT-END.md` + `DESIGN.md` + `tailwind.config.js`. Status diperiksa 26 September 2026. Target kompetisi: peningkatan UI/UX, accessibility, code quality, dan inovasi edukatif.

> **Governance sinkron (PRD §13):** PRD adalah sumber kebenaran; `task.md` adalah cermin eksekusinya. Setiap perubahan PRD wajib di-cerminkan di `task.md` dalam commit yang sama. Sebelum menutup sprint/hari, jalankan `grep -n "\[ \]\|\[-]" task.md` — setiap `[ ]`/`[-]` adalah utang yang harus punya owner & ETA di `Urutan lanjut`. Sebelum koding UI apa pun, baca `FRONT-END.md` §0 + `DESIGN.md` §0 + `tailwind.config.js` + `AuthenticatedLayout.vue` + 2–3 file page terdekat (FR-10). Sebelum commit, pastikan PRD v2.5 §13 sync.

Legenda: `[x]` selesai dan ada validasi, `[-]` ada fondasi tetapi belum siap dipakai, `[ ]` belum dibuat.

## Standar Desain & Palet Warna (Sumber kebenaran: `tailwind.config.js`)

- **Brand Primary**: `#3154D5` (Sidebar background, top navbar background, tombol aksi utama "Buat Kuis") — `brand.primary`
- **Secondary**: `#90CB31` (Header "WORKSPACE" & "Selamat Datang Kembali", active menu pill di sidebar, tombol "Buka Live Quiz", banner "Level Up!", angka stat "Quiz" & "Attempt Saya", badge aksi "C" & "T") — `brand.secondary`
- **Accent**: `#E6F1F5` (Background kanvas dashboard utama, background badge ikon aksi cepat) — `brand.accent`
- **Brand Dark/Hover**: `#233EA8` / `#2645B8` — `brand.dark` / `brand.hover`
- **Support Colors (Opsional & Semantik)**:
  - `#D7A928` (Warm amber/emas untuk label & angka stat "Soal Aktif", status pending/draft) — `support.1`
  - `#7C869C` (Muted slate/abu untuk teks bantuan, subteks aktivitas, dan border card halus) — `support.2`
  - `#4B392E` (Deep charcoal/gelap untuk label & angka stat "Live Aktif", serta teks kontras tinggi) — `support.3`
- **Shadow Standards (Figma Drop Shadow — `tailwind.config.js`)**:
  - `shadow-figma`: `0 4px 16px rgba(0, 0, 0, 0.08)` untuk kartu utama (Hero, 4 Stat Cards, Kuis Terbaru, Streak Card)
  - `shadow-figma-sm`: `0 2px 8px rgba(0, 0, 0, 0.06)` untuk kartu interaktif kecil (Aksi Cepat C & T)
  - `shadow-figma-hover`: `0 6px 20px rgba(0, 0, 0, 0.12)` untuk efek hover mengambang halus
- **Aturan migrasi:** Jangan menghidupkan kembali `teal-*`/`emerald-*`/`#0AB883`/`#2DD4BF` di halaman yang sudah dimigrasikan (FRONT-END.md §4). Selalu pakai token `brand-*`.

## MVP Kompetisi — Baseline 15 September 2026

Sumber acceptance: `docs/MVP_SCOPE_COMPETITION.md`.

- P0: Creator Builder, Self-Paced result/gradebook, Live realtime, tiga E2E demo, mobile QA.
- P1 bonus: Gemini AI, gamification, laporan CSV, QR join.
- Post-MVP baseline lama: XLSX export, moderation publik, Super Admin lengkap, Horizon/TLS/monitoring, audit log penuh. Status 27 Sep 2026: XLSX export, moderation publik, dan Super Admin hub sudah ditarik masuk scope kompetisi; Horizon/TLS/monitoring dan audit log penuh tetap deployment/post-MVP.
- Jangan menganggap seluruh item di bawah wajib untuk demo kompetisi; tracker ini juga memuat P1 dan post-MVP.

### P0 Execution

- [x] Creator Builder UI: create, attach, reorder tombol, preview urutan, publish.
- [x] Self-Paced player, result, gradebook UI dan export CSV.
- [x] Live Hub, PIN join, player, host control, Reverb event stream, podium.
- [x] E2E: creator publish, participant self-paced, guest live realtime, role guard.
- [x] Mobile QA 375 px: PIN join dan LivePlay realtime, pilihan jawaban, keyboard focus, serta tanpa horizontal overflow.

## 0. Fondasi proyek  ✅ done 16 Sep 2026

- [x] Laravel 13.32, PHP 8.3, Vue 3, TypeScript, Inertia, Tailwind, Pest.
- [x] Email Resend: transport `symfony/resend-mailer`, konfigurasi `resend`, dan template environment tersedia. API key/domain pengirim diisi saat deploy.
- [x] MySQL 8 dan Redis dipakai konfigurasi lokal.
- [x] Reverb backend, Laravel Echo client, dan channel token publik terpasang untuk MVP.
- [x] Reverb server lulus smoke test Windows dan service Docker tersedia. TLS/domain/monitoring post-MVP (Docker ready).
- [-] Horizon production Linux — blocker lingkungan: wajib Linux/WSL/CI karena `pcntl` dan `posix` tidak tersedia native Windows. `composer require laravel/horizon --dry-run` gagal di Windows karena `ext-pcntl` dan `ext-posix`; WSL Ubuntu ada tetapi PHP CLI belum terpasang dan `sudo` meminta password.
- [x] Feature test, lint, type-check, production build berjalan.
- [x] Playwright + Chrome local browser E2E: auth, creator publish, participant self-paced, guest live realtime, mobile 375 px, dan admin health lulus.

## 1. Identitas, organisasi, dan izin

- [x] Register, login, reset password, verifikasi email dari Breeze.
- [x] `organizations`, `organization_user`, role per organisasi.
- [x] Tenant context dan global scope untuk quiz, question, tag, live session, attempt.
- [x] Enum role: `participant`, `creator`, `organization_admin`, `super_admin`.
- [x] Middleware pemilih organisasi aktif dan reset tenant context tiap request.
- [x] Policy create/update/delete: owner, organization admin, dan super admin tenant.
- [x] Add/disable member, group, dan assign/remove member ke group selesai sebagai class/departemen dasar.
- [x] Seed demo organization dan akun per role.

## 2. Taksonomi dan Question Bank

- [x] Kategori global.
- [x] Tag tenant-scoped dan pivot `question_tag`.
- [x] Question Bank model: MC, true/false, fill blank, essay melalui kolom `type`.
- [x] Test infrastructure: feature tests, unit tests, E2E Playwright.
- [x] CSV/Excel import dengan validasi dan error reporting.
- [x] Question preview: creator preview soal sebelum add ke quiz.

## 3. Quiz Builder & Player

- [x] Manual Quiz Builder: drag-and-drop reorder, preview urutan, publish/archive.
- [x] Quiz types: MC, true/false, fill-in-the-blank, essay (manual grading).
- [x] Quiz metadata: title, description, category, image optional, duration, deadline.
- [x] Quiz Builder UI: tombol attach, reorder, preview, publish, archive.
- [x] Quiz Player: multiple choice, drag-drop reorder, timer display, submission.
- [x] Quiz Preview Mode: Creator preview quiz sebelum publish — **COMPETITION ADD**

## 4. Live Quiz

- [x] Live Session: lobby, PIN 6-digit, QR code, host control.
- [x] Broadcast: lobby status, timer, transisi soal, leaderboard.
- [x] Live player: pilihan jawaban, timer sinkron, submit.
- [x] Leaderboard: real-time, podium final.
- [x] Live Host Control: start, pause, skip, end, unlock answers.
- [x] Retry Live Join UI: Participant disconnect → "Retry" button dengan auto-reconnect.

## 5. Self-Paced/Homework

- [x] Self-Paced player: pengerjaan mandiri, deadline, max attempts.
- [x] Feedback: pembahasan, hasil skor, status jawaban.
- [x] Gradebook: manual grading essay, export CSV.
- [x] Attempt history: list attempts, view detail per attempt.

## 6. AI Generation & Materials

- [x] Upload PPT/PPTX/PDF maksimal 25 MB dan 50 halaman.
- [x] Gemini AI generate draft soal dengan human-in-the-loop review.
- [x] AI generation queue dengan retry/backoff dan status gagal terlihat.
- [x] Weekly quota creator: 10 uploads/week.
- [x] AI Review Diff View: highlight perubahan prompt vs generated — **COMPETITION ADD**

## 7. Gamification

- [x] XP system dengan level threshold.
- [x] Daily streak counter dengan 30-day heatmap.
- [x] Badge system dengan lima badge awal.
- [x] "Quiz of the Day" quiz curated untuk daily engagement — **COMPETITION ADD**

## 8. Dashboard & Workspace Per-Role — PRD v2.5 (26 Sep 2026)

> **FR-10 Front-End First Rule:** Setiap pengerjaan UI baru wajib audit `FRONT-END.md` + `DESIGN.md` + `tailwind.config.js` + `AuthenticatedLayout.vue` + 2–3 file referensi terdekat, lalu lapor audit sebelum edit.

### 8.1 Dashboard Per-Role (PRD §7 + §10)

- [x] `participant/dashboard.vue` — XP, streak, level, badge, attempt saya, kuis tersedia, quiz of the day (role `participant`) — **COMPETITION ADD**
- [x] `creator/dashboard.vue` — ringkasan kuis/soal/live miliknya, aktivitas terbaru, student insights (role `creator`) — **COMPETITION ADD**
- [x] `admin/dashboard.vue` — member count, grup, laporan tenant, completion count, dan average class score (role `organization_admin`) — **COMPETITION ADD**
- [x] `superadmin/dashboard.vue` — metrik global, 7-day trend Chart.js, distribusi role, top 5 org (role `super_admin`)
- [x] `DashboardController` branching per `organization.role` dan render Inertia page `participant/dashboard.vue` / `creator/dashboard.vue` / `admin/dashboard.vue` / `superadmin/dashboard.vue` — **COMPETITION ADD**

### 8.2 Workspace / Admin Terpisah Per-Role (PRD §7)

- [x] `creator/question-bank.vue` — Question Bank creator dengan form soal dan tag autocomplete
- [x] `admin/members.vue` / `admin/groups.vue` / `admin/settings.vue` — Org Admin
- [x] `superadmin/tenants.vue` / `superadmin/categories.vue` / `superadmin/ai-monitoring.vue` / `superadmin/moderation.vue` — Super Admin hub

### 8.3 Komponen Reusable

- [x] `StatCard.vue` — Generic stat card dengan props `label`, `value`, `accent`, `helper`
- [x] `QuizCard.vue` — Quiz summary card dengan props `title`, `questionsCount`, `status`, `description` dan slot `actions`
- [x] `ActivityFeed.vue` — Recent activity feed dengan props `items[]`
- [x] `ProgressBar.vue` — Horizontal progress bar dengan props `current`, `max`, `label`
- [x] `StreakCalendar.vue` — 30-day streak heatmap dengan props `streaks[]`
- [x] `BadgeGrid.vue` — Badge display grid dengan props `badges[]`

### 8.4 UI/UX Enhancements — Competition Focus

- [x] Skeleton loading: `QuizListSkeleton.vue` tampil saat filter Quiz Builder memuat ulang daftar — **COMPETITION ADD**
- [x] Empty states: blank state tersedia untuk quiz, question bank, attempts, dan workspace — **COMPETITION ADD**
- [x] Toast notifications: `Toast.vue` terpasang global dari flash session untuk aksi backend — **COMPETITION ADD**
- [x] Keyboard shortcuts (Live Quiz): Arrow Up/Down pilih answer, Enter submit build hijau
- [x] Quiz Preview Mode: Creator preview quiz sebelum publish
- [x] Export CSV/XLSX Toggle: User pilih format export CSV/XLSX di question bank, gradebook, dan reports; backend `format=csv|xlsx` hijau
- [x] Retry Live Join UI: Participant disconnect → "Retry" button dengan auto-reconnect
- [x] Question Tag Autocomplete: creator Question Bank memakai endpoint `questions.tags` tenant-scoped
- [x] WCAG 2.1 AA Fixes: Modal memakai `inert`, Dropdown memakai `aria-controls`, keyboard/focus behavior teruji lewat build — **COMPETITION ADD**
- [x] Mobile UX law pass: bottom nav tidak overlap konten, active nav memakai `aria-current` + indikator non-warna, tap target 44 px, focus ring global, reduced motion, dan tanpa horizontal overflow; validasi `npm run build` + `tests/E2E/mobile.spec.ts` hijau — **COMPETITION ADD**

### 8.5 Code Quality — Competition Focus

- [x] `tsconfig.json` strict mode: `strict`, `noImplicitAny`, dan `exactOptionalPropertyTypes` aktif; build hijau — **COMPETITION ADD**
- [x] Test Coverage Dashboard: Unit test `DashboardController` branching per-role — **COMPETITION ADD**
- [x] Lint/build/test: `npm run build` hijau dan dashboard feature test hijau setelah reusable component refactor
- [x] Error boundary global untuk graceful error handling: `GlobalErrorBoundary.vue` wrap Inertia root build hijau — **COMPETITION ADD**

## 9. Competition Focus Items — PRD v2.5 (26 Sep 2026)

- [x] Visual XP/Level/Badge (participant dashboard) — progress bar dan heatmap streak (visualisasi progress belajar) — **High impact Innovation** — **COMPETITION ADD**
- [x] Quiz Analytics (creator dashboard) — student retention, weak topics, dan avg time per question berbasis timestamp jawaban — **High impact Innovation** — **COMPETITION ADD**
- [x] Visual Feedback Pembahasan (participant): hasil menandai benar/salah dan pembahasan di AttemptPlay build hijau — **Medium impact Theme** — **COMPETITION ADD**
- [x] Theme Templates (categories): Quiz Builder memakai `categories.theme_key` untuk accent visual per tema — **Medium impact Theme** — **COMPETITION ADD**

## 10. Post-MVP

- [x] XLSX export: question bank, gradebook, dan reports mendukung `.xlsx` tanpa dependency baru
- [x] Public quiz moderation queue: halaman Super Admin menampilkan queue dan aksi approve/reject
- [x] Class/departemen management: halaman organisasi dapat membuat group dan assign member ke group
- [x] Dark Mode toggle tersimpan di localStorage, mengikuti preferensi sistem saat belum ada setting, dan punya token kontras untuk layout/card/bottom nav
- [x] Notification System (email + toast): pusat notifikasi Inertia menampilkan list unread dan mendukung mark-read
- [x] Advanced Search & Filter: Quiz Builder memakai filter search/status/category dari backend
- [-] Horizon production Linux — blocker lingkungan Windows; jalankan di Linux/WSL/CI dengan PHP CLI + `pcntl` + `posix`.
- [x] Study Mode (Flashcard view setelah deadline): route mendukung JSON dan halaman Inertia flashcard — **COMPETITION ADD**
- [x] Student Progress Dashboard (visualisasi progress per subject): participant dashboard progress/xp/streak/badge sudah hijau — **COMPETITION ADD**
- [x] Teacher Insights Dashboard (retention, weak topics, avg time): creator analytics retention/weak topics/avg time sudah hijau — **COMPETITION ADD**
- [x] Multilingual Toggle (Bahasa/English): toggle Profil menyimpan `users.locale` melalui `profile.locale` — **COMPETITION ADD**
- [x] Help Hints System (hint button per soal): backend `questions.hint` + tombol hint di AttemptPlay build hijau — **COMPETITION ADD**
- [x] Offline Mode (PWA service worker): `public/sw.js` + production registration build hijau — **COMPETITION ADD**
- [x] Collaborative Quiz (co-creator): Quiz Builder dapat menambah dan menghapus co-creator — **COMPETITION ADD**

## 11. Cara Cek yang Belum Selesai — Selalu cek ini sebelum tutup hari

```bash
grep -n "\[ \]\|\[-]" task.md
grep -n "PRD v2.5" PRD_v2.2_Merged_Draft.md
```

- Setiap `[ ]` dan `[-]` di atas adalah utang. Jangan tutup sprint bila masih ada `[ ]` tanpa owner & ETA tertulis di `Urutan lanjut`.
- Setelah mengubah `PRD_v2.2_Merged_Draft.md`, ubah `task.md` di commit yang sama (PRD §13) — keduanya harus sinkron hari itu juga.
- Build & test harus hijau sebelum dianggap selesai: `npm run build` + `php artisan test` (+ Playwright untuk alur kritis).

## Urutan lanjut

1. Role enum, organization middleware, policy, member management. — **selesai**
2. HTTP CRUD Question Bank dan Quiz Builder, validation, mobile UI. — **selesai**
3. Self-Paced deadline/manual grading/gradebook. — **selesai**
4. Live HTTP flow, broadcasts, leaderboard, browser E2E. — **selesai**
5. Upload + AI queue/review/quota. — **selesai**
6. Gamification, reports, moderation, deployment, load test. — **selesai untuk scope lokal; Horizon production Linux blocked lingkungan Windows**
7. **(Baru v2.3)** Refactor Front-End per-role: Dashboard `participant`/`creator`/`admin`/`superadmin` + Workspace/Admin per-role — **selesai**.
8. **(Baru v2.4)** PRD v2.4 improvements: Dashboard per-role, komponen reusable, keyboard shortcuts, CSV/XLSX toggle, WCAG 2.1 AA, tsconfig strict, test coverage — **selesai**.
9. **(Baru v2.5)** Competition Focus: WCAG 2.1 AA, TypeScript strict, test coverage, skeleton loading, empty states, toast, study mode, student progress, multilingual toggle, help hints, collaborative quiz, offline mode — **selesai; Horizon tetap owner deployment Linux/CI**.
