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
# BUILD PROPOSAL (INSYFEST 2026 - ABSOLUTE ZERO DASHES)
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
    r = p_cover.add_run("PROPOSAL DESAIN DAN INOVASI APLIKASI WEB\nINSYFEST 2026 WEB DEVELOPMENT COMPETITION")
    r.font.name = 'Arial'
    r.font.size = Pt(16)
    r.bold = True
    r.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(16)
    r_sub = p_sub.add_run("KUESIFY: Platform Asesmen Edukasi Interaktif dan Multiplayer Live Quiz Berbasis AI dan Real Time WebSockets\nNama Tim: Kai Cenat Mewing Department (2026)")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading(doc, "1. Latar Belakang dan Urgensi Inovasi", level=1)
    add_p(doc, "Pendidikan era digital mengharuskan guru untuk tidak hanya menyampaikan materi, tetapi juga mengukur tingkat pemahaman siswa secara langsung dan personal. Riset Gallup dan Walton Foundation mencatat bahwa pengajar menghabiskan waktu rata rata 5,9 jam setiap minggu hanya untuk membuat bahan ajar dan soal ujian. Di sisi lain, platform populer seperti Kahoot membatasi versi gratisnya hingga 10 peserta per sesi, yang membuat sekolah harus membayar biaya berlangganan berkisar USD 36 hingga 228 setiap tahunnya. Google Forms juga memiliki keterbatasan karena tidak memiliki elemen interaktif, sehingga siswa mudah merasa jenuh saat mengerjakan evaluasi.")
    add_p(doc, "Kuesify menyelesaikan permasalahan ini dengan menyediakan platform gratis tanpa batasan jumlah peserta dalam satu kelas. Aplikasi ini menggunakan teknologi real time WebSockets serta pemroses AI untuk mengekstrak dokumen materi pelajaran secara otomatis.")

    add_heading(doc, "2. Tujuan dan Manfaat", level=1)
    add_bullet(doc, "Proses belajar menjadi lebih menarik melalui kompetisi live multiplayer, papan peringkat interaktif, serta sistem perolehan XP dan Badge.", bold_prefix="Bagi Peserta Didik: ")
    add_bullet(doc, "Waktu pembuatan kuis berkurang drastis dengan bantuan Gemini AI Parser, serta kemudahan dalam mengoreksi jawaban essay secara terpusat.", bold_prefix="Bagi Guru atau Pengajar: ")
    add_bullet(doc, "Menggunakan arsitektur Multi Tenant SaaS dengan pemisahan data antar sekolah, efisiensi server tanpa biaya kuota pihak ketiga, serta laporan analitik terpadu.", bold_prefix="Bagi Institusi Pendidikan: ")

    add_heading(doc, "3. Inovasi Penggunaan Teknologi Digital dan Keunggulan Stack", level=1)
    add_heading(doc, "3.1 Konsep dan Kebaruan Inovasi", level=2)
    add_p(doc, "Kuesify menggabungkan empat bagian utama yaitu Real Time Engine, Generative AI Parsing, Gamification Loop, dan Multi Tenant Architecture ke dalam satu sistem yang saling terhubung.")

    add_heading(doc, "3.2 Arsitektur dan Detail Keunggulan Tech Stack", level=2)
    add_bullet(doc, "Menggunakan fitur Strict Typing, Service Layer Pattern, Eloquent ORM, dan pengujian Pest PHP sebanyak 127 pengujian yang lulus 100 persen untuk menjaga keandalan sistem.", bold_prefix="Laravel 12 dan PHP 8.3 (Backend Core): ")
    add_bullet(doc, "Arsitektur Single Page Application yang berjalan tanpa perlu reload halaman, sehingga perpindahan antar antarmuka terasa sangat cepat.", bold_prefix="Vue 3 dan Inertia.js (Frontend Engine): ")
    add_bullet(doc, "Server WebSocket bawaan yang berjalan langsung di server lokal tanpa tergantung pihak ketiga seperti Pusher atau Ably, menghasilkan komunikasi data yang cepat tanpa biaya kuota.", bold_prefix="Laravel Reverb (Real Time WebSockets): ")
    add_bullet(doc, "Membaca file materi seperti PDF atau PPTX secara otomatis untuk dijadikan draf soal kuis lengkap dengan opsi jawaban dan penjelasan.", bold_prefix="Google Gemini AI (Document Parser): ")

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
        ["Real Time WebSocket Sync", "Ya (Terbatas)", "Partial", "Tidak Ada", "Ya (Laravel Reverb Kurang Dari Satu Milidetik)"],
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
    add_p(doc, "Kuesify menerapkan Formative Feedback Loop, di mana peserta didik langsung mendapatkan hasil apakah jawabannya Benar (dengan tambahan XP) atau Salah (disertai kunci jawaban dan penjelasan) sesaat setelah memilih jawaban di layar. Metode ini membantu siswa memahami konsep lebih cepat sebelum melanjutkan ke materi berikutnya.")

    add_heading(doc, "5. Dampak Pembelajaran dan Rencana Keberlanjutan", level=1)
    add_p(doc, "Kuesify mendukung kebiasaan belajar mandiri melalui fitur Daily Streak Heatmap yang mencatat keaktifan belajar harian. Dari segi penggunaan institusi, Kuesify siap digunakan oleh sekolah dan perguruan tinggi melalui arsitektur Multi Tenant SaaS.")

    doc.save("[Proposal] INSYFEST2026-KaiCenatMewingDepartment.docx")
    print("Proposal DOCX generated!")

