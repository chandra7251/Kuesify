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
# BUILD PROPOSAL (Khusus INSYFEST 2026 Web Development Competition)
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
    r = p_cover.add_run("PROPOSAL DESAIN & INOVASI APLIKASI WEB\nINSYFEST 2026 WEB DEVELOPMENT COMPETITION")
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
    print("Updated Proposal DOCX with INSYFEST Header successfully!")

if __name__ == "__main__":
    generate_proposal()
