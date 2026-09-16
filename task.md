# Kuesify MVP Task Tracker

Sumber: `PRD_v2.2_Merged_Draft.md`. Status diperiksa 15 September 2026.

Legenda: `[x]` selesai dan ada validasi, `[-]` ada fondasi tetapi belum siap dipakai, `[ ]` belum dibuat.

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

- [x] Upload PDF/PPT/PPTX 25 MB, signature check, dan extractor membatasi 50 halaman/slide. Scan antivirus post-MVP.
- [x] Storage record dan extraction retry selesai. Virus scan/cleanup post-MVP.
- [x] Gemini client, schema JSON, 5/10/20 soal, difficulty, dan tipe soal.
- [x] Queue retry/backoff maksimal 3.
- [x] Kuota generation per organisasi/bulan selesai. Audit token post-MVP.
- [x] Review, edit, reject, approval draft wajib sebelum Question Bank.
- [x] Failure Gemini tampil aman di UI dengan retry owner-only; response `429` dan JSON invalid ditest.

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
- [-] Super admin tenant/category/health overview tersedia. Moderation CRUD post-MVP.
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

## Urutan lanjut

1. Role enum, organization middleware, policy, member management.
2. HTTP CRUD Question Bank dan Quiz Builder, validation, mobile UI.
3. Self-Paced deadline/manual grading/gradebook.
4. Live HTTP flow, broadcasts, leaderboard, browser E2E.
5. Upload + AI queue/review/quota.
6. Gamification, reports, moderation, deployment, load test.




