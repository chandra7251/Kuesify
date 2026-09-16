# Panduan Pengumpulan ZIP Kuesify

## Isi ZIP

ZIP harus memuat source code, PRD, dokumentasi, migration, seed, test, serta file konfigurasi contoh.

ZIP tidak memuat secret atau dependency hasil install:

- `.env`
- `vendor`
- `node_modules`
- `public/build`
- `public/hot`
- `.git`
- `test-results`
- `dump.rdb`
- log dan cache runtime

## Membuat ZIP

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\Build-SubmissionZip.ps1
```

Hasil ZIP dibuat pada folder `dist` dengan nama bertanggal.

## Jalankan Setelah Extract

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
```

Lihat `README.md` untuk service lokal dan akun demo.

## Validasi Sebelum Upload

```powershell
npm run lint
npm run build
php artisan test
npx playwright test
```
