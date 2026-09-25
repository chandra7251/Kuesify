# Product Requirements Document - Platform Edukasi Interaktif

**Versi:** 2.3 Draft Sinkron Front-End (26 Sep 2026)
**Status:** Siap implementasi — sinkron dengan `FRONT-END.md` + `DESIGN.md` + `tailwind.config.js`
**Baseline:** `PRD_Platform_Edukasi_Interaktif.pdf` v2.0
**Perubahan dari 2.2:** Pemisahan halaman & dashboard **per-role** (tidak lagi generic), Front-End First Rule, dan governance sinkron PRD ↔ task.md

> **Aturan wajib sebelum membuat tampilan apa pun (FRONT-END.md §0 & DESIGN.md §0):** Baca dan pahami seluruh root project — `README.md`, `DESIGN.md`, `FRONT-END.md`, `task.md`, `SUBMISSION.md`, `package.json`, `composer.json`, `tailwind.config.js`, `routes/web.php`, `resources/js/Layouts/AuthenticatedLayout.vue`, serta 2–3 file komponen/page terdekat. Laporkan audit singkat (stack, struktur relevan, komponen reusable, risiko/konflik, file minimum yang akan disentuh) **sebelum menulis kode**. Konvensi yang sudah berjalan menang atas asumsi pribadi. Jangan menambah dependency/abstraksi baru jika solusi sudah ada.

---

## 1. Product Overview

Platform edukasi interaktif untuk sekolah, perguruan tinggi, corporate training, dan pembelajar umum. Creator membuat kuis manual atau mengubah PPT/PPTX/PDF menjadi draft soal melalui Gemini API. Peserta mengerjakan kuis dalam Live Multiplayer atau Self-Paced/Homework.

Nilai produk:

- Mengurangi waktu pembuatan asesmen melalui AI generation dengan human review.
- Meningkatkan engagement melalui leaderboard realtime, XP, level, badge, dan streak.
- Menyediakan analitik hasil belajar bagi creator dan organisasi.
- Menjaga pemisahan data antar organisasi.

Nama produk: **Kuesify**.

Scope implementasi kompetisi yang berlaku ada di `docs/MVP_SCOPE_COMPETITION.md`. Dokumen ini mempertahankan visi produk penuh; file scope kompetisi menjadi batas acceptance MVP.

---

## 2. Scope MVP

### Masuk MVP

- Register/login email-password, multi-role, dan guest join memakai PIN 6 digit.
- Multi-tenancy dengan `organization_id`, Global Scope, dan Policies.
- Manual Quiz Builder: multiple choice, true/false, fill-in-the-blank, uraian pendek.
- Metadata kuis, kategori, image optional, drag-and-drop urutan, Question Bank, import CSV/Excel, preview.
- AI generation dari PPT/PPTX/PDF maksimal **25 MB** dan **50 halaman**.
- Human-in-the-loop review untuk seluruh hasil Gemini sebelum publish.
- Live Quiz: lobby, PIN, QR, host control, timer sinkron, leaderboard, podium.
- Self-Paced/Homework: deadline, max attempts, status jawaban, pembahasan, gradebook.
- XP, level, daily streak, dan lima badge awal.
- Analitik, ekspor CSV/XLSX, moderasi konten publik, monitoring AI quota.

### Di luar MVP

- Matching question, LaTeX interaktif, OCR penuh dari slide gambar, transkripsi video.
- Battle Royale, 1v1 duel, SM-2 flashcards, power-up, avatar 3D.
- Native mobile app, SSO enterprise, LTI 1.3, AI proctoring.
- Adaptive learning path prediktif, marketplace, billing live, broadcast WhatsApp/email.
- Audio question, video embed, social sharing. Belum disetujui sebagai MVP.

---

## 3. Technology Stack

| Area | Pilihan | Status | Alasan |
|---|---|---|---|
| Bahasa | PHP 8.3+ | Baseline | Sesuai PRD awal. |
| Backend | Laravel 13 | Target | Project baru memakai versi yang dipilih tim. |
| Frontend | Vue 3 + TypeScript + Inertia 3 + Tailwind CSS | Diputuskan | UI realtime dan interaksi kompleks tanpa memecah Laravel menjadi API terpisah. |
| Database | MySQL 8.0 | Baseline | FK, transaksi, relasi, JSON. |
| Queue/cache | Redis + Horizon | Baseline | Queue AI, caching, monitoring job. |
| Realtime | Laravel Reverb + Laravel Echo | Baseline | Lobby, timer, transisi soal, leaderboard pada Vue. |
| AI | Google Gemini API | Baseline | Draft soal dari materi. |
| Dev environment | Sail/Docker | Opsional | Konsistensi environment tim, bukan fitur produk. |

