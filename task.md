# Kuesify MVP Task Tracker

Sumber: `PRD_v2.2_Merged_Draft.md` **v2.3 (26 Sep 2026)** — sinkron dengan `FRONT-END.md` + `DESIGN.md` + `tailwind.config.js`. Status diperiksa 26 September 2026.

> **Governance sinkron (PRD §12):** PRD adalah sumber kebenaran; `task.md` adalah cermin eksekusinya. Setiap perubahan PRD wajib di-cerminkan di `task.md` dalam commit yang sama. Sebelum menutup sprint/hari, jalankan `grep -n "\[ \]\|\[-]" task.md` — setiap `[ ]`/`[-]` adalah utang yang harus punya owner & ETA di `Urutan lanjut`. Sebelum koding UI apa pun, baca `FRONT-END.md` §0 + `DESIGN.md` §0 + `tailwind.config.js` + `AuthenticatedLayout.vue` + 2–3 file page terdekat (FR-10).

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
- Post-MVP: XLSX export, moderation publik, Super Admin lengkap, Horizon/TLS/monitoring, audit log penuh.
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
- [ ] Horizon production Linux. Tidak dapat dijalankan native Windows karena `pcntl` dan `posix`.
- [x] Feature test, lint, type-check, production build berjalan.
- [x] Playwright + Chrome local browser E2E: auth, creator publish, participant self-paced, guest live realtime, mobile 375 px, dan admin health lulus.

## 1. Identitas, organisasi, dan izin

- [x] Register, login, reset password, verifikasi email dari Breeze.
- [x] `organizations`, `organization_user`, role per organisasi.
- [x] Tenant context dan global scope untuk quiz, question, tag, live session, attempt.
- [x] Enum role: `participant`, `creator`, `organization_admin`, `super_admin`.
- [x] Middleware pemilih organisasi aktif dan reset tenant context tiap request.
- [x] Policy create/update/delete: owner, organization admin, dan super admin tenant.
- [x] Add/disable member dan group selesai. Class/departemen post-MVP.
- [x] Seed demo organization dan akun per role.

## 2. Taksonomi dan Question Bank

- [x] Kategori global.
- [x] Tag tenant-scoped dan pivot `question_tag`.
- [x] Question Bank model: MC, true/false, fill blank, essay melalui kolom `type`.
- [x] Test isolasi tag tenant dan reuse soal.
- [x] Create Question Bank HTTP dengan validation dasar dan tag transaction.
- [x] Read/update/delete, search, filter kategori/tag/tipe, pagination.
- [x] Import CSV/XLSX native dengan preview, mapping header, rollback transaksi, dan error row report.
- [x] Export CSV tenant-scoped. XLSX post-MVP.

## 3. Quiz Builder

- [x] Quiz tenant-scoped, creator, status draft/published.
- [x] Pivot `quiz_question` dan urutan posisi.
- [x] Publish guard: minimal satu soal.
- [x] Essay diblok untuk Live Quiz.
- [x] Metadata: description, cover image, visibility, category utama, deadline, max attempts, pembahasan.
- [x] CRUD/reorder/clone/archive backend dan UI QuizBuilder selesai.
- [x] Upload dan validasi image.
- [x] Builder UI dan publish flow tersedia dan sudah E2E.

## 4. AI materi ke draft soal

- [x] Upload PDF/PPT/PPTX 25 MB, signature check, pembatasan 50 halaman/slide, dan ekstraksi teks materi (PPTX slide XML & PDF text stream) ke `extracted_text`. Scan antivirus post-MVP.
- [x] Storage record dan extraction retry selesai. Virus scan/cleanup post-MVP.
- [x] Gemini client dengan structured schema prompt, normalisasi respon toleran (*self-healing*), 5/10/20 soal, difficulty, dan tipe soal.
- [x] Queue retry/backoff maksimal 3.
- [x] Kuota generation per organisasi/bulan dan per creator/minggu (10/minggu) selesai terintegrasi ke backend & UI. Audit token post-MVP.
- [x] Review, edit, reject, approval draft wajib sebelum Question Bank.
- [x] Failure Gemini tampil aman di UI dengan retry owner-only; response `429`, `503`, dan JSON invalid ditest.

