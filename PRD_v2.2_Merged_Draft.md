# Product Requirements Document - Platform Edukasi Interaktif

**Versi:** 2.5 Competition Target (26 Sep 2026)
**Status:** Siap implementasi bertahap — sinkron dengan `FRONT-END.md`, `DESIGN.md`, `tailwind.config.js`, dan `task.md`
**Baseline:** `PRD_v2.2_Merged_Draft.md` v2.4
**Perubahan dari 2.4:** Menambahkan target kompetisi v2.5: WCAG 2.1 AA, TypeScript strict mode, test coverage dashboard, skeleton loading, empty states, toast notifications, keyboard navigation, study mode, quiz of the day, student progress dashboard, teacher insights, multilingual toggle, help hints, offline mode PWA, collaborative quiz, error boundary global.

> **Aturan wajib sebelum membuat tampilan apa pun (FRONT-END.md §0 & DESIGN.md §0):** Baca dan pahami `README.md`, `DESIGN.md`, `FRONT-END.md`, `task.md`, `SUBMISSION.md`, `package.json`, `composer.json`, `tailwind.config.js`, `routes/web.php`, `resources/js/Layouts/AuthenticatedLayout.vue`, serta 2–3 file komponen/page terdekat. Laporkan audit singkat sebelum menulis kode. Konvensi yang sudah berjalan menang atas asumsi pribadi. Jangan menambah dependency/abstraksi baru jika solusi sudah ada.

---

## 1. Product Overview

Kuesify adalah platform edukasi interaktif untuk sekolah, perguruan tinggi, corporate training, dan pembelajar umum. Creator membuat kuis manual atau mengubah PPT/PPTX/PDF menjadi draft soal melalui Gemini API. Peserta mengerjakan kuis dalam Live Multiplayer atau Self-Paced/Homework.

Nilai produk:

- Mengurangi waktu pembuatan asesmen melalui AI generation dengan human review.
- Meningkatkan engagement melalui leaderboard realtime, XP, level, badge, dan streak.
- Menyediakan analitik hasil belajar bagi creator dan organisasi.
- Menjaga pemisahan data antar organisasi.
- Mempermudah akses pendidikan melalui kuis, materi, mode belajar, dan dukungan offline dasar.

Nama produk: **Kuesify**.

Scope implementasi kompetisi yang berlaku ada di `docs/MVP_SCOPE_COMPETITION.md`. Dokumen ini mempertahankan visi produk penuh; file scope kompetisi menjadi batas acceptance MVP.

---

## 2. Scope MVP

### Masuk MVP

- Multi-tenant organization, active organization switch, role per organisasi.
- Role: participant, creator, organization_admin, super_admin.
- Manual quiz builder dengan question bank, kategori, tag, import CSV/Excel.
- Quiz mode: Live Multiplayer dan Self-Paced/Homework.
- Guest live join dengan PIN/QR.
- Server-side scoring dan audit response.
- AI document-to-quiz dari PPT/PPTX/PDF memakai Gemini API dengan human review.
- Gamification: XP, level, streak, dan badge awal.
- Dashboard dan workspace terpisah per role.
- Export dasar untuk creator/admin sesuai tenant scope.
- UI/UX polish v2.5 untuk kompetisi.

### Di luar MVP

- Payment/subscription.
- Native mobile app.
- Marketplace konten penuh.
- Audio question, video embed, social sharing.
- AI grading essay otomatis.
- ClamAV wajib lokal; antivirus upload adalah keputusan deployment.
- Horizon production Linux untuk lokal Windows.

---

## 3. Competition Focus — Kuesify v2.5

Tema lomba: **Website harus mempermudah akses pendidikan dan mendukung proses belajar secara inovatif**.

| Kriteria | Bobot | Fokus Implementasi |
|---|---:|---|
| Kualitas Kode & Struktur | 35% | TypeScript strict, komponen reusable, dashboard per-role, error boundary, test coverage |
| Desain & UI/UX | 25% | Skeleton loading, empty states, toast, keyboard navigation, WCAG 2.1 AA, responsive polish |
| Inovasi & Orisinalitas | 20% | Study mode, quiz of the day, collaborative quiz, student progress dashboard |
| Kesesuaian Tema Pendidikan | 20% | Help hints, teacher insights, multilingual toggle, offline mode PWA, feedback pembahasan |

### Prioritas v2.5 Yang Masuk Sprint