### Keputusan Frontend

Frontend memakai **Vue 3 + TypeScript + Inertia 3 + Tailwind CSS**. Inertia menjaga Laravel sebagai modular monolith; aplikasi tidak perlu REST API terpisah untuk halaman web. Laravel Echo menerima event Reverb, sedangkan Vue menangani lobby, timer, drag-and-drop builder, leaderboard, feedback skor, dan dashboard interaktif.

Pinia tidak menjadi dependency wajib awal. Tambahkan hanya bila state lintas halaman atau lintas komponen tidak dapat ditangani oleh props, composables, dan Inertia page props.

**Sumber kebenaran desain (sinkron 26 Sep 2026):**

- Warna & shadow dari `tailwind.config.js` adalah kebenaran — bukan nilai lama di dokumen lain.
  - `brand.primary: #3154D5` — sidebar, top navbar, tombol aksi utama.
  - `brand.secondary: #90CB31` — highlight aktif, badge, indikator, focus state.
  - `brand.accent: #E6F1F5` — canvas/latar halaman.
  - `brand.dark: #233EA8`, `brand.hover: #2645B8`.
  - `shadow-figma: 0 4px 16px rgba(0,0,0,0.08)`, `shadow-figma-sm`, `shadow-figma-hover`.
  - Jangan menghidupkan kembali teal lama `teal-*` / `emerald-*` / `#0AB883` di halaman yang sudah dimigrasi (FRONT-END.md §4).
- Layout acuan: `resources/js/Layouts/AuthenticatedLayout.vue` (sidebar collapse, TopNavBar, mobile drawer).
- Ikon: satu set konsisten (saat ini Font Awesome 6 / SVG inline di AuthenticatedLayout) — jangan campur gaya acak.
- Workflow wajib per FRONT-END.md §5: pahami request → telusuri route/page/layout/props → gunakan ulang pola terdekat → perubahan minimum → pertahankan permission/route/responsive → mobile-first + a11y → verifikasi `prettier` + `eslint` + `vue-tsc --noEmit` + `npm run build` (+ `php artisan test` bila sentuh backend).
- Checklist Anti-AI-Slop DESIGN.md §6 wajib lolos sebelum dianggap selesai.

---

## 4. Roles dan Alasan Akses

### Peserta / Siswa / Umum

- Join sesi dengan PIN/QR dan alias guest.
- Mengerjakan Live Quiz atau Self-Paced/Homework.
- Melihat hasil sendiri, pembahasan, XP, badge, level, dan streak.

**Alasan:** Peserta menerima asesmen; tidak boleh mengubah materi, konfigurasi sesi, atau hasil peserta lain.

### Guru / Trainer / Creator

- Membuat, preview, publish, archive kuis miliknya.
- Menulis soal, gambar optional, poin, pembahasan, kategori, dan urutan.
- Memakai Question Bank dan import CSV/Excel.
- Upload materi, memilih jumlah/difficulty/tipe soal AI, lalu review draft AI.
- Membuka Live Session, mengelola lobby, mengunci lobby, mengeluarkan peserta, dan mengubah soal.
- Membuka gradebook, analitik kuis, dan ekspor hasil.

**Alasan:** Creator bertanggung jawab atas materi, kelancaran sesi, dan evaluasi hasil.

### Organization Admin

- Mengundang, mengubah, atau menonaktifkan member.
- Membuat kelas/jurusan atau departemen/divisi.
- Mengakses bank soal bersama, laporan organisasi, dan pengaturan tenant.

**Alasan:** Mengelola organisasi, bukan menjalankan tiap sesi kuis.

### Platform Super Admin

- Mengelola tenant dan kategori global.
- Moderasi kuis publik.
- Memantau metrik global, WebSocket, token Gemini, dan status ekstraksi.

**Alasan:** Menjaga keamanan, kualitas konten publik, biaya AI, serta operasi platform.

