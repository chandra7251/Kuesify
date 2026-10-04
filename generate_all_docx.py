import os
import sys
import docx
from docx import Document
from docx.shared import Pt, Inches, RGBColor, Mm
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import qn, nsdecls

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def add_heading_styled(doc, text, level):
    h = doc.add_heading(level=level)
    run = h.add_run(text)
    run.font.name = 'Arial'
    run.font.color.rgb = RGBColor(0, 0, 0)
    run.bold = True
    if level == 1:
        run.font.size = Pt(18)
        h.paragraph_format.space_before = Pt(14)
        h.paragraph_format.space_after = Pt(6)
    elif level == 2:
        run.font.size = Pt(14)
        h.paragraph_format.space_before = Pt(12)
        h.paragraph_format.space_after = Pt(4)
    elif level == 3:
        run.font.size = Pt(12)
        h.paragraph_format.space_before = Pt(8)
        h.paragraph_format.space_after = Pt(2)
    return h

def add_paragraph_styled(doc, text, bold_prefix=None, space_after=4):
    p = doc.add_paragraph()
    p.paragraph_format.space_after = Pt(space_after)
    p.paragraph_format.line_spacing = 1.15
    if bold_prefix:
        r_pre = p.add_run(bold_prefix)
        r_pre.font.name = 'Arial'
        r_pre.font.size = Pt(11)
        r_pre.bold = True
        r_pre.font.color.rgb = RGBColor(0, 0, 0)
    r_text = p.add_run(text)
    r_text.font.name = 'Arial'
    r_text.font.size = Pt(11)
    r_text.font.color.rgb = RGBColor(30, 41, 59)
    return p

def add_bullet_styled(doc, text, bold_prefix=None):
    p = doc.add_paragraph(style='List Bullet')
    p.paragraph_format.space_after = Pt(3)
    p.paragraph_format.line_spacing = 1.15
    if bold_prefix:
        r_pre = p.add_run(bold_prefix)
        r_pre.font.name = 'Arial'
        r_pre.font.size = Pt(11)
        r_pre.bold = True
        r_pre.font.color.rgb = RGBColor(0, 0, 0)
    r_text = p.add_run(text)
    r_text.font.name = 'Arial'
    r_text.font.size = Pt(11)
    r_text.font.color.rgb = RGBColor(30, 41, 59)
    return p

def add_screenshot_placeholder(doc, description_text):
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = table.cell(0, 0)
    set_cell_background(cell, "F1F5F9")
    set_cell_margins(cell, top=140, bottom=140, left=200, right=200)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after = Pt(6)
    r = p.add_run(f"[Foto Tangkapan Layar: {description_text}]")
    r.font.name = 'Arial'
    r.font.size = Pt(10)
    r.font.italic = True
    r.bold = True
    r.font.color.rgb = RGBColor(71, 85, 105)
    doc.add_paragraph().paragraph_format.space_after = Pt(4)

