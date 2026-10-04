import os
import sys
import docx
from docx import Document
from docx.shared import Pt, Inches, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import qn, nsdecls

def set_cell_background(cell, fill_hex):
    tcPr = cell._tc.get_or_add_tcPr()
    shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
    tcPr.append(shd)

def set_cell_margins(cell, top=140, bottom=140, left=200, right=200):
    tcPr = cell._tc.get_or_add_tcPr()
    tcMar = OxmlElement('w:tcMar')
    for m, val in [('top', top), ('bottom', bottom), ('left', left), ('right', right)]:
        node = OxmlElement(f'w:{m}')
        node.set(qn('w:w'), str(val))
        node.set(qn('w:type'), 'dxa')
        tcMar.append(node)
    tcPr.append(tcMar)

def add_heading(doc, text, level):
    h = doc.add_heading(level=level)
    run = h.add_run(text)
    run.font.name = 'Arial'
    run.font.color.rgb = RGBColor(0, 0, 0)
    run.bold = True
    if level == 1:
        run.font.size = Pt(16)
        h.paragraph_format.space_before = Pt(14)
        h.paragraph_format.space_after = Pt(6)
    elif level == 2:
        run.font.size = Pt(13)
        h.paragraph_format.space_before = Pt(12)
        h.paragraph_format.space_after = Pt(4)
    elif level == 3:
        run.font.size = Pt(11)
        h.paragraph_format.space_before = Pt(8)
        h.paragraph_format.space_after = Pt(2)
    return h

def add_p(doc, text, bold_prefix=None, space_after=4):
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

def add_bullet(doc, text, bold_prefix=None):
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

def add_img_placeholder(doc, desc):
    table = doc.add_table(rows=1, cols=1)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    cell = table.cell(0, 0)
    set_cell_background(cell, "F1F5F9")
    set_cell_margins(cell, top=140, bottom=140, left=200, right=200)
    p = cell.paragraphs[0]
    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p.paragraph_format.space_before = Pt(6)
    p.paragraph_format.space_after = Pt(6)
    r = p.add_run(f"[Foto Tangkapan Layar: {desc}]")
    r.font.name = 'Arial'
    r.font.size = Pt(10)
    r.font.italic = True
    r.bold = True
    r.font.color.rgb = RGBColor(71, 85, 105)
    doc.add_paragraph().paragraph_format.space_after = Pt(4)