## 5. Live Quiz

- [x] Live session tenant-scoped, host, PIN enam digit, guest alias/reconnect token.
- [x] Timer server dan formula skor `base points + remaining seconds × multiplier`.
- [x] Satu jawaban per peserta per soal di database.
- [x] Test skor tepat waktu dan duplicate answer.
- [x] PIN lookup/join dan reconnect token session selesai. QR reconnect tersedia di LivePlay.
- [x] Lobby, lock lobby, host start/next/end, kick participant, question transition.
- [x] Atomic submission: transaction, lock, unique violation handling.
- [x] Leaderboard cache tersedia. Token public channel aktif via Reverb.
- [x] Leaderboard/podium, timer, dan UI host/peserta khusus tersedia di LivePlay.
- [x] Feature test reconnect/late/kick tersedia dan browser realtime E2E lulus.

## 6. Self-Paced/Homework dan gradebook

- [x] Quiz attempt, jawaban objektif, status `pending_review` untuk essay.
- [x] Test score objektif dan essay pending review.
- [x] Deadline dan max attempts server-side. Timezone organisasi tersimpan dan dipakai di streak.
- [x] Manual essay grading, feedback, final score recalculate.
- [x] History/gradebook workspace tersedia dan export CSV aktif.

## 7. Gamification

- [x] XP ledger idempotent.
- [x] Level threshold.
- [x] Timezone organisasi dipakai untuk kalkulasi streak.
- [x] Lima badge awal dan award rules.
- [x] Dashboard XP/streak/level/badge tersedia.

## 8. Laporan dan platform admin

- [x] Creator analytics dan report halaman tersedia.
- [x] Export report CSV aktif. XLSX post-MVP.
- [ ] Public quiz moderation queue. Post-MVP.
- [-] Super admin tenant/category/health overview tersedia (via `WorkspaceController@admin` → `Workspace.vue` section `admin`). Moderation CRUD & pemisahan `Pages/Admin/*` post-MVP (PRD v2.3).
- [x] AI failure health & WebSocket (Reverb) live probe health aktif di Platform Admin (tests/Feature/ReverbHealthServiceTest.php, tests/E2E/admin-health.spec.ts).

## 9. Keamanan dan kualitas

- [x] Database FK/cascade pada core models; tenant query scope pada core models.
- [x] Form request validation dan throttle PIN/upload/AI/auth tersedia. Login lockout, register 5/menit, reset password throttle ditest.
- [x] Path storage tenant terisolasi, MIME signature, dan download authorization selesai. Scan antivirus post-MVP.
- [x] Role/policy dan cross-tenant HTTP guard selesai dan ditest (7 cross-tenant guard tests).
- [x] Policy coverage tersedia. Audit log post-MVP.
- [x] Workspace responsive dan semantic nav tersedia. Audit manual LivePlay 375 px: focus keyboard, pilihan jawaban, dan overflow lulus.
- [x] Browser E2E: auth, creator build/publish, guest live realtime, self-paced, mobile viewport, keyboard focus lulus.
- [-] Docker production app/worker/scheduler/Reverb tersedia. TLS/domain/monitoring post-MVP.

## 10. Refactor Front-End Per-Role — PRD v2.3 (26 Sep 2026) — BELUM DIKERJAKAN

> Hasil audit FRONT-END.md §0 (26 Sep): `Dashboard.vue` masih generik (single file untuk semua role) dan `Workspace.vue` masih multi-section via prop `section` (`questions`/`reports`/`admin`). `DashboardController` belum branching per-role, `WorkspaceController@admin` masih render `Workspace.vue`. Navigasi `AuthenticatedLayout.vue` sudah benar per-role, tetapi page-nya belum terpisah — inilah penyebab jomplang yang kamu keluhkan. Semua item di bawah wajib ikuti FR-10 (audit wajib sebelum koding + token `brand-*`).

