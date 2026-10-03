import os
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, HRFlowable
)
from reportlab.pdfgen import canvas

class NumberedCanvas(canvas.Canvas):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self._saved_page_states = []

    def showPage(self):
        self._saved_page_states.append(dict(self.__dict__))
        self._startPage()

    def save(self):
        num_pages = len(self._saved_page_states)
        for state in self._saved_page_states:
            self.__dict__.update(state)
            self.draw_page_decorations(num_pages)
            super().showPage()
        super().save()

    def draw_page_decorations(self, page_count):
        self.saveState()
        
        # Header
        self.setFont("Helvetica-Bold", 8)
        self.setFillColor(colors.HexColor("#000000"))
        self.drawString(54, 805, "MANUAL BOOK — KUESIFY v1.5 (INSYFEST 2026)")
        
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#555555"))
        self.drawRightString(541, 805, "Kai Cenat Mewing Department")
        
        self.setStrokeColor(colors.HexColor("#CCCCCC"))
        self.setLineWidth(0.5)
        self.line(54, 797, 541, 797)
        
        # Footer
        self.line(54, 45, 541, 45)
        
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#555555"))
        self.drawString(54, 32, "Panduan Penggunaan & Spesifikasi Pengujian Juri")
        
        page_text = f"Halaman {self._pageNumber} dari {page_count}"
        self.drawRightString(541, 32, page_text)
        
        self.restoreState()