1. Dashboard per role: participant, creator, organization_admin, super_admin.
2. Komponen reusable: `StatCard`, `QuizCard`, `ActivityFeed`, `ProgressBar`, `StreakCalendar`, `BadgeGrid`.
3. Participant dashboard: XP, level, badge, attempt saya, kuis tersedia, quiz of the day, progress visual.
4. Creator dashboard: ringkasan kuis/soal/live, aktivitas terbaru, quiz analytics.
5. Org admin dashboard: member count, grup, laporan tenant, class performance.
6. Super admin dashboard: metrik global, 7-day trend, distribusi role, top organisasi.
7. Export dan governance konten: toggle CSV/XLSX, moderation queue public quiz, notification center.
8. UX polish: skeleton loading, empty states, toast notifications, keyboard shortcuts Live Quiz.
9. Code quality: TypeScript strict, dashboard branching tests, lint/build hijau, error boundary.

### Kandidat Inovasi Lanjutan

- **Study Mode:** Flashcard view setelah deadline untuk review jawaban.
- **Student Progress Dashboard:** Visualisasi progress belajar per kategori/subject.
- **Teacher Insights:** Retention, weak topics, average time per question.
- **Multilingual Toggle:** Quick switch Bahasa/English untuk istilah UI utama.
- **Help Hints System:** Tombol bantuan per soal dengan hint dari creator.
- **Collaborative Quiz:** Creator invite co-creator.
- **Offline Mode:** PWA service worker untuk cache quiz list dan akses dasar.

Status 27 Sep 2026: kandidat di atas sudah ditarik masuk sprint kompetisi dan tercermin di `task.md` sebagai `[x]`, kecuali Horizon production Linux yang tetap blocked lingkungan lokal Windows.

---

## 4. Technology Stack

| Area | Pilihan | Status | Alasan |
|---|---|---|---|
| Backend | Laravel 13.32, PHP 8.3 | Dipakai | Ekosistem MVC, queue, broadcast, auth |
| Frontend | Vue 3, TypeScript, Inertia, Vite | Dipakai | SPA-like UX tanpa API layer terpisah |
| Styling | Tailwind CSS | Dipakai | Cepat, konsisten, mobile-first |
| Database | MySQL 8 | Dipakai | Relasional dan umum untuk deployment lomba |
| Cache/Queue | Redis | Dipakai lokal | Queue AI, session/cache, realtime support |
| Realtime | Laravel Reverb + Echo | Dipakai | Live quiz, lobby, leaderboard |
| Testing | Pest, Playwright | Dipakai | Feature test dan E2E demo |
| Chart | Chart.js | Dipakai | Dashboard analytics |
| AI | Gemini API | Dipakai | Generate draft soal dari dokumen |

### Keputusan Frontend

- Inertia page props menjadi jalur data utama.
- Pinia tidak menjadi dependency wajib awal. Tambahkan hanya bila state lintas halaman tidak dapat ditangani props/composables.
- Sumber kebenaran desain adalah `tailwind.config.js`.
- Gunakan token brand:
  - `brand.primary: #3154D5`
  - `brand.secondary: #90CB31`
  - `brand.accent: #E6F1F5`
  - `brand.dark: #233EA8`
  - `brand.hover: #2645B8`
- Jangan menghidupkan kembali `teal-*`, `emerald-*`, atau `#0AB883` di halaman baru/migrasi.
- Ikon harus konsisten; jangan campur gaya acak.

---

## 5. Roles dan Alasan Akses

### Peserta / Siswa / Umum

- Join live quiz via PIN/QR.
- Mengerjakan self-paced quiz.
- Melihat hasil, pembahasan, XP, level, streak, badge.
- Mengakses materi publik organisasi sesuai tenant.
- Membutuhkan dashboard belajar yang fokus ke progress, rekomendasi, dan kuis tersedia.

### Guru / Trainer / Creator

- Membuat dan mengelola kuis.
- Mengelola question bank, tag, kategori, import.
- Mengupload materi dan membuat draft soal AI.
- Menjalankan live session.
- Melihat gradebook dan analytics pembelajaran.
- Tidak boleh mengelola tenant secara penuh kecuali diberi role admin.

### Organization Admin

- Mengelola anggota dan grup.
- Melihat laporan tenant.
- Mengelola kuis/soal tenant sesuai policy.
- Membutuhkan overview health organisasi, member, class/group performance.

### Platform Super Admin

- Mengelola organisasi, kategori global, monitoring AI, dan moderation queue.
- Melihat metrik platform global lintas tenant.
- Tidak menjadi peserta belajar biasa.