# ==========================================
# 1. PROPOSAL DOCX
# ==========================================
def build_proposal_docx(filename):
    doc = Document()
    for section in doc.sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)

    # Title
    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_after = Pt(2)
    r_title = p_title.add_run("PROPOSAL TEKNIS & INOVASI LOMBA WEB DEVELOPMENT")
    r_title.font.name = 'Arial'
    r_title.font.size = Pt(20)
    r_title.bold = True
    r_title.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(14)
    r_sub = p_sub.add_run("KUESIFY: Platform Edukasi Interaktif & Asesmen Multiplayer Berbasis AI & Real-time WebSockets\nINSYFEST 2026 — Tim: Kai Cenat Mewing Department")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading_styled(doc, "1. Executive Summary & Statement of Innovation", level=1)
    add_paragraph_styled(doc, "Di era Kurikulum Merdeka dan akselerasi transformasi digital pendidikan Indonesia, tenaga pendidik menghadapi tantangan struktural berupa beban administrasi pembuatan asesmen yang mencapai rata-rata 5.9 jam per minggu (Gallup-Walton Study). Di sisi lain, platform populer seperti Kahoot terbatas pada skema berbayar tinggi (free tier dibatasi 10 peserta per sesi) serta Google Forms yang minim elemen gamifikasi dan retensi belajar.")
    add_paragraph_styled(doc, "Kuesify hadir sebagai solusi platform edukasi interaktif full-stack modern yang menggabungkan engine multiplayer real-time berbasis Laravel Reverb WebSockets, pembuat kuis otomatis berbasis Gemini AI dari materi dokumen pendidik (PDF/PPTX), gamifikasi komprehensif (XP, Leveling, Badges, Heatmap Streak), serta arsitektur Multi-Tenant SaaS untuk institusi pendidikan Indonesia secara 100% inklusif.")

    add_heading_styled(doc, "2. Riset Pasar & Matriks Keunggulan Kompetitif", level=1)
    add_paragraph_styled(doc, "Berdasarkan analisis komparatif komprehensif, Kuesify memecahkan celah kritis yang ditinggalkan oleh pemain global:")
    
    table = doc.add_table(rows=6, cols=5)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    headers = ["Dimensi Evaluasi", "Kahoot!", "Quizizz", "Google Forms", "KUESIFY (Produk Tim)"]
    for i, h in enumerate(headers):
        cell = table.cell(0, i)
        set_cell_background(cell, "1E293B")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'
        r.bold = True
        r.font.size = Pt(9.5)
        r.font.color.rgb = RGBColor(255, 255, 255)
    
    matrix_data = [
        ["Real-time WebSocket Sync", "Ya (Terbatas)", "Partial", "Tidak (Statik HTTP)", "Ya (Laravel Reverb Sub-ms)"],
        ["AI Quiz dari Dokumen Guru", "Tidak Ada", "Sangat Terbatas", "Tidak Ada", "Ya (Gemini AI Parser PDF/PPT)"],
        ["Kapasitas Peserta Sesi Live", "Dibatasi 10 (Free)", "Terbatas Kuota", "Tanpa Batas (Statik)", "Gratis Tanpa Batas Peserta"],
        ["Gamifikasi Penuh (XP & Badge)", "Partial (Leaderboard)", "Partial", "Tidak Ada", "Penuh (XP, Level, Badge, Streak)"],
        ["Multi-Tenant SaaS Institusi", "Tidak Ada", "Tidak Ada", "Tidak Ada", "Ya (Isolasi Data Organisasi)"]
    ]
    for r_idx, row in enumerate(matrix_data, start=1):
        for c_idx, val in enumerate(row):
            cell = table.cell(r_idx, c_idx)
            set_cell_background(cell, "F8FAFC" if r_idx % 2 == 1 else "FFFFFF")
            p = cell.paragraphs[0]
            r = p.add_run(val)
            r.font.name = 'Arial'
            r.font.size = Pt(9)
            if c_idx == 4:
                r.bold = True
                r.font.color.rgb = RGBColor(0, 0, 0)

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    add_heading_styled(doc, "3. Spesifikasi Arsitektur & Rekayasa Perangkat Lunak", level=1)
    add_bullet_styled(doc, "Laravel 12.x dengan PHP 8.3 Strict Typing, Service Layer Pattern, dan Orm Eloquent.", bold_prefix="Backend Framework: ")
    add_bullet_styled(doc, "Vue 3 dengan Inertia.js (Single Page Application tanpa API duplication) dan Tailwind CSS.", bold_prefix="Frontend Engine: ")
    add_bullet_styled(doc, "Laravel Reverb WebSockets (Sub-millisecond latency, zero third-party quota cost, 100% server-authoritative).", bold_prefix="Real-time Engine: ")
    add_bullet_styled(doc, "Google Gemini AI Flash API dengan Guardrails Human-in-the-Loop review.", bold_prefix="Artificial Intelligence: ")
    add_bullet_styled(doc, "Pest PHP dengan 127 Unit & Feature Tests (827 Assertions — 100% Passing Status).", bold_prefix="Jaminan Kualitas Kode: ")

    add_heading_styled(doc, "4. Nilai Tambah Keberlanjutan & Dampak Sosial Pendidikan", level=1)
    add_paragraph_styled(doc, "Kuesify dirancang tidak hanya sebagai proyek kompetisi, tetapi sebagai produk yang siap di-deploy secara nasional untuk mendukung Kurikulum Merdeka di Indonesia. Arsitektur Multi-Tenant memungkinkan sekolah dan universitas mengelola ruang belajar secara mandiri dengan perlindungan data tingkat tinggi dan efisiensi biaya infrastruktur 0 Rupiah untuk koneksi real-time.")

    doc.save(filename)
    print(f"Proposal DOCX generated: {filename}")

