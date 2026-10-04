<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Oops') — Kuesify</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; }
        body {
            font-family: Figtree, ui-sans-serif, system-ui, sans-serif;
            background: #E6F1F5;
            color: #0f172a;
            display: grid;
            place-items: center;
            min-height: 100vh;
            padding: 2rem 1.25rem;
        }
        .card {
            background: #fff;
            border-radius: 1.5rem;
            box-shadow: 0 4px 16px rgba(0,0,0,.08);
            max-width: 34rem;
            width: 100%;
            padding: 3rem 2.5rem;
            text-align: center;
        }
        .code {
            font-size: 5rem;
            font-weight: 900;
            line-height: 1;
            color: #3154D5;
            letter-spacing: -0.05em;
        }
        .title {
            margin-top: 1rem;
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
        }
        .desc {
            margin-top: .75rem;
            font-size: 1rem;
            color: #64748b;
            line-height: 1.6;
        }
        .actions {
            margin-top: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: .75rem;
            justify-content: center;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: #3154D5;
            color: #fff;
            font-weight: 700;
            font-size: .875rem;
            padding: .75rem 1.5rem;
            border-radius: .75rem;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-primary:hover { background: #2645B8; }
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: transparent;
            border: 2px solid #cbd5e1;
            color: #475569;
            font-weight: 700;
            font-size: .875rem;
            padding: .75rem 1.5rem;
            border-radius: .75rem;
            text-decoration: none;
            transition: border-color .15s, color .15s;
        }
        .btn-secondary:hover { border-color: #3154D5; color: #3154D5; }
        .logo { display: flex; align-items: center; justify-content: center; gap: .5rem; margin-bottom: 2rem; }
        .logo-text { font-size: 1.25rem; font-weight: 900; letter-spacing: -0.03em; color: #0f172a; }
        .logo svg { width: 2rem; height: 2rem; color: #3154D5; }
    </style>
</head>
<body>
<div class="card">
    <div class="logo">
        <svg viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect width="40" height="40" rx="10" fill="#3154D5"/>
            <path d="M12 20a8 8 0 1 1 16 0 8 8 0 0 1-16 0Z" fill="#90CB31" opacity=".35"/>
            <path d="M20 14v6l4 2" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="logo-text">kuesify</span>
    </div>
    <p class="code">@yield('code', '?')</p>
    <h1 class="title">@yield('title', 'Terjadi kesalahan')</h1>
    <p class="desc">@yield('description', 'Sesuatu yang tidak terduga terjadi. Coba lagi atau kembali ke halaman utama.')</p>
    <div class="actions">
        <a href="/" class="btn-primary">← Beranda</a>
        <a href="javascript:history.back()" class="btn-secondary">Kembali</a>
    </div>
</div>
</body>
</html>