# ==============================================================================
# BUILD PROPOSAL (LIDM IPDP Style - TANPA Halaman Pengesahan & TANPA Abstrak)
# ==============================================================================
def generate_proposal():
    doc = Document()
    for s in doc.sections:
        s.top_margin = Inches(1)
        s.bottom_margin = Inches(1)
        s.left_margin = Inches(1)
        s.right_margin = Inches(1)

    p_cover = doc.add_paragraph()
    p_cover.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_cover.paragraph_format.space_after = Pt(2)
    r = p_cover.add_run("PROPOSAL LOMBA INOVASI DIGITAL MAHASISWA (LIDM)\nDIVISI INOVASI PEMBELAJARAN DIGITAL PENDIDIKAN (IPDP)")
    r.font.name = 'Arial'
    r.font.size = Pt(16)
    r.bold = True
    r.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(16)
    r_sub = p_sub.add_run("KUESIFY: Platform Asesmen Edukasi Interaktif & Multiplayer Live Quiz Berbasis AI dan Real-Time WebSockets\nNama Tim: Kai Cenat Mewing Department | Tahun: 2026")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading(doc, "1. Latar Belakang & Urgensi Inovasi", level=1)
    add_p(doc, "Pendidikan era digital mengharuskan guru untuk tidak hanya menyampaikan materi, tetapi juga mengukur tingkat pemahaman siswa secara realtime dan personal. Namun, riset Gallup-Walton Foundation menunjukkan bahwa guru menghabiskan waktu rata-rata 5.9 jam per minggu hanya untuk menyiapkan materi dan asesmen. Platform populer seperti Kahoot! membatasi versi gratisnya hingga 10 peserta per sesi—memaksa sekolah membayar USD 36 hingga 228 per tahun yang menjadi beban anggaran pendidikan. Sementara Google Forms tidak memiliki elemen gamifikasi, sehingga memicu rasa bosan bagi peserta didik.")
    add_p(doc, "Kuesify memecahkan kebuntuan tersebut dengan menyediakan platform gratis untuk kelas penuh tanpa batasan peserta, didukung teknologi modern real-time WebSockets dan AI parser untuk materi dokumen.")

    add_heading(doc, "2. Tujuan dan Manfaat", level=1)
    add_bullet(doc, "Mata kuliah dan materi pembelajaran terasa lebih menyenangkan melalui kompetisi live multiplayer, leaderboard interaktif, serta perolehan XP dan Badge.", bold_prefix="Bagi Peserta Didik: ")
    add_bullet(doc, "Menghemat waktu penyusunan soal kuis dari jam menjadi menit menggunakan Gemini AI Parser, serta kemudahan dalam mengevaluasi jawaban essay secara terpusat.", bold_prefix="Bagi Guru / Pengajar: ")
    add_bullet(doc, "Menyediakan arsitektur Multi-Tenant SaaS dengan isolasi data antar sekolah, efisiensi server 0 Rupiah biaya kuota pihak ketiga, serta analitik belajar terpadu.", bold_prefix="Bagi Institusi Pendidikan: ")

    add_heading(doc, "3. Inovasi Penggunaan Teknologi Digital & Keunggulan Stack", level=1)
    add_heading(doc, "3.1 Konsep dan Kebaruan Inovasi", level=2)
    add_p(doc, "Kuesify mengintegrasikan empat pilar utama: Real-Time Engine, Generative AI Parsing, Gamification Loop, dan Multi-Tenant Architecture ke dalam satu ekosistem terpadu.")

    add_heading(doc, "3.2 Arsitektur dan Detail Keunggulan Tech Stack", level=2)
    add_bullet(doc, "Menyediakan fitur Strict Typing, Service Layer Pattern, Eloquent ORM, dan pengujian Pest PHP (127 tests/827 assertions passing 100%). Menjamin keandalan logika bisnis di tingkat server.", bold_prefix="Laravel 12.x & PHP 8.3 (Backend Core): ")
    add_bullet(doc, "Arsitektur Single Page Application (SPA) tanpa perlu membangun API terpisah, menghasilkan pergerakan halaman yang seamless tanpa reload layar.", bold_prefix="Vue 3 & Inertia.js (Frontend Engine): ")
    add_bullet(doc, "Server WebSocket bawaan Laravel yang berjalan di tingkat server lokal tanpa ketergantungan pada SaaS pihak ketiga (seperti Pusher atau Ably). Menghasilkan latensi sub-millisecond tanpa batasan kuota pesan.", bold_prefix="Laravel Reverb (Real-time WebSockets): ")
    add_bullet(doc, "Mengekstrak file materi (PDF/PPTX) secara otomatis menjadi draf kuis berkonsep Bloom's Taxonomy lengkap dengan pilihan jawaban dan pembahasan.", bold_prefix="Google Gemini AI (Document Parser): ")

    add_heading(doc, "3.3 Matriks Perbandingan Keunggulan Kompetitif", level=2)
    table = doc.add_table(rows=5, cols=5)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    headers = ["Kriteria Evaluasi", "Kahoot!", "Quizizz", "Google Forms", "KUESIFY (Produk Tim)"]
    for i, h in enumerate(headers):
        cell = table.cell(0, i)
        set_cell_background(cell, "1E293B")
        p = cell.paragraphs[0]
        r = p.add_run(h)
        r.font.name = 'Arial'
        r.bold = True
        r.font.size = Pt(9.5)
        r.font.color.rgb = RGBColor(255, 255, 255)
    
    matrix = [
        ["Kapasitas Peserta Free", "Terbatas 10 Peserta", "Terbatas Kuota", "Tanpa Batas (Statik)", "Gratis Tanpa Batas Peserta"],
        ["Real-time WebSocket Sync", "Ya (Terbatas)", "Partial", "Tidak Ada", "Ya (Laravel Reverb Sub-ms)"],
        ["AI Quiz dari Dokumen Guru", "Tidak Ada", "Sangat Terbatas", "Tidak Ada", "Ya (Gemini AI PDF/PPTX)"],
        ["Gamifikasi (XP, Badge, Streak)", "Partial", "Partial", "Tidak Ada", "Penuh (XP, Level, Badge, Heatmap)"]
    ]
    for r_idx, row in enumerate(matrix, start=1):
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

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    add_heading(doc, "4. Penjelasan Strategi dan Metode Evaluasi Pembelajaran", level=1)
    add_p(doc, "Kuesify menerapkan Formative Feedback Loop, di mana peserta didik langsung mendapatkan feedback apakah jawabannya Benar (+poin XP) atau Salah (beserta kunci jawaban dan penjelasan) segera setelah menjawab soal di layar peserta. Hal ini mempercepat proses perbaikan pemahaman konsep sebelum melangkah ke topik berikutnya.")

    add_heading(doc, "5. Dampak Pembelajaran dan Rencana Keberlanjutan", level=1)
    add_p(doc, "Kuesify mendukung pembentukan karakter siswa yang tangguh melalui mekanisme Daily Streak Heatmap yang melatih disiplin belajar harian. Secara institusional, Kuesify siap diintegrasikan pada jaringan sekolah menengah dan perguruan tinggi di Indonesia melalui arsitektur Multi-Tenant SaaS.")

    doc.save("[Proposal] INSYFEST2026-KaiCenatMewingDepartment.docx")
    print("Updated Proposal DOCX successfully (No Approval / No Abstract)!")