| Kapabilitas | Peserta | Creator | Org Admin | Super Admin |
|---|---:|---:|---:|---:|
| Join / jawab kuis | Ya | Ya | Ya | Ya |
| Buat / edit kuis | Tidak | Ya, miliknya | Tidak | Moderasi/support |
| Generate AI | Tidak | Ya | Tidak | Monitoring |
| Host live session | Tidak | Ya | Tidak | Support |
| Kelola anggota | Tidak | Tidak | Ya | Semua tenant |
| Moderasi konten | Tidak | Tidak | Tidak | Ya |

---

## 5. Functional Requirements

### FR-01 Authentication dan Tenant Isolation

- Register/login memakai email/password terenkripsi.
- Guest join memakai PIN 6 digit dan alias.
- Semua entitas tenant-scoped memakai `organization_id` dan Global Scope.
- Policies memeriksa ownership serta membership organisasi.

### FR-02 Organization dan Member Management

- Org Admin mengundang, mengubah, menonaktifkan creator dan peserta.
- Peserta dikelompokkan ke kelas/jurusan atau departemen/divisi.
- Role dalam organisasi disimpan terpisah dari role platform.

### FR-03 Manual Quiz Builder dan Question Bank

- Metadata kuis: title, slug, description, category, cover image, status, passing score, timer.
- Empat tipe soal: multiple choice, true/false, fill-in-the-blank, short answer/essay pendek.
- Soal menyimpan content, pilihan JSON, kunci JSON, pembahasan, poin, difficulty, image optional, dan order.
- Kreator mengatur urutan drag-and-drop serta preview peserta.
- Soal dapat disalin dari Question Bank organisasi.
- Bulk import menggunakan template CSV/Excel yang disepakati sebelum implementasi.
- Question Bank memakai tag terstruktur untuk search, filter, dan analytics.

### FR-04 AI Quiz Generation

- Upload PPT/PPTX/PDF maksimal 25 MB dan 50 halaman.
- Background job mengekstrak teks dan konsep penting.
- Creator memilih jumlah soal, difficulty, dan jenis soal.
- Prompt harus grounded pada dokumen dan mengembalikan JSON berisi soal, distractor, jawaban, pembahasan, serta referensi konsep.
- Hasil AI selalu draft; creator wajib review sebelum publish.
- Status job: pending, processing, completed, failed; retry/backoff saat timeout atau rate limit.

### FR-05 Live Session dan Lobby

- Creator memilih Live Multiplayer atau Self-Paced/Homework.
- Live session menghasilkan PIN 6 digit serta QR unik.
- Lobby realtime menampilkan peserta yang sudah masuk.
- Host dapat mulai sesi, mengunci lobby, mengeluarkan peserta, mengontrol transisi soal, dan memakai projector leaderboard.

### FR-06 Realtime Game Engine dan Scoring

- Reverb menyiarkan lobby, session started, question changed, answer accepted, dan leaderboard updated.
- Timer serta perubahan soal sinkron bagi peserta.
- Skor dihitung di server dengan baseline `base_points + (remaining_time × speed_multiplier)`.
- Question randomization dan answer shuffling dapat dikonfigurasi creator.
- Target awal: 100 peserta per sesi dan p95 broadcast latency di bawah 200 ms; wajib dibuktikan load test.

### FR-07 Self-Paced / Homework

- Creator menetapkan deadline dan max attempts.
- Peserta melihat status belum dijawab, dijawab, atau ragu-ragu sebelum submit.
- Tipe objektif dinilai otomatis di server.
- Essay pendek hanya tersedia pada mode Self-Paced/Homework di MVP.
- Essay pendek selalu dinilai manual oleh Creator; AI grading tidak masuk MVP.
- Attempt dengan essay berstatus `pending_review` setelah peserta submit. Nilai objektif dapat ditampilkan sebagai nilai sementara, lalu nilai final dan gradebook diperbarui setelah Creator memberi skor dan feedback.

### FR-08 Gamification

- XP dari partisipasi, akurasi, dan perfect completion.
- Progress level dan lima badge awal: First Quiz, Perfect Score, 5-Day Streak, serta dua badge lain yang ditetapkan tim.
- Daily streak memakai aturan timezone yang masih perlu diputuskan.

### FR-09 Analytics, Export, dan Moderation

- Dashboard pasca-kuis: rata-rata, nilai tertinggi/terendah, kelulusan, distribusi nilai.
- Analisis soal tersulit dan analisis akurasi peserta per topik/kategori.
- Creator dan Org Admin ekspor CSV/XLSX sesuai scope tenant.
- Super Admin moderasi kuis publik, tenant, kategori global, quota Gemini, latency AI, dan traffic Reverb.

