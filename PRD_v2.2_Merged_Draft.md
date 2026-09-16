# Product Requirements Document - Platform Edukasi Interaktif

**Versi:** 2.2 Draft Gabungan  
**Status:** Perlu review sebelum implementasi  
**Baseline:** `PRD_Platform_Edukasi_Interaktif.pdf` v2.0

Dokumen ini mempertahankan domain lengkap dari PDF dan memasukkan klarifikasi yang telah dibahas. Keputusan yang belum disetujui dipisahkan sebagai *Open Decisions*.

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

---

## 6. Required Pages / UI

### Public / Peserta

- `/`, `/explore`, `/join`, `/login`, `/register`
- `/live/{sessionCode}`, `/quiz/{slug}/play`, `/quiz/{slug}/result`
- `/dashboard/student`, `/profile`

### Creator

- `/creator/dashboard`, `/creator/quizzes`, `/creator/quizzes/create`
- `/creator/quizzes/{id}/builder`, `/creator/quizzes/ai-generate`, `/creator/quizzes/{id}/ai-review`
- `/creator/question-bank`, `/creator/sessions/{id}/host`, `/creator/quizzes/{id}/analytics`

### Organization Admin

- `/org/dashboard`, `/org/members`, `/org/groups`, `/org/question-bank`, `/org/reports`, `/org/settings`

### Platform Super Admin

- `/admin/login`, `/admin/dashboard`, `/admin/tenants`, `/admin/moderation`, `/admin/ai-monitoring`, `/admin/categories`

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

---

## 10. Open Decisions Sebelum Implementasi

| Keputusan | Opsi | Dampak |
|---|---|---|
| Nama produk | Kuesify / QuizNusa / EduBlitz | Branding dan URL. |
| Audio/video soal | MVP atau post-MVP | Storage, player, moderation. |
| Antivirus upload | Tanpa scanner lokal atau ClamAV deployment | Infrastruktur. |
| Rate limits | Berdasarkan load test/quota | UX, biaya AI, abuse protection. |
| Timezone streak/deadline | User atau organisasi | Konsistensi aturan belajar. |

---

## 11. Change Log dari PDF v2.0

- Laravel 13 dimasukkan sebagai target teknologi yang akan divalidasi saat setup.
- Frontend diputuskan: Vue 3, TypeScript, Inertia 3, Tailwind CSS, Laravel Echo, dan Reverb.
- Essay dibatasi ke Self-Paced/Homework dengan manual grading dan status `pending_review`.
- Tag diputuskan memakai tabel `tags` dan pivot `question_tag` tenant-scoped.
- Role dan alasan akses diperjelas dengan matriks akses.
- Semua alur PDF penting dipertahankan: Required Pages, lobby, host control, speed scoring, AI grounded generation, deadline, gradebook, database rules, dan `quiz_sessions`.
- Audio/video, ClamAV, upload 50 MB, serta angka rate limit tidak dijadikan requirement final tanpa persetujuan.