# ==============================================================================
# BUILD MANUAL BOOK
# ==============================================================================
def generate_manualbook():
    doc = Document()
    for s in doc.sections:
        s.top_margin = Inches(1)
        s.bottom_margin = Inches(1)
        s.left_margin = Inches(1)
        s.right_margin = Inches(1)

    p_cover = doc.add_paragraph()
    p_cover.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_cover.paragraph_format.space_after = Pt(2)
    r = p_cover.add_run("MANUAL BOOK & PANDUAN PENGGUNAAN APLIKASI KUESIFY")
    r.font.name = 'Arial'
    r.font.size = Pt(16)
    r.bold = True
    r.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(16)
    r_sub = p_sub.add_run("Dokumentasi Operasional 4 Role Pengguna Lengkap dengan Visualisasi Antarmuka\nNama Tim: Kai Cenat Mewing Department | Tahun: 2026")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading(doc, "1. Akun Pengujian & Credentials Demo Juri", level=1)
    add_p(doc, "Berikut adalah kredensial akun pengujian yang telah disediakan di database lokal untuk diuji oleh dewan juri:")

    table = doc.add_table(rows=5, cols=4)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    headers = ["Role Pengguna", "Email Credentials", "Password", "Fungsi & Hak Akses Utama"]
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
        ["Super Admin", "superadmin@kuesify.com", "password", "Akses global tenant, moderasi kuis publik, monitoring AI."],
        ["Org Admin", "admin@kuesify.com", "password", "Akses manajemen anggota, grup kelas, dan laporan analitik."],
        ["Creator (Guru)", "creator@kuesify.com", "password", "Akses pembuat kuis AI/manual, Bank Soal, Host Live, dan penilaian essay."],
        ["Participant (Siswa)", "participant@kuesify.com", "password", "Akses Join Live PIN, pengerjaan kuis mandiri, badge/XP, dan notifikasi."]
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

    doc.add_paragraph().paragraph_format.space_after = Pt(6)

    add_heading(doc, "2. Panduan Instalasi dan Setup Lingkungan Lokal", level=1)
    add_bullet(doc, "Jalankan 'composer install' dan 'npm install' pada direktori proyek Kuesify.", bold_prefix="Langkah 1 (Dependencies): ")
    add_bullet(doc, "Pastikan file .env memiliki konfigurasi BROADCAST_CONNECTION=reverb dan QUEUE_CONNECTION=sync.", bold_prefix="Langkah 2 (Environment): ")
    add_bullet(doc, "Eksekusi 'php artisan migrate:fresh --seed' untuk menginstansiasi skema tabel dan akun demo.", bold_prefix="Langkah 3 (Database Seeding): ")
    add_bullet(doc, "Buka terminal khusus dan jalankan 'php artisan reverb:start' untuk mengaktifkan server WebSocket real-time.", bold_prefix="Langkah 4 (WebSocket Server): ")
    add_bullet(doc, "Jalankan 'php artisan serve' dan 'npm run dev' lalu buka portal di http://localhost:8000.", bold_prefix="Langkah 5 (Jalankan App): ")

    add_heading(doc, "3. Panduan Operasional Lengkap 4 Role Pengguna", level=1)

    # ROLE 1: PARTICIPANT
    add_heading(doc, "3.1 Role Participant (Siswa / Peserta Didik)", level=2)
    add_p(doc, "1. Membuka halaman '/join', memasukkan 6-digit PIN kuis live yang diberikan oleh Host/Guru.")
    add_p(doc, "2. Memilih avatar profil dari 13 pilihan avatar khas Kuesify (apabila pengguna sudah login, sistem otomatis mengisi alias dan melewati langkah avatar).")
    add_img_placeholder(doc, "Tampilan Layar Join Live Quiz untuk Peserta (Input PIN & Multi-step Avatar Selection)")

    add_p(doc, "3. Saat kuis live dimulai, peserta melihat pertanyaan dan pilihan jawaban. Klik pada salah satu opsi akan secara otomatis mengunci jawaban (one-shot lock).")
    add_p(doc, "4. Peserta menerima umpan balik seketika: Indikator Benar (+XP) berwarna hijau atau Salah (disertai pembukaan kunci jawaban yang benar) berwarna merah.")
    add_img_placeholder(doc, "Tampilan Interaktif Layar Peserta Saat Mengunci Jawaban & Menerima Feedback Benar/Salah")

    add_p(doc, "5. Peserta dapat memantau perolehan XP, Leveling, Katalog Badge yang didapatkan, serta mengecek pesan di halaman Notifikasi.")
    add_img_placeholder(doc, "Dashboard Gamifikasi Peserta (Level Progress Bar, Badge Grid, Notifikasi dengan Unread Badge)")

    # ROLE 2: CREATOR
    add_heading(doc, "3.2 Role Creator (Guru / Pengajar)", level=2)
    add_p(doc, "1. Pembuatan Soal Context-Aware di '/creator/question-bank': Guru memilih tipe soal (Pilihan Ganda A-E, True/False radio, Isian, Essay) di mana formulir akan menyesuaikan input jawaban secara otomatis.")
    add_img_placeholder(doc, "Tampilan Formulir Creator Question Bank Context-Aware Berdasarkan Tipe Soal")

    add_p(doc, "2. Mengontrol Sesi Live Quiz sebagai Host: Host dapat memantau indikator progres 'Soal ke X dari Y', menghitung mundur waktu otomatis, serta mengaktifkan Intermission Podium Top 3 di akhir soal.")
    add_img_placeholder(doc, "Tampilan Dashboard Host Live Session (Countage Soal 'Soal ke X dari Y' & Layar Intermission Podium)")

    add_p(doc, "3. Menilai Jawaban Essay di '/attempts': Guru melihat jawaban essay peserta, memberikan skor manual, dan sistem otomatis mengirimkan notifikasi ke akun peserta terkait.")
    add_img_placeholder(doc, "Tampilan Halaman Penilaian Essay Peserta oleh Creator & Kirim Notifikasi")

    # ROLE 3: ORGANIZATION ADMIN
    add_heading(doc, "3.3 Role Organization Admin (Administrator Sekolah/Institusi)", level=2)
    add_p(doc, "1. Mengelola daftar anggota organisasi dan pembagian grup kelas pada halaman '/admin/members'.")
    add_p(doc, "2. Memantau laporan analitik hasil kuis peserta di '/reports' (layar telah dioptimalkan agar responsif pada perangkat mobile).")
    add_img_placeholder(doc, "Tampilan Halaman Laporan Analitik & Manajemen Anggota Organisasi (Responsif Mobile)")

    # ROLE 4: SUPER ADMIN
    add_heading(doc, "3.4 Role Super Admin (Administrator Platform Global)", level=2)
    add_p(doc, "1. Memantau metrik global platform, manajemen tenant multi-organisasi di '/superadmin/tenants'.")
    add_p(doc, "2. Melakukan moderasi kuis publik yang diajukan oleh creator di '/admin/moderation' (Approve/Reject).")
    add_img_placeholder(doc, "Tampilan Panel Kontrol Super Admin & Antarmuka Moderasi Kuis Publik Global")

    doc.save("[ManualBook] INSYFEST2026-KaiCenatMewingDepartment.docx")
    print("Updated Manual Book DOCX successfully!")