### FR-10 Front-End Governance (baru 26 Sep 2026)

- Setiap pembuatan/perubahan tampilan wajib melewati audit FRONT-END.md §0 sebelum koding.
- Dashboard dan halaman elemen **wajib terpisah per role** (lihat §6). Tidak boleh menumpuk semua role dalam satu `Dashboard.vue` atau satu `Workspace.vue` dengan `v-if="role"` yang membuat jomplang.
- Semua halaman per-role memakai `AuthenticatedLayout.vue` + token `tailwind.config.js` yang sama — sidebar/nav konsisten, hanya konten yang berbeda per role.
- Verifikasi visual: cek 2–3 file existing terdekat (mis. `Dashboard.vue`, `Workspace.vue`, `QuizBuilder.vue`, `LiveHub.vue`) untuk meniru gaya import, props typing, spacing, radius `rounded-2xl`, shadow `shadow-figma`, dan a11y.
- Validasi wajib lolos: `npx prettier --write <file>` + `npx eslint <file>` + `npx vue-tsc --noEmit` + `npm run build`. Backend kritis: `php artisan test`.

---

## 6. Required Pages / UI — Terpisah Per Role (sinkron FRONT-END.md 26 Sep 2026)

> **Prinsip baru:** Satu role = satu direktori Pages + satu dashboard + navigasi yang relevan. Menghindari jomplang visual & permission bocor. Route lama tetap didukung selama masa migrasi, tetapi halaman baru adalah sumber kebenaran.

### Public / Guest (tanpa login)

- `/`, `/explore`, `/join`, `/login`, `/register`
- `/live/{sessionCode}` (guest play), `/quiz/{slug}/play`, `/quiz/{slug}/result` (jika ada)

### Peserta (participant)

| Route (doc) | Route aktual saat ini | Page tujuan (per-role) | Keterangan |
|---|---|---|---|
| `/dashboard` (participant) | `GET /dashboard` → `DashboardController` | `resources/js/Pages/participant/dashboard.vue` | XP, streak, level, badge, attempt saya, kuis tersedia |
| `/quiz/{slug}/play`, `/quiz/{slug}/result` | `GET /attempts/{attempt}/play` | `Pages/AttemptPlay.vue` | Player self-paced |
| `/attempts` (read-own) | `GET /attempts` | `Pages/Attempts.vue` (mode participant) | Riwayat sendiri |
| `/live/join` | `GET /join` | `Pages/LiveJoin.vue` | Join via PIN |
| `/live/{id}/play` | `GET /live-sessions/{session}/play` | `Pages/LivePlay.vue` | Live participant view |
| `/profile` | `GET /profile` | `Pages/Profile/Edit.vue` | Semua role |

### Creator (guru/trainer)

| Route (doc) | Route aktual | Page tujuan (per-role) | Keterangan |
|---|---|---|---|
| `/creator/dashboard` | `GET /dashboard` | `creator/dashboard.vue` | Ringkasan kuis/soal/live miliknya, aktivitas terbaru |
| `/creator/question-bank` | `GET /questions` | `creator/question-bank.vue` | Bank soal tenant-scoped |
| `/creator/quizzes` + builder | `GET /quizzes` | `Pages/QuizBuilder.vue` | CRUD kuis, reorder, publish |
| `/creator/quizzes/ai-generate` | `GET /materials` | `Pages/Materials.vue` | Upload + AI draft |
| `/creator/sessions/{id}/host` | `GET /live-sessions` + `/live-sessions/{id}/play` | `Pages/LiveHub.vue` / `Pages/LivePlay.vue` (host mode) | Host control |
| `/creator/quizzes/{id}/analytics` | `GET /reports` | `creator/reports.vue` | Analitik & gradebook |
| `/attempts` (gradebook) | `GET /attempts` | `Pages/Attempts.vue` (mode creator) | Nilai & ekspor CSV |

### Organization Admin

| Route (doc) | Route aktual | Page tujuan (per-role) | Keterangan |
|---|---|---|---|
| `/org/dashboard` | `GET /dashboard` | `admin/dashboard.vue` | Ringkasan tenant, member, grup, laporan |
| `/org/members` | `GET /organization` | `admin/members.vue` | Invite/disable member |
| `/org/groups` | `GET /organization` (+ groups) | `admin/groups.vue` | Kelas/jurusan |
| `/org/question-bank` | `GET /questions` | `admin/question-bank.vue` | Bank bersama (read) |
| `/org/reports` | `GET /reports` | `admin/reports.vue` | Laporan tenant |
| `/org/settings` | `GET /organization` | `admin/settings.vue` | Pengaturan tenant |