# ==========================================
# 2. MANUAL BOOK DOCX
# ==========================================
def build_manualbook_docx(filename):
    doc = Document()
    for section in doc.sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_after = Pt(2)
    r_title = p_title.add_run("MANUAL BOOK & PANDUAN PENGGUNAAN KUESIFY")
    r_title.font.name = 'Arial'
    r_title.font.size = Pt(20)
    r_title.bold = True
    r_title.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(14)
    r_sub = p_sub.add_run("Panduan Lengkap 4 Role Pengguna & Dokumentasi Pengujian Sistem\nINSYFEST 2026 — Tim: Kai Cenat Mewing Department")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading_styled(doc, "1. Akun Pengujian & Credentials Demo Juri", level=1)
    add_paragraph_styled(doc, "Untuk mempermudah dewan juri dalam menguji seluruh alur dan role aplikasi di lingkungan lokal (http://localhost:8000), berikut adalah kredensial resmi yang siap digunakan:")

    table = doc.add_table(rows=5, cols=4)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    headers = ["Role Pengguna", "Email Credentials", "Password", "Hak Akses & Otoritas Utama"]
    for i, h in enumerate(headers):
        cell = table.cell(0, i)
        set_cell_background(cell, "1E293B")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'
        r.bold = True
        r.font.size = Pt(9.5)
        r.font.color.rgb = RGBColor(255, 255, 255)

    creds = [
        ["Super Admin", "superadmin@kuesify.com", "password", "Manajemen global tenant, kategori, moderasi kuis publik, AI monitoring."],
        ["Org Admin", "admin@kuesify.com", "password", "Kelola anggota organisasi, pembagian grup kelas, pengaturan institusi."],
        ["Creator (Guru)", "creator@kuesify.com", "password", "Buat kuis AI/manual, bank soal context-aware, Host Live Session, nilai essay."],
        ["Participant (Siswa)", "participant@kuesify.com", "password", "Join Live PIN, kerjakan kuis mandiri, klaim badge/XP, baca materi."]
    ]
    for r_idx, row in enumerate(creds, start=1):
        for c_idx, val in enumerate(row):
            cell = table.cell(r_idx, c_idx)
            set_cell_background(cell, "F8FAFC" if r_idx % 2 == 1 else "FFFFFF")
            p = cell.paragraphs[0]
            r = p.add_run(val)
            r.font.name = 'Arial'
            r.font.size = Pt(9)
            if c_idx == 0:
                r.bold = True

    doc.add_paragraph().paragraph_format.space_after = Pt(4)

    add_heading_styled(doc, "2. Panduan Instalasi & Jalankan Sistem (Technical Setup)", level=1)
    add_bullet_styled(doc, "Jalankan 'composer install' dan 'npm install' untuk mengunduh seluruh dependensi.", bold_prefix="Langkah 1 (Dependencies): ")
    add_bullet_styled(doc, "Salin file .env.example menjadi .env, pastikan BROADCAST_CONNECTION=reverb dan QUEUE_CONNECTION=sync.", bold_prefix="Langkah 2 (Environment): ")
    add_bullet_styled(doc, "Jalankan 'php artisan migrate:fresh --seed' untuk menginstansiasi database beserta kredensial demo.", bold_prefix="Langkah 3 (Database): ")
    add_bullet_styled(doc, "Jalankan 'php artisan reverb:start' di satu terminal untuk mengaktifkan engine WebSockets real-time.", bold_prefix="Langkah 4 (WebSocket Server): ")
    add_bullet_styled(doc, "Jalankan 'php artisan serve' dan 'npm run dev' untuk membuka portal aplikasi di http://localhost:8000.", bold_prefix="Langkah 5 (Web Server): ")

    add_heading_styled(doc, "3. Workflow Penggunaan Per Role", level=1)
    add_heading_styled(doc, "A. Role Participant (Siswa / Peserta)", level=2)
    add_bullet_styled(doc, "Buka '/join', masukkan 6-digit PIN dari Host. Pilih avatar dari 13 pilihan khas Kuesify (auto-fill jika terautentikasi).")
    add_bullet_styled(doc, "Saat kuis dimulai, jawab soal sebelum timer habis. Pilihan langsung terkunci (one-shot lock). Layar menampilkan umpan balik Benar (+poin) / Salah (kunci jawaban).")
    add_bullet_styled(doc, "Akses '/notifications' dengan badge merah indikator unread di sidebar navigasi.")

    add_heading_styled(doc, "B. Role Creator (Guru / Pengajar)", level=2)
    add_bullet_styled(doc, "Buka Bank Soal ('/creator/question-bank'). Pilih tipe soal (Pilihan Ganda opsi A-E, True/False, Isian, Essay) untuk menampilkan form input dinamis.")
    add_bullet_styled(doc, "Mulai Live Session dari kuis yang ada. Kendalikan urutan soal ('Soal ke X dari Y') dan aktifkan Intermission Podium Top 3.")
    add_bullet_styled(doc, "Buka '/attempts' untuk menilai jawaban essay siswa. Sistem otomatis mengirim notifikasi ke peserta.")

    add_heading_styled(doc, "C. Role Organization Admin & Super Admin", level=2)
    add_bullet_styled(doc, "Org Admin mengelola anggota kelas dan laporan di '/admin/members' & '/reports'. Sidebar bersih dari fitur pembuatan kuis.")
    add_bullet_styled(doc, "Super Admin mengelola tenant global, kategori kuis, dan moderasi kuis publik di '/admin/moderation'.")

    doc.save(filename)
    print(f"Manual Book DOCX generated: {filename}")