# ==============================================================================
# BUILD MANUAL BOOK (ABSOLUTE ZERO DASHES)
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
    r = p_cover.add_run("MANUAL BOOK DAN PANDUAN PENGGUNAAN APLIKASI KUESIFY")
    r.font.name = 'Arial'
    r.font.size = Pt(16)
    r.bold = True
    r.font.color.rgb = RGBColor(0, 0, 0)

    p_sub = doc.add_paragraph()
    p_sub.alignment = WD_ALIGN_PARAGRAPH.CENTER
    p_sub.paragraph_format.space_after = Pt(16)
    r_sub = p_sub.add_run("Dokumentasi Operasional Empat Role Pengguna Lengkap dengan Visualisasi Antarmuka\nNama Tim: Kai Cenat Mewing Department (2026)")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading(doc, "1. Akun Pengujian dan Kredensial Demo Juri", level=1)
    add_p(doc, "Berikut adalah daftar akun pengujian yang disiapkan di database lokal untuk dicoba oleh dewan juri:")

    table = doc.add_table(rows=5, cols=4)
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    headers = ["Role Pengguna", "Email Credentials", "Password", "Fungsi dan Hak Akses Utama"]
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
        ["Super Admin", "superadmin@kuesify.com", "password", "Pengaturan tenant global, moderasi kuis publik, dan pemantauan AI."],
        ["Org Admin", "admin@kuesify.com", "password", "Pengelolaan anggota, grup kelas, dan laporan hasil belajar."],
        ["Creator (Guru)", "creator@kuesify.com", "password", "Pembuatan kuis AI dan manual, Bank Soal, Host Live, dan koreksi essay."],
        ["Participant (Siswa)", "participant@kuesify.com", "password", "Masuk sesi kuis live via PIN, kuis mandiri, klaim badge, dan notifikasi."]
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
    add_bullet(doc, "Eksekusi perintah composer install dan npm install pada direktori proyek Kuesify.", bold_prefix="Langkah 1 (Dependencies): ")
    add_bullet(doc, "Pastikan file .env berisi konfigurasi BROADCAST_CONNECTION=reverb dan QUEUE_CONNECTION=sync.", bold_prefix="Langkah 2 (Environment): ")
    add_bullet(doc, "Jalankan php artisan migrate:fresh opsional seed untuk membuat skema tabel dan mengisi data akun awal.", bold_prefix="Langkah 3 (Database Seeding): ")
    add_bullet(doc, "Buka terminal baru lalu jalankan php artisan reverb:start untuk mengaktifkan server WebSocket.", bold_prefix="Langkah 4 (WebSocket Server): ")
    add_bullet(doc, "Jalankan php artisan serve dan npm run dev kemudian buka link http://localhost:8000.", bold_prefix="Langkah 5 (Jalankan App): ")

    add_heading(doc, "3. Panduan Operasional Lengkap Empat Role Pengguna", level=1)

    # ROLE 1: PARTICIPANT
    add_heading(doc, "3.1 Role Participant (Siswa)", level=2)
    add_p(doc, "1. Buka halaman /join, masukkan 6 angka PIN kuis live yang diberikan oleh guru.")
    add_p(doc, "2. Pilih avatar karakter dari 13 pilihan avatar yang ada. Apabila siswa sudah dalam kondisi login, nama alias diisi otomatis oleh sistem.")
    add_img_placeholder(doc, "Tampilan Layar Join Live Quiz untuk Peserta (Input PIN dan Pemilihan Avatar)")

    add_p(doc, "3. Ketika kuis dimulai, siswa melihat soal dan opsi jawaban. Mengklik salah satu opsi akan langsung mengunci jawaban tersebut.")
    add_p(doc, "4. Peserta menerima hasil langsung: tampilan hijau jika jawaban Benar (dengan poin XP) atau tampilan merah jika Salah (disertai pembukaan kunci jawaban).")
    add_img_placeholder(doc, "Tampilan Layar Peserta Saat Mengunci Jawaban dan Menerima Hasil Benar atau Salah")

    add_p(doc, "5. Siswa dapat memantau perolehan XP, level, koleksi badge, serta melihat pesan masuk pada halaman Notifikasi.")
    add_img_placeholder(doc, "Dashboard Gamifikasi Peserta (Level Progress Bar, Badge Grid, dan Notifikasi)")

    # ROLE 2: CREATOR
    add_heading(doc, "3.2 Role Creator (Guru)", level=2)
    add_p(doc, "1. Pembuatan Soal di /creator/question/bank: Guru memilih jenis soal seperti Pilihan Ganda A hingga E, True/False, Isian, atau Essay di mana formulir akan menyesuaikan bentuk isian secara otomatis.")
    add_img_placeholder(doc, "Tampilan Formulir Creator Question Bank Berdasarkan Jenis Soal")

    add_p(doc, "2. Memimpin Sesi Live Quiz: Host dapat melihat indikator Soal ke X dari Y, menghitung mundur waktu otomatis, serta menampilkan animasi papan peringkat Top 3.")
    add_img_placeholder(doc, "Tampilan Dashboard Host Live Session (Countage Soal 'Soal ke X dari Y' dan Layar Intermission Podium)")

    add_p(doc, "3. Koreksi Jawaban Essay di /attempts: Guru memeriksa jawaban essay siswa, menginput nilai secara manual, dan sistem akan mengirimkan pemberitahuan otomatis ke akun siswa.")
    add_img_placeholder(doc, "Tampilan Halaman Penilaian Essay Peserta oleh Creator dan Pengiriman Notifikasi")

    # ROLE 3: ORGANIZATION ADMIN
    add_heading(doc, "3.3 Role Organization Admin (Pengelola Sekolah)", level=2)
    add_p(doc, "1. Mengatur daftar anggota sekolah dan grup kelas pada halaman /admin/members.")
    add_p(doc, "2. Melihat laporan hasil belajar siswa di /reports yang telah disesuaikan agar nyaman dibuka dari HP.")
    add_img_placeholder(doc, "Tampilan Halaman Laporan Analitik dan Manajemen Anggota Organisasi (Responsif Mobile)")

    # ROLE 4: SUPER ADMIN
    add_heading(doc, "3.4 Role Super Admin (Pengelola Platform)", level=2)
    add_p(doc, "1. Mengawasi statistik platform global dan manajemen sekolah di /superadmin/tenants.")
    add_p(doc, "2. Memeriksa kuis publik yang diajukan oleh guru di /admin/moderation untuk disetujui atau ditolak.")
    add_img_placeholder(doc, "Tampilan Panel Kontrol Super Admin dan Moderasi Kuis Publik Global")

    doc.save("[ManualBook] INSYFEST2026-KaiCenatMewingDepartment.docx")
    print("Manual Book DOCX generated!")

