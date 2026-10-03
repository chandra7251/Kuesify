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
        self.drawString(54, 805, "DOKUMEN TAMPILAN WEBSITE — KUESIFY (INSYFEST 2026)")
        
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
        self.drawString(54, 32, "Kuesify — Domain & Visual Interface Showcase")
        
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

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9,
        leading=13.5,
        textColor=colors.HexColor('#222222'),
        spaceAfter=8
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

    placeholder_box_style = ParagraphStyle(
        'PlaceholderBox',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9.5,
        leading=14,
        textColor=colors.HexColor('#0F172A'),
        alignment=1
    )

    story = []

    # TITLE BLOCK
    story.append(Paragraph("DOKUMEN TAMPILAN WEBSITE & DOMAIN LINK", title_style))
    story.append(Paragraph("<b>KUESIFY: Multi-Tenant Real-Time Gamified Learning Platform</b>", subtitle_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#000000'), spaceAfter=15))

    # META & LINK DOMAIN
    meta_data = [
        [Paragraph("<b>Nama Tim</b>", table_cell_bold), Paragraph("Kai Cenat Mewing Department", table_cell_style)],
        [Paragraph("<b>Link Domain Website (Hosting)</b>", table_cell_bold), Paragraph("<b>http://localhost:8000</b> (Ready for Live Evaluation by Judges)", table_cell_style)],
        [Paragraph("<b>Framework Backend & Frontend</b>", table_cell_bold), Paragraph("Laravel 12 (PHP 8.3) + Vue 3 Inertia + Laravel Reverb WebSockets", table_cell_style)],
        [Paragraph("<b>Status Hosting</b>", table_cell_bold), Paragraph("Active & Operational (Realtime Broadcasting Enabled)", table_cell_style)],
    ]
    meta_table = Table(meta_data, colWidths=[150, 337])
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

    story.append(Paragraph("DESAIN ANTARMUKA & TANGKAPAN LAYAR INTERFASE UTAMA", h1_style))
    story.append(Paragraph("Berikut adalah dokumentasi visual antarmuka utama platform Kuesify yang telah dibangun dan disesuaikan dengan standar kompetisi:", body_style))

    screenshots = [
        ("1. Halaman Utama / Landing Page & Auth Slide", "Tampilan awal platform Kuesify dengan slide animasi Auth terpadu (Login/Register) dan visual hero berbasis latar blur modern."),
        ("2. Real-Time Live Quiz Host Lobby & Soal Counter (Soal X dari Y)", "Tampilan Host saat memimpin kuis live. Menampilkan PIN 6-digit, QR Code, badge jumlah soal realtime (Soal ke X dari Y), serta interaksi kontrol kuis."),
        ("3. Tampilan Live Quiz Participant & Jawaban One-Shot Lock", "Antarmuka Peserta saat menjawab kuis live. Pilihan jawaban dikunci satu kali, disertai animasi umpan balik Benar/Salah (+XP) dan indikator status terkunci."),
        ("4. Intermission Podium Top 3 & 3-Second Lock", "Tampilan podium 3 besar saat jeda antar soal dengan animasi GSAP dan hitung mundur 3 detik sebelum dapat melanjutkan soal berikutnya."),
        ("5. Question Bank Context-Aware Creator Workspace", "Halaman pembuatan bank soal oleh Creator. Input form otomatis beradaptasi dengan tipe soal (Pilihan Ganda opsi A-E, True/False radio, Isian, Essay)."),
        ("6. Penilaian Essay (Gradebook) & Sidebar Notifikasi Unread", "Halaman review jawaban essay oleh Creator serta indikator badge merah notifikasi belum dibaca di sidebar navigasi."),
        ("7. Dashboard Analytics & Multi-Tenant Reports (Mobile Responsive)", "Halaman laporan analitik tenant dan ringkasan eksekutif yang responsif penuh di perangkat seluler maupun desktop."),
    ]

    for title_text, desc_text in screenshots:
        box_content = [
            [Paragraph(f"<b>[Foto Tangkapan Layar: {title_text}]</b>", placeholder_box_style)],
            [Paragraph(desc_text, ParagraphStyle('SubText', parent=styles['Normal'], fontSize=8, textColor=colors.HexColor('#475569'), alignment=1))]
        ]
        box_table = Table(box_content, colWidths=[487])
        box_table.setStyle(TableStyle([
            ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F1F5F9')),
            ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#94A3B8')),
            ('TOPPADDING', (0,0), (-1,-1), 8),
            ('BOTTOMPADDING', (0,0), (-1,-1), 8),
            ('LEFTPADDING', (0,0), (-1,-1), 10),
            ('RIGHTPADDING', (0,0), (-1,-1), 10),
            ('ALIGN', (0,0), (-1,-1), 'CENTER'),
        ]))
        story.append(box_table)
        story.append(Spacer(1, 10))

    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"Tampilan Website PDF successfully generated at: {filename}")

if __name__ == '__main__':
    output_path = os.path.join(os.getcwd(), "[TampilanWeb] INSYFEST2026-KaiCenatMewingDepartment.pdf")
    build_pdf(output_path)