# ==========================================
# 3. TAMPILAN WEB DOCX (Placeholder Foto Lengkap Seluruh Route)
# ==========================================
def build_tampilanweb_docx(filename):
    doc = Document()
    for section in doc.sections:
        section.top_margin = Inches(1)
        section.bottom_margin = Inches(1)
        section.left_margin = Inches(1)
        section.right_margin = Inches(1)

    p_title = doc.add_paragraph()
    p_title.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_title.paragraph_format.space_after = Pt(2)
    r_title = p_title.add_run("DOKUMENTASI VISUAL TAMPILAN WEBSITE KUESIFY")
    r_title.font.name = 'Arial'
    r_title.font.size = Pt(20)
    r_title.bold = True
    r_title.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(14)
    r_sub = p_sub.add_run("Katalog Lengkap Tangkapan Layar Berdasarkan Route & User Flow Aplikasi\nLink Local Domain Testing: http://localhost:8000\nINSYFEST 2026 — Tim: Kai Cenat Mewing Department")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading_styled(doc, "1. Flow Autentikasi & Profil Pengguna", level=1)
    
    add_heading_styled(doc, "Route: / (Landing Page)", level=2)
    add_paragraph_styled(doc, "Halaman utama publik menampilkan hero section banner carousel, fitur unggulan Kuesify, dan tombol masuk/daftar.")
    add_screenshot_placeholder(doc, "Halaman Utama Landing Page Kuesify dengan Hero Banner Carousel dan Tombol CTA Login/Register")

    add_heading_styled(doc, "Route: /login & /register (Auth Slide Modal)", level=2)
    add_paragraph_styled(doc, "Halaman autentikasi terpadu dengan efek slide animation dan ilustrasi latar belakang terburam.")
    add_screenshot_placeholder(doc, "Formulir Login dan Register Terpadu dengan Slide Animation Single-Page Auth")

    add_heading_styled(doc, "Route: /choose-avatar & /profile", level=2)
    add_paragraph_styled(doc, "Halaman pemilihan 13 avatar unik Kuesify dan pengaturan profil pengguna serta preferensi akun.")
    add_screenshot_placeholder(doc, "Antarmuka Pemilihan Avatar Profil (13 Pilihan Karakter) dan Halaman Edit Profil")

    add_heading_styled(doc, "2. Flow Live Multiplayer Quiz (Real-time WebSockets)", level=1)

    add_heading_styled(doc, "Route: /join (Live Join PIN & Avatar Picker Step)", level=2)
    add_paragraph_styled(doc, "Halaman peserta memasukkan 6-digit PIN kuis live, diikuti oleh langkah memilih avatar sementara.")
    add_screenshot_placeholder(doc, "Halaman Join Live Quiz dengan Input PIN 6-Digit dan Stepper Avatar Picker Peserta")

    add_heading_styled(doc, "Route: /live-sessions/{session}/play (Layar Host Live Session)", level=2)
    add_paragraph_styled(doc, "Tampilan kontrol Host menampilkan indikator 'Soal ke X dari Y', hitung mundur waktu, dan tombol Intermission Podium Top 3.")
    add_screenshot_placeholder(doc, "Dashboard Host Live Quiz Menampilkan Pertanyaan Aktif, Countage Soal 'Soal ke X dari Y', dan Kontrol Intermission")

    add_heading_styled(doc, "Route: /live-sessions/{session}/play (Layar Participant Live Session)", level=2)
    add_paragraph_styled(doc, "Layar interaktif peserta saat menjawab soal dengan penguncian tombol satu kali dan banner umpan balik Benar/Salah.")
    add_screenshot_placeholder(doc, "Tampilan Peserta Live Quiz Menampilkan Tombol Pilihan Jawaban Terkunci dan Feedback Real-time Benar (+XP) / Salah")

    add_heading_styled(doc, "3. Flow Creator (Guru / Pembuat Kuis)", level=1)

    add_heading_styled(doc, "Route: /creator/question-bank (Context-Aware Question Bank)", level=2)
    add_paragraph_styled(doc, "Formulir pembuatan soal cerdas yang menyesuaikan opsi input berdasarkan tipe soal (Pilihan Ganda A-E, True/False, Isian, Essay).")
    add_screenshot_placeholder(doc, "Form Creator Question Bank dengan Adaptif Input Tipe Soal (Pilihan Ganda Opsi A-E & Radio True/False)")

    add_heading_styled(doc, "Route: /quizzes & /materials (AI Quiz Builder & Documents)", level=2)
    add_paragraph_styled(doc, "Daftar kuis creator dan fitur ekstraksi materi PDF/PPTX menjadi draft soal otomatis berbasis Gemini AI.")
    add_screenshot_placeholder(doc, "Dashboard Manajemen Kuis Creator dan Modal Upload Dokumen AI Generator")

    add_heading_styled(doc, "Route: /attempts (Penilaian Essay & Gradebook Creator)", level=2)
    add_paragraph_styled(doc, "Halaman khusus creator untuk memeriksa dan memberikan nilai pada soal tipe essay peserta.")
    add_screenshot_placeholder(doc, "Halaman Penilaian Essay Peserta oleh Creator dengan Input Nilai Manual dan Catatan Review")

    add_heading_styled(doc, "4. Flow Participant (Siswa / Gamifikasi)", level=1)

    add_heading_styled(doc, "Route: /dashboard & /participant/badges (Dashboard Gamifikasi)", level=2)
    add_paragraph_styled(doc, "Dashboard siswa berisi ringkasan XP, level progression bar, streak heatmap calendar, dan katalog badge.")
    add_screenshot_placeholder(doc, "Dashboard Gamifikasi Participant Menampilkan Level Progress Bar, Heatmap Streak, dan Grid Badge Unlocked")

    add_heading_styled(doc, "Route: /notifications (Pusat Notifikasi Dua Arah)", level=2)
    add_paragraph_styled(doc, "Halaman notifikasi siswa dan creator dengan dukungan badge merah indikator unread di sidebar navigasi.")
    add_screenshot_placeholder(doc, "Halaman Pusat Notifikasi Pengguna dengan Indikator Unread Red Badge di Sidebar Navigasi")

    add_heading_styled(doc, "5. Flow Administrator & Organisasi", level=1)

    add_heading_styled(doc, "Route: /admin/members & /reports (Org Admin Dashboard)", level=2)
    add_paragraph_styled(doc, "Dashboard admin organisasi untuk mengelola anggota, grup kelas, dan laporan analitik belajar yang responsif.")
    add_screenshot_placeholder(doc, "Halaman Laporan Analitik dan Manajemen Anggota Organisasi pada Mode Mobile/Desktop")

    add_heading_styled(doc, "Route: /admin & /superadmin/tenants (Super Admin Control)", level=2)
    add_paragraph_styled(doc, "Panel kontrol utama platform untuk pemantauan multi-tenant, manajemen kategori global, dan moderasi kuis publik.")
    add_screenshot_placeholder(doc, "Panel Control Super Admin Menampilkan Metrik Platform Global dan Tabel Moderasi Kuis Publik")

    doc.save(filename)
    print(f"Tampilan Web DOCX generated: {filename}")

if __name__ == "__main__":
    build_proposal_docx("[Proposal] INSYFEST2026-KaiCenatMewingDepartment.docx")
    build_manualbook_docx("[ManualBook] INSYFEST2026-KaiCenatMewingDepartment.docx")
    build_tampilanweb_docx("[TampilanWeb] INSYFEST2026-KaiCenatMewingDepartment.docx")