- [ ] **FR-10 — Front-End First Rule ditegakkan:** Setiap pengerjaan UI baru wajib audit `FRONT-END.md` + `DESIGN.md` + `tailwind.config.js` + `AuthenticatedLayout.vue` + 2–3 file referensi terdekat, lalu lapor audit sebelum edit. PRD §3 & §6 dan `task.md` governance sudah dipertegas (PRD v2.3 §3, §6.0, §12).
- [ ] **Dashboard terpisah per-role (PRD §6.1–6.4):**
  - [ ] `participant/dashboard.vue` — XP, streak, level, badge, attempt saya, kuis tersedia (role `participant`)
  - [ ] `creator/dashboard.vue` — ringkasan kuis/soal/live miliknya, aktivitas terbaru (role `creator`)
  - [ ] `admin/dashboard.vue` — member count, grup, laporan tenant (role `organization_admin`)
  - [ ] `superadmin/dashboard.vue` — metrik global, 7-day trend Chart.js, distribusi role, top 5 org (role `super_admin`)
  - [ ] `DashboardController` branching per `organization.role` dan render Inertia page `participant/dashboard.vue` / `creator/dashboard.vue` / `admin/dashboard.vue` / `superadmin/dashboard.vue` (fallback `Dashboard.vue` legacy selama migrasi)
- [ ] **Workspace / Admin terpisah per-role (PRD §6.5):**
  - [ ] `creator/question-bank.vue` — Question Bank creator
  - [ ] `admin/members.vue` / `admin/groups.vue` / `admin/settings.vue` — Org Admin
  - [ ] `superadmin/platform.vue` (+ `superadmin/tenants.vue` / `superadmin/categories.vue` / `superadmin/ai-monitoring.vue` / `superadmin/moderation.vue`) — Super Admin hub, menggantikan `Workspace.vue` section `admin`
  - [ ] `WorkspaceController` + `PlatformAdminController` dipecah per-role dengan `abort_unless` yang tepat dan Inertia page `participant/*`/`creator/*`/`admin/*`/`superadmin/*`
- [ ] **Konsistensi visual:** Semua halaman baru pakai `AuthenticatedLayout`, `bg-brand-accent`, kartu `rounded-2xl border shadow-figma`, tombol `bg-brand-primary`, aksen `brand.secondary #90CB31`, ikon konsisten, mobile-first, a11y AA, empty/loading state. Validasi `prettier` + `eslint` + `vue-tsc --noEmit` + `npm run build` hijau.
- [ ] **Migrasi bertahap tanpa breaking:** Route lama (`/dashboard`, `/questions`, `/quizzes`, `/admin`, dll.) tetap hidup selama transisi; halaman baru menjadi sumber kebenaran. Legacy `Dashboard.vue` & `Workspace.vue` dihapus setelah semua role ter-migrasi dan `php artisan test` + Playwright hijau.

## 11. Cara Cek yang Belum Selesai — Selalu cek ini sebelum tutup hari

```bash
grep -n "\[ \]\|\[-]" task.md
grep -n "PRD v2.3" PRD_v2.2_Merged_Draft.md
```

- Setiap `[ ]` dan `[-]` di atas adalah utang. Jangan tutup sprint bila masih ada `[ ]` tanpa owner & ETA tertulis di `Urutan lanjut`.
- Setelah mengubah `PRD_v2.2_Merged_Draft.md`, ubah `task.md` di commit yang sama (PRD §12) — keduanya harus sinkron hari itu juga.
- Build & test harus hijau sebelum dianggap selesai: `npm run build` + `php artisan test` (+ Playwright untuk alur kritis).

## Urutan lanjut

1. Role enum, organization middleware, policy, member management. — **selesai**
2. HTTP CRUD Question Bank dan Quiz Builder, validation, mobile UI. — **selesai**
3. Self-Paced deadline/manual grading/gradebook. — **selesai**
4. Live HTTP flow, broadcasts, leaderboard, browser E2E. — **selesai**
5. Upload + AI queue/review/quota. — **selesai**
6. Gamification, reports, moderation, deployment, load test. — **selesai (moderation queue & XLSX post-MVP)**
7. **(Baru v2.3)** Refactor Front-End per-role: Dashboard `participant`/`creator`/`admin`/`superadmin` + Workspace/Admin per-role — **owner: kamu + aku, ETA: iterasi berikutnya, mulai dari `Dashboard/SuperAdmin.vue` & `Admin/Platform.vue`**. Wajib audit `FRONT-END.md` sebelum koding.
