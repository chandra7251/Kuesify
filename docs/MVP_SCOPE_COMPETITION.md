# Kuesify — MVP Scope Kompetisi

Status: disepakati untuk implementasi pada 15 September 2026.

## Tujuan

Mendemokan platform kuis pendidikan yang dapat dipakai creator untuk membuat kuis, peserta untuk mengerjakan, dan host untuk menjalankan sesi live. Nilai utama: pembelajaran terasa aktif, cepat, dan mudah dipakai dari ponsel.

## Definisi Selesai

MVP selesai bila tiga alur berikut dapat didemokan tanpa data manual di database:

1. Creator membuat kuis, menambah soal, mengurutkan, lalu publish.
2. Peserta menyelesaikan satu kuis mandiri dan melihat nilai atau status `pending_review` untuk essay.
3. Host membuka Live Quiz; peserta masuk dengan PIN; soal berpindah; skor dan leaderboard akhir tampil.

## P0 — Wajib untuk MVP

### Role

- `Creator`: membuat, edit, publish, dan menjalankan kuis miliknya.
- `Registered Participant`: mengerjakan kuis mandiri dan melihat hasil sendiri.
- `Guest Participant`: masuk Live Quiz memakai PIN dan alias.
- `Organization Admin` dan `Super Admin`: hanya diperlukan sebagai akun seed dan guard keamanan; tidak perlu layar admin lengkap untuk demo MVP.

### Creator dan Question Bank

- Login/register, workspace organisasi personal, dan role guard.
- Question Bank tenant-scoped: create, edit, delete, search/filter, tag, kategori.
- Tipe soal: multiple choice, true/false, fill blank, dan essay pendek.
- Buat kuis: judul, deskripsi, kategori, visibility, deadline, max attempts, pembahasan.
- Tambah/hapus/reorder soal, preview sederhana, publish guard minimal satu soal.
- Import dan export CSV. XLSX tetap kompatibilitas tambahan, bukan acceptance blocker.

### Self-Paced/Homework

- Peserta memulai kuis published.
- Server menegakkan deadline dan max attempts.
- Jawaban objektif dinilai server-side.
- Essay berstatus `pending_review`; creator memberi nilai dan feedback.
- Hasil peserta menunjukkan status dan skor akhir.

### Live Quiz

- Creator membuka lobby dari kuis tanpa essay.
- PIN enam digit dan alias guest.
- Host dapat lock lobby, start, next question, kick, dan end.
- Timer dihitung server-side; satu jawaban per soal; skor server-authoritative.
- Realtime event untuk mulai sesi, transisi soal, jawaban, leaderboard, dan akhir sesi.
- Leaderboard akhir/podium sederhana.

### Kualitas Minimum

- Semua data tenant-scoped dan policy role aktif.
- Validasi request, CSRF, rate limit PIN/upload/AI, upload 25 MB PDF/PPT/PPTX dengan signature check.
- Feature test untuk alur creator, participant, guest live, dan guard role.
- Browser E2E untuk tiga alur demo di atas.
- Mobile-first: 375 px, tombol minimal 44 px, keyboard focus, error field jelas.

## P1 — Bonus Demo

- AI Gemini dari PDF/PPT/PPTX: upload, queue, draft, edit/reject/approve.
- XP, level sederhana, streak, lima badge, dan ringkasan dashboard.
- CSV report nilai dan analitik kuis dasar.
- QR join Live Quiz.

P1 hanya didemokan bila stabil. Kegagalan API Gemini atau Reverb tidak boleh merusak alur manual P0.

## Post-MVP

- XLSX export dan template import kompleks.
- Moderasi kuis publik, halaman Super Admin lengkap, class/departemen model.
- Monitoring production lengkap, Horizon, TLS/domain, audit log penuh, load test 100 peserta.
- AI virus scan, OCR gambar, advanced analytics, leaderboard per pertanyaan, social share, native mobile.

## Bukan Scope Kompetisi

- Marketplace, billing, SSO enterprise, LTI, proctoring, native mobile, battle mode, avatar, video/audio question.

## Urutan Implementasi

1. Lengkapi Creator Builder dan Self-Paced result/gradebook.
2. Lengkapi Live realtime: channel auth, Echo, event, host/player/podium.
3. Tambah E2E tiga alur demo dan mobile QA.
4. Stabilkan AI sebagai bonus; matikan secara aman bila `GEMINI_API_KEY` tidak tersedia.