---

## 6. Functional Requirements

### FR-01 Authentication dan Tenant Isolation

- User dapat register, login, reset password, verifikasi email.
- User dapat memilih organisasi aktif.
- Semua query tenant-scoped wajib memakai active organization context.
- Tenant A tidak boleh membaca/menulis data tenant B.

### FR-02 Organization dan Member Management

- Organization admin dapat menambah dan disable member.
- Organization admin dapat membuat group.
- Organization admin dapat assign/remove member ke group sebagai class/departemen dasar untuk segmentasi belajar.

### FR-03 Manual Quiz Builder dan Question Bank

- Creator dapat membuat quiz draft, mengatur metadata, attach question, reorder, preview, publish, archive.
- Question bank mendukung multiple choice, true/false, fill-in-the-blank, essay.
- Essay hanya untuk Self-Paced/Homework.
- Import CSV/Excel wajib validasi dan rollback bila ada row invalid.
- Tag tenant-scoped dan kategori global.

### FR-04 AI Quiz Generation

- Creator dapat upload PPT/PPTX/PDF maksimal 25 MB dan 50 halaman/slide.
- Gemini menghasilkan draft soal.
- Hasil AI wajib review sebelum masuk question bank/quiz.
- Queue harus punya retry/backoff dan status gagal terlihat.
- Quota awal creator: 10 uploads/week.

### FR-05 Live Session dan Lobby

- Creator dapat membuat live session dari published quiz.
- Sistem menghasilkan PIN 6-digit dan QR code.
- Guest dapat join tanpa login.
- Lobby realtime menampilkan participant.
- Host dapat start, pause/lock, skip/next, end, kick, dan unlock answers.

### FR-06 Realtime Game Engine dan Scoring

- Timer disinkronkan dari server.
- Server menghitung skor dan menyimpan response audit.
- Leaderboard realtime dan podium final tersedia.
- Target awal: 100 peserta per sesi; p95 broadcast latency di bawah 200 ms harus dibuktikan load test.

### FR-07 Self-Paced / Homework

- Participant dapat mengerjakan quiz mandiri dengan deadline dan max attempts.
- Sistem menampilkan review jawaban, skor, status benar/salah, dan pembahasan.
- Creator dapat manual grading essay.
- Gradebook dapat export CSV.

### FR-08 Gamification

- XP bertambah setelah attempt selesai.
- Level dihitung dari threshold XP.
- Daily streak dihitung dari aktivitas belajar.
- Minimal lima badge awal tersedia.
- Participant dashboard harus memvisualkan XP, level, badge, dan streak.

### FR-09 Analytics, Export, dan Moderation

- Creator dapat melihat gradebook dan ringkasan performa quiz.
- Org admin dapat melihat laporan tenant.
- Super admin dapat melihat metrik global.
- Question bank, gradebook, dan reports mendukung export `format=csv|xlsx` tanpa dependency spreadsheet baru.
- Public quiz moderation queue masuk sprint kompetisi: super admin dapat melihat queue, approve, dan reject quiz public.
- Notification center menampilkan notifikasi user, unread count, dan mark-as-read untuk feedback alur belajar.

### FR-10 Front-End Governance

- Setiap pembuatan/perubahan tampilan wajib melewati audit `FRONT-END.md` §0 dan `DESIGN.md` §0.
- Dashboard dan halaman elemen wajib terpisah per role.
- Tidak boleh menumpuk semua role dalam satu `Dashboard.vue` atau `Workspace.vue` dengan `v-if` yang membuat jomplang.
- Halaman baru wajib mengikuti source of truth warna di `tailwind.config.js`.
- Validasi wajib: `npx prettier --write <file>`, `npx eslint <file>`, `npx vue-tsc --noEmit`, `npm run build`. Backend kritis: `php artisan test`.

---

## 7. Required Pages / UI — Terpisah Per Role

### Public / Guest

| Route | Page | Tujuan |
|---|---|---|
| `/` | `Welcome.vue` | Landing dan CTA login/register/join |
| `/join` | `LiveJoin.vue` | Join live quiz via PIN |
| `/live-sessions/{session}/play` | `LivePlay.vue` | Guest/player live quiz |

### Participant

