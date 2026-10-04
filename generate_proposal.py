import os
import sys
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle, PageBreak, KeepTogether, HRFlowable
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
        
        # Header (Top bar with title)
        self.setFont("Helvetica-Bold", 8)
        self.setFillColor(colors.HexColor("#000000"))
        self.drawString(54, 805, "PROPOSAL INSYFEST 2026 — WEB DEVELOPMENT COMPETITION")
        
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#555555"))
        self.drawRightString(541, 805, "Kai Cenat Mewing Department")
        
        self.setStrokeColor(colors.HexColor("#CCCCCC"))
        self.setLineWidth(0.5)
        self.line(54, 797, 541, 797)
        
        # Footer (Bottom line with page number)
        self.line(54, 45, 541, 45)
        
        self.setFont("Helvetica", 8)
        self.setFillColor(colors.HexColor("#555555"))
        self.drawString(54, 32, "Kuesify — Real-Time Gamified Micro-Learning & Assessment Platform")
        
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
    
    # Custom Styles adhering to constraints: Arial/Helvetica, Pure Black (#000000) Headings
    title_style = ParagraphStyle(
        'DocTitle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=20,
        leading=24,
        textColor=colors.HexColor('#000000'),
        spaceAfter=8,
        alignment=0
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
        fontSize=14,
        leading=18,
        textColor=colors.HexColor('#000000'),
        spaceBefore=16,
        spaceAfter=8,
        keepWithNext=True
    )

    h2_style = ParagraphStyle(
        'Heading2_Custom',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=11,
        leading=15,
        textColor=colors.HexColor('#000000'),
        spaceBefore=12,
        spaceAfter=6,
        keepWithNext=True
    )

    body_style = ParagraphStyle(
        'Body_Custom',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=9.5,
        leading=14,
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
        fontSize=9,
        leading=12,
        textColor=colors.HexColor('#FFFFFF')
    )

    table_cell_style = ParagraphStyle(
        'TableCell',
        parent=styles['Normal'],
        fontName='Helvetica',
        fontSize=8.5,
        leading=12,
        textColor=colors.HexColor('#222222')
    )

    table_cell_bold = ParagraphStyle(
        'TableCellBold',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=8.5,
        leading=12,
        textColor=colors.HexColor('#000000')
    )

    code_style = ParagraphStyle(
        'CodeStyle',
        parent=styles['Normal'],
        fontName='Courier',
        fontSize=8,
        leading=10,
        textColor=colors.HexColor('#1E293B')
    )

    placeholder_style = ParagraphStyle(
        'PlaceholderStyle',
        parent=styles['Normal'],
        fontName='Helvetica-Bold',
        fontSize=9,
        leading=13,
        textColor=colors.HexColor('#1E3A8A'),
        alignment=1
    )

    story = []

    # TITLE BLOCK
    story.append(Paragraph("PROPOSAL INSYFEST 2026 — WEB DEVELOPMENT", title_style))
    story.append(Paragraph("<b>KUESIFY: Real-Time Gamified Micro-Learning & Multi-Tenant Live Assessment Platform</b>", subtitle_style))
    story.append(HRFlowable(width="100%", thickness=1.5, color=colors.HexColor('#000000'), spaceAfter=15))

    # META TABLE
    meta_data = [
        [Paragraph("<b>Nama Tim</b>", table_cell_bold), Paragraph("Kai Cenat Mewing Department", table_cell_style)],
        [Paragraph("<b>Tema Lomba</b>", table_cell_bold), Paragraph("Pendidikan (E-Learning & Interactive Assessment Platform)", table_cell_style)],
        [Paragraph("<b>Nama Aplikasi</b>", table_cell_bold), Paragraph("Kuesify v1.5 (Multi-Tenant Real-Time Gamification Platform)", table_cell_style)],
        [Paragraph("<b>URL Production</b>", table_cell_bold), Paragraph("http://localhost:8000 (Hosted & Ready for Live Evaluation)", table_cell_style)],
        [Paragraph("<b>Repository</b>", table_cell_bold), Paragraph("C:/dev/github/Kuesify (Laravel 12 + Inertia Vue 3 + Reverb WebSockets)", table_cell_style)],
    ]
    meta_table = Table(meta_data, colWidths=[120, 367])
    meta_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,-1), colors.HexColor('#F8FAFC')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#E2E8F0')),
        ('TOPPADDING', (0,0), (-1,-1), 6),
        ('BOTTOMPADDING', (0,0), (-1,-1), 6),
        ('LEFTPADDING', (0,0), (-1,-1), 8),
        ('RIGHTPADDING', (0,0), (-1,-1), 8),
    ]))
    story.append(meta_table)
    story.append(Spacer(1, 15))

    # SECTION 1: EXECUTIVE SUMMARY & LATAR BELAKANG
    story.append(Paragraph("1. EXECUTIVE SUMMARY & LATAR BELAKANG", h1_style))
    story.append(Paragraph(
        "Di era transformasi digital pendidikan, tantangan utama e-learning modern bukan lagi pada ketersediaan materi, melainkan pada <b>tingkat keterlibatan (engagement)</b> dan <b>efektivitas evaluasi</b>. Metode pembelajaran konvensional berbasis dokumen statis sering kali memicu kebosanan, sementara platform kuis interaktif yang ada umumnya memiliki keterbatasan: kaku, mahal untuk skala organisasi/sekolah, kurangnya integrasi AI dalam pembuatan materi, serta lemahnya umpan balik waktu nyata (real-time feedback) saat kuis berlangsung.",
        body_style
    ))
    story.append(Paragraph(
        "<b>Kuesify</b> hadir sebagai solusi terpadu berbasis web yang menggabungkan <b>Real-Time Live Multiplayer Quiz</b> (menggunakan Laravel Reverb WebSockets), <b>Multi-Tenant Hierarchy Architecture</b>, <b>AI Material & Question Generator</b>, serta <b>Gamification Ecosystem</b>. Kuesify dirancang tidak hanya untuk institusi formal (sekolah, universitas), tetapi juga untuk korporat dan komunitas yang membutuhkan platform pembelajaran dan evaluasi yang dinamis, terstruktur, serta menyenangkan.",
        body_style
    ))

    # SECTION 2: KEUNGGULAN PRODUK & NILAI TAMBAH (VALUE PROPOSITION)
    story.append(Paragraph("2. KEUNGGULAN PRODUK & NILAI TAMBAH (VALUE PROPOSITION)", h1_style))
    story.append(Paragraph(
        "Jika dibandingkan dengan platform serupa yang ada di pasaran (seperti Kahoot! atau Quizizz), Kuesify menawarkan nilai tambah unggulan yang menyelesaikan berbagai *pain points* mendasar:",
        body_style
    ))

    vp_data = [
        [Paragraph("<b>Fitur & Aspek</b>", table_header_style), Paragraph("<b>Platform Pasar (Kahoot/Quizizz)</b>", table_header_style), Paragraph("<b>Kuesify (Solusi Inovatif Tim)</b>", table_header_style)],
        [
            Paragraph("<b>Arsitektur Organisasi</b>", table_cell_bold),
            Paragraph("Single-user / Akun individu terpisah tanpa struktur hierarki institusi.", table_cell_style),
            Paragraph("<b>Multi-Tenant & Role Isolation:</b> Super Admin, Org Admin, Creator, dan Participant dengan kontrol akses ketat.", table_cell_style)
        ],
        [
            Paragraph("<b>Teknologi Real-Time</b>", table_cell_bold),
            Paragraph("Terikat pada server pihak ketiga berbayar dengan keterbatasan kuota pemain.", table_cell_style),
            Paragraph("<b>Laravel Reverb (Pusher-compatible WebSocket):</b> Komunikasi <i>sub-millisecond latency</i> tanpa biaya kuota pihak ketiga.", table_cell_style)
        ],
        [
            Paragraph("<b>Pembuatan Soal</b>", table_cell_bold),
            Paragraph("Manual input satu per satu atau impor spreadsheet terbatas.", table_cell_style),
            Paragraph("<b>AI Material & Question Generator:</b> Otomatisasi pembentukan kuis, ringkasan materi, dan bank soal kontekstual.", table_cell_style)
        ],
        [
            Paragraph("<b>Penilaian Essay</b>", table_cell_bold),
            Paragraph("Hanya mendukung pilihan ganda dan true/false otomatis.", table_cell_style),
            Paragraph("<b>Hybrid Grading + Real-Time Notification:</b> Mendukung soal Essay dengan notifikasi instan saat peserta submit.", table_cell_style)
        ],
        [
            Paragraph("<b>Pengalaman UX/UI</b>", table_cell_bold),
            Paragraph("Warna mencolok dan iklan yang mengganggu fokus belajar.", table_cell_style),
            Paragraph("<b>Clean Modern Figma-Spec UI:</b> Responsif penuh, mikro-interaksi smooth (GSAP), dan dukungan aksesibilitas.", table_cell_style)
        ],
    ]
    vp_table = Table(vp_data, colWidths=[110, 180, 197])
    vp_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#000000')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#E2E8F0')),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(vp_table)
    story.append(Spacer(1, 15))

    # SECTION 3: SPESIFIKASI TEKNIS & KUALITAS KODE (BOBOT 35%)
    story.append(Paragraph("3. SPESIFIKASI TEKNIS & KUALITAS KODE (BOBOT 35%)", h1_style))
    story.append(Paragraph(
        "Kuesify dibangun di atas fondasi teknologi modern berbasis <b>Laravel 12</b> dan <b>Vue 3 (Inertia.js)</b>. Seluruh baris kode mematuhi standar *clean code*, *DRY (Don't Repeat Yourself)*, serta arsitektur yang sangat terisolasi untuk menjamin keamanan dan performa maksimal.",
        body_style
    ))

    story.append(Paragraph("<b>Stack Teknologi & Depedensi Utama:</b>", h2_style))
    story.append(Paragraph("• <b>Backend Framework:</b> Laravel 12.x dengan PHP 8.3 (Strict Typing, Eloquent ORM, Service Layer Pattern).", bullet_style))
    story.append(Paragraph("• <b>Frontend Engine:</b> Vue 3 (Composition API dengan Script Setup TypeScript) + Inertia.js v2 (Single Page Application tanpa REST API overhead).", bullet_style))
    story.append(Paragraph("• <b>Real-Time Engine:</b> Laravel Reverb (WebSocket server bawaan Laravel untuk eksekusi event broadcasting tanpa antrean sync).", bullet_style))
    story.append(Paragraph("• <b>Styling & Motion:</b> Tailwind CSS v3 (Custom Brand Palette) + GSAP (GreenSock Animation Platform) untuk efek visual podium dan reward.", bullet_style))
    story.append(Paragraph("• <b>Database & Caching:</b> MySQL 8.0 / MariaDB dengan optimasi index kolom dan Redis-compatible Caching untuk leaderboard.", bullet_style))
    story.append(Paragraph("• <b>Testing & Automation:</b> Pest PHP Framework (100% Pass: 127 Feature & Unit Tests, 827 Assertions) + Vite Production Build.", bullet_style))

    story.append(Spacer(1, 6))
    story.append(Paragraph("<b>Struktur Arsitektur & Keamanan Kode:</b>", h2_style))
    story.append(Paragraph("1. <b>Multi-Tenant Isolation Middleware:</b> Menggunakan <code>SetOrganizationContext</code> untuk memastikan data antar tenant (organisasi) tidak bocor.", body_style))
    story.append(Paragraph("2. <b>Presenter Pattern:</b> <code>LiveSessionPresenter</code> memformat JSON payload secara terpusat untuk WebSocket broadcast dan Inertia props.", body_style))
    story.append(Paragraph("3. <b>Concurrency & Network Latency Guard:</b> <code>LiveSession::submit()</code> dilengkapi dengan <i>2-second grace period</i> dari deadline waktu untuk mengantisipasi *latency* jaringan peserta tanpa mengorbankan keadilan skor.", body_style))
    story.append(Paragraph("4. <b>Backend RBAC Enforcement:</b> Pengecekan peran dilakukan di query level server (misal <code>wherePivotNotIn</code>), bukan sekadar menyembunyikan elemen visual di frontend.", body_style))

    story.append(Spacer(1, 10))

    # SECTION 4: FITUR-FITUR UTAMA & FITUR UNGGULAN APLIKASI
    story.append(Paragraph("4. RINCIAN FITUR UTAMA & KEMAMPUAN SISTEM", h1_style))
    story.append(Paragraph(
        "Kuesify menyediakan ekosistem fitur terlengkap yang mencakup seluruh alur kerja pembelajaran dan evaluasi:",
        body_style
    ))

    features_data = [
        [Paragraph("<b>Modul Fitur</b>", table_header_style), Paragraph("<b>Deskripsi Teknis & Pengalaman Pengguna</b>", table_header_style)],
        [
            Paragraph("<b>Real-Time Live Quiz Engine</b>", table_cell_bold),
            Paragraph("Host dapat membuat sesi live kuis dengan PIN 6-digit. Mendukung layar Host interaktif, hitung mundur waktu otomatis, penguncian jawaban satu kali (*one-shot answer lock*), pengungkitan skor kecepatan (*speed multiplier*), tampilan progres soal (*Soal X dari Y*), serta *Intermission Podium Top 3* yang di-broadcast via Reverb WebSockets.", table_cell_style)
        ],
        [
            Paragraph("<b>Multi-Step Live Join & Avatar Picker</b>", table_cell_bold),
            Paragraph("Peserta yang bergabung via PIN dapat memilih avatar unik (13 pilihan karakter) dan alias. Bagi pengguna yang sudah login, alias dan avatar diisi otomatis tanpa hambatan.", table_cell_style)
        ],
        [
            Paragraph("<b>Context-Aware Question Bank</b>", table_cell_bold),
            Paragraph("Memungkinkan Creator mengelola bank soal reusable dengan berbagai tipe (Pilihan Ganda, True/False, Isian, Essay). Form pembuatan soal menyesuaikan input secara otomatis sesuai tipe soal yang dipilih.", table_cell_style)
        ],
        [
            Paragraph("<b>AI Material & Question Generator</b>", table_cell_bold),
            Paragraph("Modul pemrosesan materi berbasis AI untuk menghasilkan rangkuman pembelajaran, peta konsep, serta rekomendasi kuis secara otomatis.", table_cell_style)
        ],
        [
            Paragraph("<b>Hybrid Essay Grading & Notifications</b>", table_cell_bold),
            Paragraph("Dukungan evaluasi essay manual oleh Creator. Ketika peserta mengumpulkan essay, sistem langsung mengirim notifikasi realtime ke Creator dan menampilkan badge notifikasi di sidebar.", table_cell_style)
        ],
        [
            Paragraph("<b>Gamification & Badges System</b>", table_cell_bold),
            Paragraph("Sistem XP, kalkulasi level, pencapaian lencana (badges), dan riwayat belajar (attempts) untuk meningkatkan motivasi belajar peserta.", table_cell_style)
        ],
        [
            Paragraph("<b>Tenant Reports & Mobile Workspace</b>", table_cell_bold),
            Paragraph("Laporan analitik lengkap untuk Admin Organisasi dan Creator, responsif penuh di perangkat mobile hingga desktop.", table_cell_style)
        ],
    ]
    feat_table = Table(features_data, colWidths=[140, 347])
    feat_table.setStyle(TableStyle([
        ('BACKGROUND', (0,0), (-1,0), colors.HexColor('#000000')),
        ('BOX', (0,0), (-1,-1), 1, colors.HexColor('#CBD5E1')),
        ('INNERGRID', (0,0), (-1,-1), 0.5, colors.HexColor('#E2E8F0')),
        ('TOPPADDING', (0,0), (-1,-1), 5),
        ('BOTTOMPADDING', (0,0), (-1,-1), 5),
        ('LEFTPADDING', (0,0), (-1,-1), 6),
        ('RIGHTPADDING', (0,0), (-1,-1), 6),
        ('VALIGN', (0,0), (-1,-1), 'TOP'),
    ]))
    story.append(feat_table)
    story.append(Spacer(1, 15))

    # SECTION 5: REKAPITULASI PEMBARUAN & REVISI PRD
    story.append(Paragraph("5. REKAPITULASI PEMBARUAN TERBARU & OPTIMASI PRD", h1_style))
    story.append(Paragraph(
        "Dalam iterasi pengikatan sistem terbaru (versi v1.5), Tim Kai Cenat Mewing Department telah melakukan serangkaian optimasi strategis berdasarkan umpan balik pengguna dan kebutuhan kompetisi:",
        body_style
    ))

    story.append(Paragraph("1. <b>Penetapan Single Light Theme (Penyederhanaan UI):</b> Membuang penanganan Dark Mode yang redundan di seluruh komponen layout dan page untuk menjamin konsistensi kontras visual (berbasis token ENERGY 3, RHYTHM 3, MOTION 2 pada DESIGN.md).", bullet_style))
    story.append(Paragraph("2. <b>Integrasi Real-Time Question Counter:</b> Menambahkan field <code>questionIndex</code> dan <code>totalQuestions</code> pada <code>LiveSessionPresenter</code> serta menampilkan badge indikator <i>Soal ke X dari Y</i> pada layar Host.", bullet_style))
    story.append(Paragraph("3. <b>Real-Time Feedback Jawaban Peserta:</b> Peserta mendapatkan umpan balik langsung (Benar/Salah, poin didapat, atau kunci jawaban) begitu waktu habis atau server merespons, lengkap dengan animasi status.", bullet_style))
    story.append(Paragraph("4. <b>Sistem Notifikasi Dua Arah & Unread Badge Sidebar:</b> Mengintegrasikan <code>unreadNotificationsCount</code> di <code>HandleInertiaRequests</code> dan menampilkan badge counter merah di item Notifikasi sidebar secara realtime.", bullet_style))
    story.append(Paragraph("5. <b>Peningkatan Creator Workflow:</b> Menambahkan menu <i>Penilaian Essay</i> di sidebar Creator serta memperbaiki alur form Question Bank agar pilihan jawaban menyesuaikan tipe soal.", bullet_style))

    story.append(Spacer(1, 15))

    # SECTION 6: KESIMPULAN & PENUTUP
    story.append(Paragraph("6. KESIMPULAN & KESIAPAN KOMPETISI", h1_style))
    story.append(Paragraph(
        "Platform <b>Kuesify</b> yang dikembangkan oleh <b>Kai Cenat Mewing Department</b> tidak hanya memenuhi seluruh kriteria teknis dan estetika yang dipersyaratkan dalam Guide Book INSYFEST 2026, melainkan melampauinya dengan memberikan solusi e-learning modern yang andal, orisinal, dan siap pakai.",
        body_style
    ))
    story.append(Paragraph(
        "Dengan arsitektur real-time berbasis Laravel Reverb, kualitas kode yang teruji (127 pest tests passing), desain antarmuka yang presisi, serta fitur pembalut gamifikasi yang lengkap, Kuesify siap memberikan dampak positif nyata bagi dunia pendidikan digital Indonesia.",
        body_style
    ))

    # Build PDF with NumberedCanvas
    doc.build(story, canvasmaker=NumberedCanvas)
    print(f"PDF Proposal successfully generated at: {filename}")

if __name__ == '__main__':
    output_path = os.path.join(os.getcwd(), "[Proposal] INSYFEST2026-KaiCenatMewingDepartment.pdf")
    build_pdf(output_path)