# ==============================================================================
# BUILD TAMPILAN WEB
# ==============================================================================
def generate_tampilanweb():
    doc = Document()
    for s in doc.sections:
        s.top_margin = Inches(1)
        s.bottom_margin = Inches(1)
        s.left_margin = Inches(1)
        s.right_margin = Inches(1)

    p_cover = doc.add_paragraph()
    p_cover.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_cover.paragraph_format.space_after = Pt(2)
    r = p_cover.add_run("KATALOG VISUAL TAMPILAN WEBSITE KUESIFY")
    r.font.name = 'Arial'
    r.font.size = Pt(16)
    r.bold = True
    r.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(16)
    r_sub = p_sub.add_run("Dokumentasi Lengkap Antarmuka Berdasarkan Route & User Flow Aplikasi\nTesting URL: http://localhost:8000 | Tim: Kai Cenat Mewing Department")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading(doc, "1. Modul Autentikasi dan Manajerial Akun", level=1)
    add_heading(doc, "Route: / (Landing Page Publik)", level=2)
    add_img_placeholder(doc, "Halaman Landing Page Utama dengan Carousel Hero Promo Banner dan CTA Login")

    add_heading(doc, "Route: /login & /register (Single-Page Auth Modal)", level=2)
    add_img_placeholder(doc, "Formulir Login dan Register Terpadu dengan Slide Animation Latar Belakang Terburam")

    add_heading(doc, "Route: /choose-avatar & /profile (Profil & Avatar Picker)", level=2)
    add_img_placeholder(doc, "Pilihan 13 Avatar Unik Kuesify dan Halaman Pengaturan Akun Profil Pengguna")

    add_heading(doc, "2. Modul Live Multiplayer Quiz (Real-time WebSockets)", level=1)
    add_heading(doc, "Route: /join (Live Join PIN & Stepper Avatar)", level=2)
    add_img_placeholder(doc, "Halaman Join Live Quiz dengan Input PIN 6-Digit & Avatar Temporary Picker")

    add_heading(doc, "Route: /live-sessions/{session}/play (Layar Host Live Quiz)", level=2)
    add_img_placeholder(doc, "Dashboard Host Live Session dengan Countage 'Soal ke X dari Y' & Intermission Podium")

    add_heading(doc, "Route: /live-sessions/{session}/play (Layar Participant Live Quiz)", level=2)
    add_img_placeholder(doc, "Antarmuka Peserta Live Quiz dengan One-Shot Lock & Real-time Feedback Benar/Salah")

    add_heading(doc, "3. Modul Creator (Guru & Evaluasi)", level=1)
    add_heading(doc, "Route: /creator/question-bank (Context-Aware Question Bank)", level=2)
    add_img_placeholder(doc, "Formulir Pembuatan Soal Context-Aware (Dropdown PG A-E, Radio TF, Isian, Essay)")

    add_heading(doc, "Route: /quizzes & /materials (AI Generator & Dokumen)", level=2)
    add_img_placeholder(doc, "Daftar Kuis Creator dan Modal AI Generator Ekstraksi Dokumen PDF/PPTX")

    add_heading(doc, "Route: /attempts (Penilaian Essay & Gradebook)", level=2)
    add_img_placeholder(doc, "Halaman Penilaian Essay Peserta oleh Creator & Tombol Kirim Review Notifikasi")

    add_heading(doc, "4. Modul Participant (Gamifikasi & Notifikasi)", level=1)
    add_heading(doc, "Route: /dashboard & /participant/badges (Gamifikasi)", level=2)
    add_img_placeholder(doc, "Dashboard Participant dengan Level Progress Bar, Streak Heatmap, dan Katalog Badge")

    add_heading(doc, "Route: /notifications (Pusat Notifikasi)", level=2)
    add_img_placeholder(doc, "Pusat Notifikasi Pengguna dengan Indikator Unread Red Badge di Sidebar Navigasi")

    add_heading(doc, "5. Modul Administrasi & Organisasi", level=1)
    add_heading(doc, "Route: /admin/members & /reports (Org Admin Dashboard)", level=2)
    add_img_placeholder(doc, "Halaman Analitik Laporan & Manajemen Anggota Organisasi (Responsif Mobile)")

    add_heading(doc, "Route: /admin & /superadmin/tenants (Super Admin Panel)", level=2)
    add_img_placeholder(doc, "Panel Kontrol Super Admin untuk Multi-Tenant & Moderasi Kuis Publik Global")

    doc.save("[TampilanWeb] INSYFEST2026-KaiCenatMewingDepartment.docx")
    print("Updated Tampilan Web DOCX successfully!")

if __name__ == "__main__":
    generate_proposal()
    generate_manualbook()
    generate_tampilanweb()