def build_pdf(filename):
    doc = SimpleDocTemplate(
        filename,
        pagesize=A4,
        leftMargin=54,
        rightMargin=54,
        topMargin=54,
        bottomMargin=54
    )

    styles = getSampleStyleSheet()
    
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=20,
        leading=24,
        textColor=colors.HexColor('#000000'),
        spaceAfter=8
    )

    subtitle_style = ParagraphStyle(
        'DocSubtitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=12,
        leading=16,
        textColor=colors.HexColor('#333333'),
        spaceAfter=15
    )

    h1_style = ParagraphStyle(
        'Heading1_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=13,
        leading=17,
        textColor=colors.HexColor('#000000'),
        spaceBefore=14,
        spaceAfter=8,
        keepWithNext=True
    )

    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=10.5,
        leading=14,
        textColor=colors.HexColor('#000000'),
        spaceBefore=10,
        spaceAfter=6,
        keepWithNext=True
    )

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13.5,
        textColor=colors.HexColor('#222222'),
        spaceAfter=8
    )

    bullet_style = ParagraphStyle(
        'Bullet_Custom',
        parent=body_style,
        leftIndent=15,
        firstLineIndent=-10,
        spaceAfter=4
    )

    table_header_style = ParagraphStyle(
        'TableHeader',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11,
        textColor=colors.HexColor('#FFFFFF')
    )

    table_cell_style = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=11.5,
        textColor=colors.HexColor('#222222')
    )

    table_cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=11.5,
        textColor=colors.HexColor('#000000')
    )

    story = []

    # TITLE BLOCK
    story.append(Paragraph("MANUAL BOOK & PANDUAN PENGGUNAAN APLIKASI", title_style))
    story.append(Paragraph("<b>KUESIFY: Multi-Tenant Real-Time Gamified Learning Platform (INSYFEST 2026)</b>", subtitle_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#000000'), spaceAfter=15))

    # ACCREDITATION & META
    meta_data = [
        [Paragraph("<b>Nama Tim</b>", table_cell_bold), Paragraph("Kai Cenat Mewing Department", table_cell_style)],
        [Paragraph("<b>Aplikasi</b>", table_cell_bold), Paragraph("Kuesify v1.5 (Laravel 12 + Vue 3 Inertia + Reverb WebSockets)", table_cell_style)],
        [Paragraph("<b>URL Production / Host</b>", table_cell_bold), Paragraph("http://localhost:8000 (Dapat diuji langsung oleh Dewan Juri)", table_cell_style)],
        [Paragraph("<b>Prasyarat Lingkungan</b>", table_cell_bold), Paragraph("PHP >= 8.3, MySQL >= 8.0, Node.js >= 18, Web Browser modern (Chrome/Edge/Firefox)", table_cell_style)],
    ]
    meta_table = Table(meta_data, colWidths=[130, 357])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#E2E8F0')),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 15))

    # AKUN PENGUJI / DEMO CREDENTIALS
    story.append(Paragraph("1. INFORMASI AKUN LOGIN PENGUJI (DEMO CREDENTIALS)", h1_style))
    story.append(Paragraph("Untuk mempermudah pengujian seluruh fitur aplikasi oleh Dewan Juri, berikut kredensial login yang telah disediakan:", body_style))

    cred_data = [
        [Paragraph("<b>Role Akses</b>", table_header_style), Paragraph("<b>Email Login</b>", table_header_style), Paragraph("<b>Password</b>", table_header_style), Paragraph("<b>Hak Akses Utama</b>", table_header_style)],
        [Paragraph("<b>Super Admin</b>", table_cell_bold), Paragraph("superadmin@kuesify.com", table_cell_style), Paragraph("password", table_cell_style), Paragraph("Manajemen Tenant, AI Monitoring, Moderasi Global.", table_cell_style)],
        [Paragraph("<b>Org Admin</b>", table_cell_bold), Paragraph("admin@kuesify.com", table_cell_style), Paragraph("password", table_cell_style), Paragraph("Manajemen Anggota, Group, Laporan Tenant, Setting Org.", table_cell_style)],
        [Paragraph("<b>Creator</b>", table_cell_bold), Paragraph("creator@kuesify.com", table_cell_style), Paragraph("password", table_cell_style), Paragraph("Quiz Builder, Live Session Host, Penilaian Essay, Bank Soal.", table_cell_style)],
        [Paragraph("<b>Participant</b>", table_cell_bold), Paragraph("participant@kuesify.com", table_cell_style), Paragraph("password", table_cell_style), Paragraph("Katalog Kuis, Join Live Quiz, Badges, Attempts & Notifikasi.", table_cell_style)],
    ]
    cred_table = Table(cred_data, colWidths=[90, 140, 75, 182])
    cred_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#000000')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#E2E8F0')),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
    ]))
    story.append(cred_table)
    story.append(Spacer(1, 15))

    # PANDUAN PENGGUNAAN FITUR
    story.append(Paragraph("2. PANDUAN PENGGUNAAN FITUR UTAMA APLIKASI", h1_style))

    story.append(Paragraph("<b>A. Alur Live Multiplayer Quiz (Real-Time WebSocket Engine)</b>", h2_style))
    story.append(Paragraph("1. <b>Host Sesi (Creator):</b> Login sebagai <code>creator@kuesify.com</code> → Buka menu <b>Live Quiz</b> → Klik <i>Mulai Sesi Live</i> pada kuis yang diinginkan. Layar Host akan menampilkan PIN 6-digit dan QR Code.", bullet_style))
    story.append(Paragraph("2. <b>Join Peserta (Participant):</b> Buka halaman <code>/join</code> → Masukkan PIN 6-digit → Pilih Avatar Karakter (13 pilihan) & Alias → Klik <i>Gabung Ruang Kuis</i>.", bullet_style))
    story.append(Paragraph("3. <b>Peluncuran Soal:</b> Host klik <i>Mulai Kuis</i>. Layar Host akan menampilkan status <i>Soal X dari Y</i>, timer hitung mundur, serta pilihan jawaban. Layar Peserta otomatis me-render tombol pilihan jawaban realtime via Reverb WebSockets.", bullet_style))
    story.append(Paragraph("4. <b>Penguncian Jawaban:</b> Begitu peserta menekan salah satu pilihan, jawaban langsung terkunci (one-shot lock). Setelah waktu habis, peserta melihat umpan balik (Benar/Salah, +XP) dan Host melihat statistik jawaban.", bullet_style))
    story.append(Paragraph("5. <b>Intermission Podium Top 3:</b> Host klik <i>Tampilkan Podium Sementara</i>. Layar beralih ke podium animasi 3 detik (dengan countdown lock 3... 2... 1...) sebelum dapat melanjutkan ke soal berikutnya.", bullet_style))

    story.append(Paragraph("<b>B. Alur Penilaian Essay & Notifikasi Dua Arah</b>", h2_style))
    story.append(Paragraph("1. Peserta menyelesaikan kuis mandiri yang memiliki soal tipe Essay.", bullet_style))
    story.append(Paragraph("2. Setelah submit, status attempt berubah menjadi <code>pending_review</code> dan sistem mengirimkan notifikasi instan ke Creator kuis.", bullet_style))
    story.append(Paragraph("3. Di sidebar Creator, muncul badge indikator **Notifikasi** belum dibaca. Klik notifikasi tersebut untuk langsung menuju ke halaman <b>Penilaian Essay</b>.", bullet_style))
    story.append(Paragraph("4. Creator memasukkan poin nilai dan umpan balik → Simpan. Peserta menerima notifikasi balasan bahwa nilai essay-nya telah dirilis.", bullet_style))

    story.append(Paragraph("<b>C. Pengelolaan Question Bank Context-Aware</b>", h2_style))
    story.append(Paragraph("1. Creator masuk ke menu <b>Creator Bank</b> (`/creator/question-bank`).", bullet_style))
    story.append(Paragraph("2. Pilih Tipe Soal (Pilihan Ganda, True/False, Isian, Essay). Form otomatis menyesuaikan:", bullet_style))
    story.append(Paragraph("   - <i>Pilihan Ganda:</i> Input opsi A, B, C, D, E + Dropdown Jawaban Benar berbasis opsi.", bullet_style))
    story.append(Paragraph("   - <i>True/False:</i> Tombol radio interaktif True (Benar) / False (Salah).", bullet_style))
    story.append(Paragraph("   - <i>Essay:</i> Info box otomatis bahwa penilaian dilakukan secara manual.", bullet_style))

    story.append(Spacer(1, 15))

    # CARA MENJALANKAN (DEVELOPMENT SETUP)
    story.append(Paragraph("3. INSTRUKSI MENJALANKAN LOKAL (DEVELOPMENT SETUP)", h1_style))
    story.append(Paragraph("Untuk menjalankan proyek dari awal pada lingkungan lokal:", body_style))
    story.append(Paragraph("<code>1. git clone & cd C:/dev/github/Kuesify</code>", bullet_style))
    story.append(Paragraph("<code>2. composer install && npm install</code>", bullet_style))
    story.append(Paragraph("<code>3. cp .env.example .env && php artisan key:generate</code>", bullet_style))
    story.append(Paragraph("<code>4. php artisan migrate:fresh --seed</code>", bullet_style))
    story.append(Paragraph("<code>5. php artisan reverb:start (Jalankan WebSocket Server)</code>", bullet_style))
    story.append(Paragraph("<code>6. npm run dev & php artisan serve</code>", bullet_style))

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"Manual Book successfully generated at: {filename}")

if __name__ == '__main__':
    output_path = os.path.join(os.getcwd(), "[ManualBook] INSYFEST2026-KaiCenatMewingDepartment.pdf")
    build_pdf(output_path)
