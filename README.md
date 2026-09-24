# Kuesify

Platform kuis pendidikan interaktif. Kuesify mendukung pembuatan soal, kuis mandiri, tugas, sesi live realtime, organisasi, dan peran pengguna.

## Tech Stack

- Backend: Laravel 13, PHP 8.3+, MySQL 8, Redis 7.
- Frontend: Vue 3, TypeScript, Inertia.js, Vite, Tailwind CSS.
- Realtime: Laravel Reverb dan Laravel Echo.
- Queue: Laravel Queue dengan Redis.
- Testing: Pest PHP dan Playwright.
- AI opsional: Google Gemini untuk draft soal dari materi.

## Palet Warna & Desain Antarmuka (Mockup Reference)

Tampilan workspace dan antarmuka mengacu pada panduan desain terpadu:
- **Brand Primary** (`#3154D5`): Sidebar navigasi, top header banner, dan tombol aksi utama ("Buat Kuis").
- **Secondary** (`#0AB883`): Header "WORKSPACE" & "Selamat Datang Kembali", pill item aktif sidebar ("Dashboard"), tombol live quiz ("Buka Live Quiz"), kartu "Level Up!", indikator statistik ("Quiz", "Attempt Saya"), serta ikon badge aksi ("C", "T").
- **Accent** (`#E6F1F5`): Latar belakang (canvas background) area dashboard dan container ikon aksi cepat.
- **Support Colors (Opsional & Semantik)**:
  - `#D7A928`: Label & angka stat "Soal Aktif", indikator warning/draft.
  - `#7C869C`: Muted slate untuk subjudul, pembatas visual, dan teks penjelas.
  - `#4B392E`: Deep charcoal / dark tone untuk label & angka stat "Live Aktif" serta counter kontras tinggi.
- **Standar Shadow (Figma Drop Shadow)**:
  - `shadow-figma` (`0 4px 16px rgba(0, 0, 0, 0.08)`): Digunakan pada kartu utama (Hero Card, 4 Stat Cards, Kuis Terbaru, Streak Card, dan Banner Level Up) untuk elevasi lembut khas Figma di atas latar aksen `#E6F1F5`.
  - `shadow-figma-sm` (`0 2px 8px rgba(0, 0, 0, 0.06)`): Digunakan pada kartu aksi cepat kecil.
  - `shadow-figma-hover` (`0 6px 20px rgba(0, 0, 0, 0.12)`): Elevasi interaktif saat kursor diarahkan ke kartu.

## Prasyarat

- PHP 8.3+ dan Composer.
- Node.js 20+ dan npm.
- MySQL 8+ dan Redis 7+ untuk setup native.
- Docker Desktop opsional, untuk menjalankan MySQL dan Redis dalam container.

## Setup Native

```powershell
git clone https://github.com/chandra7251/Kuesify.git
Set-Location Kuesify
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
```

Atur database lokal di `.env`. Untuk Laragon atau MySQL lokal, pakai host `127.0.0.1`; jangan commit file `.env`.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kuesify
DB_USERNAME=root
DB_PASSWORD=
```

Buat kredensial Reverb lokal yang unik pada `.env`:

```env
REVERB_APP_ID=kuesify-local
REVERB_APP_KEY=ganti-dengan-key-acak
REVERB_APP_SECRET=ganti-dengan-secret-acak
```

Lalu siapkan aplikasi:

```powershell
php artisan migrate --seed
npm run build
```

Jalankan tiap proses pada terminal terpisah:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
npm run dev -- --host 127.0.0.1
php artisan queue:work redis --tries=3 --timeout=90
php artisan reverb:start --host=127.0.0.1 --port=8080
```

Buka `http://127.0.0.1:8000`. Jika port `8080` dipakai aplikasi lain, pindahkan Reverb ke port lain dan samakan `REVERB_PORT`, `REVERB_SERVER_PORT`, serta `VITE_REVERB_PORT`.

## Setup Docker

```powershell
Copy-Item .env.example .env
```

Isi nilai acak yang sama untuk `MYSQL_ROOT_PASSWORD` dan `DB_PASSWORD` pada `.env`, lalu jalankan:

```powershell
docker compose up --build -d
docker compose exec app php artisan migrate --seed
```

Service aplikasi tersedia pada `http://127.0.0.1:8000`; Reverb pada port `8080`.

## Email dan AI

- Development memakai `MAIL_MAILER=log`. Email verifikasi disimpan di `storage/logs/laravel.log`, tidak dikirim ke inbox.
- Untuk mengirim email sungguhan lewat Resend, verifikasi domain pengirim di dashboard Resend lalu isi `.env` (jangan commit API key):

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxxx
MAIL_FROM_ADDRESS="noreply@domain-terverifikasi.example"
MAIL_FROM_NAME="${APP_NAME}"
```

  Transport Resend (`symfony/resend-mailer`) sudah terpasang. Jalankan `php artisan config:clear` setelah mengubah konfigurasi, kemudian gunakan alur register atau reset password untuk menguji pengiriman.
- SMTP tetap dapat digunakan sebagai alternatif dengan mengisi konfigurasi `MAIL_*` pada `.env` sebelum deploy.
- Fitur AI draft soal menggunakan Google Gemini. Konfigurasikan pada `.env`:
  - `GEMINI_API_KEY`: API key Google Gemini.
  - `GEMINI_MODEL`: Model Gemini yang dipakai (default: `gemini-flash-lite-latest`).
  - `GEMINI_MONTHLY_GENERATION_QUOTA`: Batas generasi AI bulanan per organisasi (default: `100`).
  - `GEMINI_WEEKLY_CREATOR_QUOTA`: Batas generasi AI mingguan per creator (default: `10`), di-reset otomatis setiap hari Senin.
  - Teks materi dari berkas PDF dan PowerPoint (`.pptx`) diekstrak otomatis dan dikirim dengan format prompt terstruktur ke Gemini.

## Akun Demo

Password semua akun: `password`.

| Role | Email |
| --- | --- |
| Siswa / Peserta | `participant@kuesify.test` |
| Guru / Pengajar | `creator@kuesify.test` |
| Admin Organisasi | `  ` |
| Admin Platform | `superadmin@kuesify.test` |

Guest dapat masuk sesi live memakai PIN dan nama panggilan tanpa akun.

## Test dan Build

```powershell
npm run lint
npm run build
php artisan test
npx playwright test
```

## Keamanan Repository

- Jangan commit `.env`, token Gemini, password SMTP, password database, atau kredensial deploy.
- Gunakan `.env.example` hanya sebagai template tanpa nilai rahasia.
- Folder dependency, build output, log, hasil test, dump Redis, dan ZIP submission diabaikan oleh Git.