| Route | Page | Tujuan |
|---|---|---|
| `/dashboard` | `participant/dashboard.vue` | XP, streak, level, badge, attempts, kuis tersedia, quiz of the day |
| `/attempts` | `Attempts.vue` atau `participant/attempts.vue` | Kuis self-paced dan attempt history |
| `/attempts/{attempt}` | `AttemptPlay.vue` | Player self-paced dan review |
| `/materials` | `Materials.vue` atau `participant/materials.vue` | Materi organisasi yang dapat dibaca siswa |

### Creator

| Route | Page | Tujuan |
|---|---|---|
| `/dashboard` | `creator/dashboard.vue` | Ringkasan kuis/soal/live dan student insights |
| `/quizzes` | `QuizBuilder.vue` atau `creator/quizzes.vue` | Quiz builder |
| `/questions` | `creator/question-bank.vue` | Question bank, tag, import/export |
| `/live-sessions` | `LiveHub.vue` atau `creator/live.vue` | Live session management |
| `/materials` | `Materials.vue` atau `creator/materials.vue` | Upload dokumen dan AI draft |
| `/reports` | `Workspace.vue` atau `creator/reports.vue` | Gradebook dan analytics |

### Organization Admin

| Route | Page | Tujuan |
|---|---|---|
| `/dashboard` | `admin/dashboard.vue` | Tenant overview, member, grup, laporan, class performance |
| `/organization` | `admin/members.vue` + `admin/groups.vue` | Member/group management |
| `/reports` | `admin/reports.vue` | Laporan tenant |
| `/settings` | `admin/settings.vue` | Tenant settings |

### Platform Super Admin

| Route | Page | Tujuan |
|---|---|---|
| `/dashboard` | `superadmin/dashboard.vue` | Global metrics, trend, role distribution, top org |
| `/admin` | `superadmin/platform.vue` | Platform health hub |
| `/superadmin/tenants` | `superadmin/tenants.vue` | Tenant management |
| `/superadmin/categories` | `superadmin/categories.vue` | Global category management |
| `/superadmin/ai-monitoring` | `superadmin/ai-monitoring.vue` | AI quota/failure monitoring |
| `/superadmin/moderation` | `superadmin/moderation.vue` | Moderasi kuis publik post-MVP |

---

## 8. Data Model Baseline

Model inti:

- `users`
- `organizations`
- `organization_user`
- `groups`
- `categories`
- `tags`
- `questions`
- `question_tag`
- `quizzes`
- `quiz_question`
- `quiz_attempts`
- `attempt_answers`
- `live_sessions`
- `live_participants`
- `live_answers`
- `materials`
- `ai_generations`
- `ai_question_drafts`
- `user_progress`
- `badges`
- `badge_awards`

### Database Rules

- Semua model tenant wajib punya `organization_id` kecuali data global seperti kategori platform.
- Query tenant wajib lewat tenant context/global scope atau policy eksplisit.
- Pivot role wajib menyimpan role per organisasi.
- Live guest data harus tetap bisa diaudit tanpa akun.
- AI draft tidak boleh langsung publish tanpa approval creator.

### Usulan Data Model — Belum Final

- `material_progress` untuk progress baca materi per siswa.
- `material_notes` untuk catatan pribadi siswa.
- `quiz_collaborators` untuk collaborative quiz.
- `question_hints` untuk help hints system.
- `user_locale_preferences` atau kolom locale di `users`.

---

## 9. Non-Functional Requirements

- **Performance:** Dashboard harus memakai aggregate query yang efisien; hindari N+1.
- **Realtime:** Target awal 100 peserta per sesi.
- **Security:** Tenant isolation, authorization policy, upload MIME whitelist, rate limit AI/upload/live join.
- **Accessibility:** WCAG 2.1 AA dasar, keyboard navigation, aria-label icon buttons, modal role, focus state jelas.
- **Reliability:** Queue AI punya retry/backoff; error state terlihat oleh user.
- **Code Quality:** TypeScript strict mode, component organization, tests untuk branching role, build hijau.
- **UI Quality:** Halaman baru wajib lolos checklist DESIGN.md §6.
- **Data Export:** Export harus tenant-scoped.

---

## 10. Acceptance Criteria MVP — Competition v2.5