### Platform Super Admin

| Route (doc) | Route aktual | Page tujuan (per-role) | Keterangan |
|---|---|---|---|
| `/admin/dashboard` | `GET /dashboard` (super_admin) | `superadmin/dashboard.vue` | Metrik global, chart volume, distribusi role, top org |
| `/admin/tenants` | `GET /admin` → `WorkspaceController@admin` | `superadmin/tenants.vue` | CRUD tenant |
| `/admin/categories` | via `WorkspaceController@admin` | `superadmin/categories.vue` | Kategori global |
| `/admin/moderation` | — | `superadmin/moderation.vue` | Moderasi kuis publik (post-MVP queue) |
| `/admin/ai-monitoring` | `GET /admin` (health) | `superadmin/ai-monitoring.vue` | Quota Gemini, latency, failed jobs |
| `/admin/health` | `GET /admin` | `superadmin/platform.vue` | Reverb, jobs, audio |

> **Catatan migrasi:** `resources/js/Pages/Dashboard.vue` (umum) dan `resources/js/Pages/Workspace.vue` (multi-section via prop `section`) adalah **legacy**. Target refactor 2.3 adalah memecahnya menjadi direktori per-role di atas. `DashboardController` dan `WorkspaceController` akan melakukan branching per `organization.role` dan Inertia render ke page per-role yang sesuai, sambil mempertahankan route lama agar tidak breaking. Navigasi di `AuthenticatedLayout.vue` sudah memisahkan `navigationItems` per role — halaman baru tinggal mengikuti pola tersebut.

**Aturan desain untuk semua halaman baru:**

- Latar halaman `bg-brand-accent (#E6F1F5)`, kartu `bg-white rounded-2xl border border-slate-200 shadow-figma`, tombol utama `bg-brand-primary text-white`, aksen `bg-brand-secondary`.
- Ikon konsisten (SVG inline atau Font Awesome 6 — jangan campur gaya acak), radius, spacing, dan tipografi mengikuti file referensi terdekat.
- Mobile-first, thumb-zone, WCAG 2.1 AA dasar, keyboard navigation, empty/loading/error state.
- Tidak ada dekorasi berlebihan / gradient acak / shadow berat (Anti-AI-Slop).

---

## 7. Data Model Baseline

| Tabel | Fungsi |
|---|---|
| `users` | Akun, avatar, role platform, XP, level, streak, soft delete. |
| `organizations` | Tenant, branding, subscription metadata. |
| `organization_user` | Membership, unit group, role dalam organisasi. |
| `categories` | Kategori topik. |
| `quizzes` | Metadata, creator, category, metode, timer, status. |
| `questions` | Soal, media image optional, JSON options/answer, poin, difficulty. |
| `question_bank` | Snapshot soal reusable. |
| `tags` | Tag tenant-scoped untuk topik, tingkat kelas, atau label pencarian. |
| `question_tag` | Pivot many-to-many antara soal dan tag. |
| `quiz_sessions` | PIN, host, mode, lobby/active/completed, current question. |
| `session_participants` | User/guest, score, rank, completion state. |
| `responses` | Jawaban, waktu jawab, poin, audit trail. |
| `badges`, `user_badge` | Definisi serta riwayat badge. |
| `uploads` | File AI generation dan status ekstraksi. |

### Database Rules

- Global Scope bagi semua data tenant-scoped.
- JSON schema terstandar untuk options dan correct answer.
- Skor dihitung atomik di server.
- `responses` serta `session_participants` append-only setelah submit.
- Soft delete hanya untuk master: users, organizations, categories, quizzes, questions.
- `session_code` unik selama lobby atau active.
- Upload hanya PPT/PPTX/PDF maksimal 25 MB.

### Usulan Data Model - Belum Final

- `question_media`: hanya jika satu soal perlu lebih dari satu media. Untuk gambar tunggal, `questions.media_url` cukup.
- `user_preferences`: hanya bila language/theme/notification masuk scope.

---

## 8. Non-Functional Requirements