# ==============================================================================
# BUILD TAMPILAN WEB (ABSOLUTE ZERO DASHES)
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
    r_sub = p_sub.add_run("Dokumentasi Lengkap Antarmuka Berdasarkan Route dan User Flow Aplikasi\nTesting URL: http://localhost:8000 | Tim: Kai Cenat Mewing Department")
    r_sub.font.name = 'Arial'
    r_sub.font.size = Pt(12)
    r_sub.bold = True
    r_sub.font.color.rgb = RGBColor(51, 65, 85)

    add_heading(doc, "1. Modul Autentikasi dan Pengaturan Akun", level=1)
    add_heading(doc, "Route: / (Landing Page Publik)", level=2)
    add_img_placeholder(doc, "Halaman Landing Page Utama dengan Carousel Hero Promo Banner dan Tombol Login")

    add_heading(doc, "Route: /login dan /register (Single Page Auth Modal)", level=2)
    add_img_placeholder(doc, "Formulir Login dan Register Terpadu dengan Slide Animation dan Latar Blur")

    add_heading(doc, "Route: /choose/avatar dan /profile (Profil dan Pemilihan Avatar)", level=2)
    add_img_placeholder(doc, "Pilihan 13 Avatar Unik Kuesify dan Halaman Pengaturan Akun Profil Pengguna")

    add_heading(doc, "2. Modul Live Multiplayer Quiz (Real Time WebSockets)", level=1)
    add_heading(doc, "Route: /join (Live Join PIN dan Avatar Character)", level=2)
    add_img_placeholder(doc, "Halaman Join Live Quiz dengan Input PIN 6 Angka dan Pemilihan Character Avatar")

    add_heading(doc, "Route: /live/sessions/{session}/play (Layar Host Live Quiz)", level=2)
    add_img_placeholder(doc, "Dashboard Host Live Session dengan Hitungan Soal ke X dari Y dan Papan Peringkat Top 3")

    add_heading(doc, "Route: /live/sessions/{session}/play (Layar Participant Live Quiz)", level=2)
    add_img_placeholder(doc, "Antarmuka Peserta Live Quiz dengan Penguncian Tombol dan Feedback Benar atau Salah")

    add_heading(doc, "3. Modul Creator (Guru dan Evaluasi)", level=1)
    add_heading(doc, "Route: /creator/question/bank (Form Pembuatan Soal)", level=2)
    add_img_placeholder(doc, "Formulir Pembuatan Soal Adaptif (Dropdown PG A hingga E, Radio TF, Isian, Essay)")

    add_heading(doc, "Route: /quizzes dan /materials (AI Generator dan Dokumen)", level=2)
    add_img_placeholder(doc, "Daftar Kuis Creator dan Modal AI Generator Ekstraksi Dokumen PDF atau PPTX")

    add_heading(doc, "Route: /attempts (Penilaian Essay dan Koreksi)", level=2)
    add_img_placeholder(doc, "Halaman Penilaian Essay Peserta oleh Creator dan Tombol Kirim Review Notifikasi")

    add_heading(doc, "4. Modul Participant (Gamifikasi dan Notifikasi)", level=1)
    add_heading(doc, "Route: /dashboard dan /participant/badges (Gamifikasi)", level=2)
    add_img_placeholder(doc, "Dashboard Participant dengan Level Progress Bar, Streak Heatmap, dan Katalog Badge")

    add_heading(doc, "Route: /notifications (Pusat Notifikasi)", level=2)
    add_img_placeholder(doc, "Pusat Notifikasi Pengguna dengan Indikator Unread Red Badge di Sidebar Navigasi")

    add_heading(doc, "5. Modul Administrasi dan Organisasi", level=1)
    add_heading(doc, "Route: /admin/members dan /reports (Org Admin Dashboard)", level=2)
    add_img_placeholder(doc, "Halaman Analitik Laporan dan Manajemen Anggota Organisasi (Responsif Mobile)")

    add_heading(doc, "Route: /admin dan /superadmin/tenants (Super Admin Panel)", level=2)
    add_img_placeholder(doc, "Panel Kontrol Super Admin untuk Multi Tenant dan Moderasi Kuis Publik Global")

    doc.save("[TampilanWeb] INSYFEST2026-KaiCenatMewingDepartment.docx")
    print("Tampilan Web DOCX generated!")

if __name__ == "__main__":
    generate_proposal()
    generate_manualbook()
    generate_tampilanweb()