1. User dapat register/login/profile; Org Admin dapat mengelola anggota dan kelompok.
2. Creator dapat membuat, preview, publish, serta archive kuis empat tipe soal dengan gambar optional.
3. Creator dapat memakai Question Bank dan import CSV/Excel memakai template valid.
4. Upload PPT/PPTX/PDF sesuai batas menghasilkan draft AI berbasis dokumen dan wajib direview.
5. Live Session menghasilkan PIN/QR, lobby realtime, host control, timer sinkron, leaderboard, dan podium.
6. Guest dapat join; server menghitung skor dan menyimpan response audit.
7. Self-Paced mendukung deadline, max attempts, review jawaban, hasil, pembahasan, dan gradebook.
8. XP, level, daily streak, dan lima badge awal berfungsi.
9. Creator/Org Admin dapat ekspor data sesuai tenant scope.
10. Tenant A tidak dapat membaca atau menulis data tenant B.
11. Load test membuktikan target realtime yang disetujui tim.
12. Setiap role memiliki dashboard & halaman terpisah yang konsisten.
13. WCAG 2.1 AA fixes, skeleton loading, empty states, toast, dan keyboard navigation tersedia pada UI baru.
14. DashboardController merender page sesuai role: participant, creator, organization_admin, super_admin.
15. Participant dashboard memvisualkan XP/Level/Badge dengan progress bar dan streak heatmap.
16. Creator dashboard menampilkan quiz analytics minimal: retention, weak topics, avg time per question.
17. Visual feedback pembahasan menyorot jawaban benar/salah dan penjelasan.
18. Theme templates berdasarkan kategori tersedia untuk variasi visual kotak kuis.
19. TypeScript strict dan build frontend hijau.
20. Test dashboard branching per role tersedia.

---

## 11. Open Decisions Sebelum Implementasi

| Keputusan | Opsi | Dampak |
|---|---|---|
| Nama produk | Kuesify / QuizNusa / EduBlitz | Branding dan URL |
| Audio/video soal | MVP atau post-MVP | Storage, player, moderation |
| Antivirus upload | Tanpa scanner lokal atau ClamAV deployment | Infrastruktur |
| Rate limits | Berdasarkan load test/quota | UX, biaya AI, abuse protection |
| Timezone streak/deadline | User atau organisasi | Konsistensi aturan belajar |
| Pemisahan file per-role | Direktori per role vs single file `v-if` | Diputuskan: direktori per role. Single file ditolak |
| Study mode | Masuk sprint atau post-MVP | Menambah value inovasi, butuh desain ringan |
| Offline mode | Cache quiz list saja atau attempt offline | Basic cache aman; offline attempt berisiko konflik data |

---

## 12. Change Log

- **15 Sep 2026 — MVP Baseline:** Menetapkan scope kompetisi awal: builder, self-paced, live realtime, E2E demo, mobile QA.
- **26 Sep 2026 — v2.3:** Menambahkan FR-10 Front-End Governance dan pemisahan halaman/dashboard per role.
- **26 Sep 2026 — v2.4:** Menambahkan dashboard per-role, komponen reusable, keyboard shortcuts, CSV/XLSX toggle, WCAG 2.1 AA, TypeScript strict, test coverage.
- **26 Sep 2026 — v2.5:** Menambahkan competition focus: study mode, student progress dashboard, teacher insights, multilingual toggle, help hints, quiz of the day, collaborative quiz, offline mode PWA, error boundary, skeleton, empty states, toast.
- **26 Sep 2026 — PRD Cleanup:** Merapikan header rusak dan mengembalikan section v2.4 yang sempat hilang: roles, FR, required pages, data model, non-functional requirements, governance.
- **27 Sep 2026 — PRD/Task Sync:** Menarik CSV/XLSX export, public moderation queue, notification center, group member assignment, study mode, multilingual toggle, help hints, collaborative quiz, dan offline mode ke scope kompetisi sesuai `task.md`; Horizon production Linux tetap blocked lokal Windows.

---

## 13. Governance — Cara Cek Yang Belum Selesai

1. `PRD_v2.2_Merged_Draft.md` adalah sumber kebenaran produk.
2. `task.md` adalah cermin eksekusi. Setiap perubahan PRD wajib dicerminkan di `task.md` dalam commit yang sama.
3. Cek utang kerja dengan:

```bash
grep -n "\[ \]\|\[-\]" task.md
```

4. Setiap `[ ]` dan `[-]` harus punya owner dan ETA di `Urutan lanjut`.
5. Sebelum koding UI, baca `FRONT-END.md`, `DESIGN.md`, `tailwind.config.js`, `AuthenticatedLayout.vue`, dan 2–3 page/komponen terdekat.
6. Sebelum menyatakan selesai, jalankan validasi relevan: `php artisan test`, `npx vue-tsc --noEmit`, `npm run build`, dan lint/format file terkait.