- CSRF, Form Request validation, output escaping, parameter binding, Policies, bcrypt/Argon2id.
- MIME whitelist dan size limit untuk upload. ClamAV merupakan keputusan deployment, bukan requirement wajib MVP lokal.
- Queue AI melalui Horizon dengan retry/backoff dan status gagal yang terlihat pengguna.
- Rate limit ditetapkan dari hasil load test dan quota Gemini; angka final belum disetujui.
- Mobile-first, thumb-zone, WCAG 2.1 AA dasar, keyboard navigation, dan error message jelas.
- **Kualitas Front-End:** Setiap halaman baru wajib lolos checklist DESIGN.md §6 (tidak ada nama generik, tidak ada import mati, nesting ≤4–5 level, tidak ada hardcode warna/spacing berulang, copy sesuai tema edukasi, tidak ada layout SaaS generik tanpa adaptasi, tidak ada duplikasi komponen, a11y dasar, tidak ada over-engineering).

---

## 9. Acceptance Criteria MVP

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
12. **(Baru 2.3)** Setiap role memiliki dashboard & halaman terpisah yang konsisten (tidak jomplang) — diverifikasi dengan audit `FRONT-END.md` + visual check per role, serta `npm run build` + `php artisan test` hijau.

---

## 10. Open Decisions Sebelum Implementasi

| Keputusan | Opsi | Dampak |
|---|---|---|
| Nama produk | Kuesify / QuizNusa / EduBlitz | Branding dan URL. |
| Audio/video soal | MVP atau post-MVP | Storage, player, moderation. |
| Antivirus upload | Tanpa scanner lokal atau ClamAV deployment | Infrastruktur. |
| Rate limits | Berdasarkan load test/quota | UX, biaya AI, abuse protection. |
| Timezone streak/deadline | User atau organisasi | Konsistensi aturan belajar. |
| Pemisahan file per-role | Direktori `participant/*` + `creator/*` + `admin/*` + `superadmin/*` vs single file + `v-if` | **Diputuskan 26 Sep: pisah per-role (opsi 1).** Single file dengan `v-if` menyebabkan jomplang & sulit maintain — ditolak. |

---

## 11. Change Log dari PDF v2.0

- Laravel 13 dimasukkan sebagai target teknologi yang akan divalidasi saat setup.
- Frontend diputuskan: Vue 3, TypeScript, Inertia 3, Tailwind CSS, Laravel Echo, dan Reverb.
- Essay dibatasi ke Self-Paced/Homework dengan manual grading dan status `pending_review`.
- Tag diputuskan memakai tabel `tags` dan pivot `question_tag` tenant-scoped.
- Role dan alasan akses diperjelas dengan matriks akses.
- Semua alur PDF penting dipertahankan: Required Pages, lobby, host control, speed scoring, AI grounded generation, deadline, gradebook, database rules, dan `quiz_sessions`.
- Audio/video, ClamAV, upload 50 MB, serta angka rate limit tidak dijadikan requirement final tanpa persetujuan.
- **26 Sep 2026 — v2.3:** Menambahkan **FR-10 Front-End Governance**, **§6 terpisah per-role** (Participant/Creator/OrgAdmin/SuperAdmin) dengan tabel route→page tujuan, aturan **Front-End First** (audit wajib `FRONT-END.md`/`DESIGN.md`/`tailwind.config.js`/`AuthenticatedLayout.vue`), dan penegasan **dashboard & elemen page terpisah per role** agar tidak jomplang. Sumber kebenaran warna diselaraskan ke `tailwind.config.js` (`#3154D5`/`#90CB31`/`#E6F1F5`).

---

## 12. Cara Cek yang Belum Selesai (Governance — wajib dibaca)

1. **Single source of truth:** `PRD_v2.2_Merged_Draft.md` (v2.3) adalah acuan. `task.md` adalah cermin eksekusi — keduanya harus sinkron setiap kali ada perubahan.
2. **Selalu cek task yang belum:** `grep -n "\[ \]\|\[-]" task.md` — setiap `[ ]` dan `[-]` adalah utang yang harus ditutup atau dijadwalkan.
3. **Setelah mengubah PRD, ubah task.md di commit yang sama.** Jangan biarkan PRD maju sementara task.md tertinggal (penyebab jomplang yang lalu).
4. **Sebelum koding UI, baca ulang `FRONT-END.md` §0–§5 dan `DESIGN.md` §0–§7.** Jika ragu, telusuri 2–3 file existing terdekat sebelum memutuskan pola.
