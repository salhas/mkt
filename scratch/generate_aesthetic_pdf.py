import os
import base64
import subprocess

# Read logo base64
logo_path = 'public/images/mkt_logo.png'
logo_b64 = ''
if os.path.exists(logo_path):
    with open(logo_path, 'rb') as f:
        logo_b64 = base64.b64encode(f.read()).decode('utf-8')

html_content = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Syarat & Ketentuan Kemitraan dan Kolaborasi MKT Indonesia</title>
<style>
  @page {{
    size: A4 portrait;
    margin: 12mm 15mm 15mm 15mm;
  }}
  * {{
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }}
  body {{
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    line-height: 1.5;
    font-size: 11px;
    background: #ffffff;
    margin: 0;
    padding: 0;
  }}

  /* Kop Surat */
  .kop-surat {{
    display: flex;
    align-items: center;
    border-bottom: 2.5px solid #1e3a8a;
    padding-bottom: 12px;
    margin-bottom: 16px;
    position: relative;
  }}
  .kop-surat::after {{
    content: "";
    position: absolute;
    bottom: -5px;
    left: 0;
    right: 0;
    height: 1px;
    background-color: #d97706;
  }}
  .kop-logo {{
    width: 68px;
    height: 68px;
    object-fit: contain;
    margin-right: 16px;
    flex-shrink: 0;
  }}
  .kop-text {{
    flex-grow: 1;
    text-align: center;
  }}
  .kop-title {{
    font-size: 16px;
    font-weight: 900;
    color: #0f172a;
    letter-spacing: 0.5px;
    margin: 0;
    text-transform: uppercase;
  }}
  .kop-subtitle {{
    font-size: 12px;
    font-weight: 700;
    color: #1e40af;
    margin: 2px 0 0 0;
  }}
  .kop-legal {{
    font-size: 9.5px;
    color: #64748b;
    margin: 2px 0 0 0;
  }}
  .kop-address {{
    font-size: 9px;
    color: #475569;
    margin: 2px 0 0 0;
  }}

  /* Document Banner */
  .doc-banner {{
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    color: white;
    border-radius: 12px;
    padding: 14px 18px;
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }}
  .doc-banner-left {{
    max-width: 70%;
  }}
  .doc-badge {{
    display: inline-block;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    font-size: 8.5px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    margin-bottom: 4px;
  }}
  .doc-heading {{
    font-size: 14px;
    font-weight: 900;
    margin: 0;
    letter-spacing: -0.2px;
    line-height: 1.25;
  }}
  .doc-sub {{
    font-size: 9.5px;
    color: #bfdbfe;
    margin-top: 3px;
    margin-bottom: 0;
  }}
  .doc-meta {{
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 8px;
    padding: 8px 12px;
    text-align: right;
    font-size: 9px;
  }}
  .doc-meta-label {{
    color: #bfdbfe;
    font-weight: 600;
    font-size: 8px;
    text-transform: uppercase;
  }}
  .doc-meta-value {{
    font-weight: 800;
    color: #ffffff;
  }}

  /* Section Styles */
  .section-card {{
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 12px;
    page-break-inside: avoid;
  }}
  .section-header {{
    display: flex;
    align-items: center;
    margin-bottom: 8px;
  }}
  .section-letter {{
    width: 22px;
    height: 22px;
    background: #1e40af;
    color: #ffffff;
    font-weight: 800;
    font-size: 11px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 8px;
    flex-shrink: 0;
  }}
  .section-title {{
    font-size: 11.5px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.3px;
  }}

  p {{
    margin: 0 0 6px 0;
    color: #334155;
    text-align: justify;
  }}

  /* Principle Quote Box */
  .quote-box {{
    background: #eff6ff;
    border-left: 3.5px solid #2563eb;
    padding: 8px 12px;
    border-radius: 0 8px 8px 0;
    margin: 6px 0;
    font-style: italic;
    color: #1e3a8a;
    font-weight: 600;
    font-size: 10.5px;
  }}

  /* Numbered & Bullet Lists */
  ol, ul {{
    margin: 4px 0 6px 0;
    padding-left: 18px;
  }}
  li {{
    margin-bottom: 3.5px;
    color: #334155;
    text-align: justify;
  }}

  /* Principles Grid */
  .grid-2 {{
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 8px;
    margin-top: 6px;
  }}
  .principle-item {{
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    padding: 7px 10px;
  }}
  .principle-name {{
    font-weight: 800;
    color: #1e40af;
    font-size: 10px;
    margin-bottom: 2px;
    display: flex;
    align-items: center;
  }}
  .principle-num {{
    display: inline-block;
    width: 15px;
    height: 15px;
    background: #dbeafe;
    color: #1e40af;
    border-radius: 50%;
    text-align: center;
    line-height: 15px;
    font-size: 8.5px;
    margin-right: 5px;
    font-weight: 800;
  }}
  .principle-desc {{
    font-size: 9.5px;
    color: #475569;
    line-height: 1.35;
    margin: 0;
  }}

  /* Process Flowchart */
  .flowchart {{
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 10px;
    margin: 8px 0 10px 0;
  }}
  .step {{
    background: #ffffff;
    border: 1px solid #94a3b8;
    border-radius: 6px;
    padding: 4px 8px;
    font-size: 9px;
    font-weight: 700;
    color: #1e293b;
    text-align: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
  }}
  .step.active {{
    background: #1e40af;
    color: #ffffff;
    border-color: #1e40af;
  }}
  .arrow {{
    color: #64748b;
    font-size: 12px;
    font-weight: 900;
  }}

  /* Signature Block */
  .sign-container {{
    margin-top: 18px;
    display: flex;
    justify-content: flex-end;
    page-break-inside: avoid;
  }}
  .sign-box {{
    width: 250px;
    text-align: center;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 10px;
    padding: 10px 14px;
  }}
  .sign-date {{
    font-size: 9.5px;
    color: #475569;
    margin-bottom: 4px;
  }}
  .sign-inst {{
    font-size: 10px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
  }}
  .stamp-area {{
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 4px 0;
  }}
  .stamp-badge {{
    border: 1.5px dashed #1e40af;
    color: #1e40af;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 8.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    background: #eff6ff;
  }}
  .sign-name {{
    font-size: 10px;
    font-weight: 800;
    color: #0f172a;
    border-top: 1px solid #cbd5e1;
    padding-top: 4px;
  }}

  /* Footer */
  .doc-footer {{
    margin-top: 16px;
    border-top: 1px solid #e2e8f0;
    padding-top: 6px;
    display: flex;
    justify-content: space-between;
    font-size: 8.5px;
    color: #94a3b8;
  }}
</style>
</head>
<body>

  <!-- KOP SURAT -->
  <div class="kop-surat">
    <img src="data:image/png;base64,{logo_b64}" class="kop-logo" alt="Logo MKT">
    <div class="kop-text">
      <h1 class="kop-title">MKT INDONESIA</h1>
      <div class="kop-legal">SK Kemenkumham RI: AHU-0012345.AH.01.04.Tahun 2024 &bull; NPWP: 01.234.567.8-801.000</div>
      <div class="kop-address">Sekretariat Pusat: Jl. Perintis Kemerdekaan KM. 10, Tamalanrea, Makassar, Sulawesi Selatan &bull; Email: kemitraan@mkt.or.id</div>
    </div>
  </div>

  <!-- DOCUMENT BANNER -->
  <div class="doc-banner">
    <div class="doc-banner-left">
      <span class="doc-badge">REGULASI RESMI KEMITRAAN</span>
      <h2 class="doc-heading">SYARAT & KETENTUAN KEMITRAAN DAN KOLABORASI</h2>
      <p class="doc-sub">Pedoman Hak, Kewajiban, Kode Etik, dan Mekanisme Kolaborasi Kemanusiaan Bersama MKT Indonesia</p>
    </div>
    <div class="doc-meta">
      <div class="doc-meta-label">NO. REGULASI</div>
      <div class="doc-meta-value">SK-KMT/MKT/2026</div>
      <div class="doc-meta-label" style="margin-top: 4px;">SIFAT DOKUMEN</div>
      <div class="doc-meta-value">RESMI & TERBUKA</div>
    </div>
  </div>

  <!-- PASAL A: KETENTUAN UMUM -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-letter">A</div>
      <div class="section-title">Ketentuan Umum</div>
    </div>
    <p>
      MKT Indonesia membuka kesempatan kepada organisasi, lembaga, komunitas, perguruan tinggi, dunia usaha (CSR), lembaga filantropi, kelompok relawan, serta pihak lain yang memiliki kepedulian dan/atau kapasitas di bidang kemanusiaan, kebencanaan, sosial, pendidikan, teknologi, kesehatan, dan bidang terkait untuk membangun kemitraan dan kolaborasi.
    </p>
    <p>
      Kemitraan dengan MKT Indonesia tidak mengubah identitas, struktur, kewenangan, maupun independensi organisasi mitra. Setiap pihak tetap menjalankan fungsi dan kewenangannya sesuai dengan ketentuan internal serta peraturan yang berlaku.
    </p>
    <div class="quote-box">
      Prinsip Dasar MKT Indonesia: "Menghubungkan potensi, memperkuat kolaborasi, dan menghasilkan dampak kemanusiaan yang lebih besar."
    </div>
  </div>

  <!-- PASAL B: PERSYARATAN MITRA -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-letter">B</div>
      <div class="section-title">Persyaratan Mitra</div>
    </div>
    <p>Lembaga / organisasi yang mengajukan registrasi kemitraan wajib memenuhi kriteria berikut:</p>
    <ol>
      <li><strong>Identitas Jelas:</strong> Memiliki identitas dan keberadaan organisasi yang jelas, baik sebagai lembaga berbadan hukum maupun komunitas/kelompok yang dapat dipertanggungjawabkan keberadaannya.</li>
      <li><strong>Kapasitas & Relevansi:</strong> Memiliki kegiatan, kapasitas, pengalaman, jaringan, sumber daya, atau kompetensi yang relevan dengan bidang kemanusiaan, kebencanaan, sosial, pendidikan, kesehatan, teknologi, lingkungan, atau bidang lain yang mendukung misi kolaborasi.</li>
      <li><strong>Penunjukan PIC:</strong> Menunjuk narahubung / Person in Charge (PIC) resmi yang dapat dihubungi untuk kepentingan komunikasi dan koordinasi berkelanjutan.</li>
      <li><strong>Keabsahan Data:</strong> Memberikan informasi yang benar, lengkap, dan dapat dipertanggungjawabkan pada saat proses registrasi.</li>
      <li><strong>Ketersediaan Verifikasi:</strong> Bersedia melakukan verifikasi data atau klarifikasi tambahan apabila diperlukan oleh MKT Indonesia.</li>
      <li><strong>Etika Kerjasama:</strong> Bersedia menjalankan kemitraan berdasarkan prinsip profesionalitas, transparansi, akuntabilitas, kesetaraan, inklusivitas, dan saling menghormati.</li>
      <li><strong>Kepatuhan Hukum:</strong> Tidak menggunakan kemitraan dengan MKT Indonesia untuk kegiatan yang bertentangan dengan hukum, kepentingan kemanusiaan, atau prinsip-prinsip dasar organisasi.</li>
    </ol>
  </div>

  <!-- PASAL C: PRINSIP KEMITRAAN -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-letter">C</div>
      <div class="section-title">Prinsip Kemitraan (7 Pilar Kolaborasi)</div>
    </div>
    <p>Setiap kemitraan dan kolaborasi MKT Indonesia dilaksanakan secara teguh berdasarkan 7 (tujuh) prinsip dasar:</p>
    
    <div class="grid-2">
      <div class="principle-item">
        <div class="principle-name"><span class="principle-num">1</span> Kesetaraan</div>
        <p class="principle-desc">Posisi yang saling menghormati dan setara, tanpa hubungan subordinasi kecuali disepakati khusus dalam suatu kegiatan.</p>
      </div>
      <div class="principle-item">
        <div class="principle-name"><span class="principle-num">2</span> Independensi</div>
        <p class="principle-desc">Setiap organisasi tetap mempertahankan identitas, tata kelola internal, visi misi, dan kewenangannya masing-masing.</p>
      </div>
      <div class="principle-item">
        <div class="principle-name"><span class="principle-num">3</span> Transparansi</div>
        <p class="principle-desc">Keterbukaan informasi program, sumber daya, kontribusi logistik/personel, dan teknis kegiatan sesuai kesepakatan.</p>
      </div>
      <div class="principle-item">
        <div class="principle-name"><span class="principle-num">4</span> Akuntabilitas</div>
        <p class="principle-desc">Setiap pihak bertanggung jawab penuh atas peran, amanah, sumber daya, dan kegiatan yang menjadi tanggung jawabnya.</p>
      </div>
      <div class="principle-item">
        <div class="principle-name"><span class="principle-num">5</span> Non-diskriminasi & Inklusivitas</div>
        <p class="principle-desc">Menghormati keberagaman tanpa diskriminasi latar belakang suku, agama, ras, gender, maupun afiliasi institusional.</p>
      </div>
      <div class="principle-item">
        <div class="principle-name"><span class="principle-num">6</span> Kepentingan Kemanusiaan</div>
        <p class="principle-desc">Kemitraan diorientasikan semata-mata untuk memberi manfaat nyata bagi masyarakat dan memperkuat kapasitas kemanusiaan.</p>
      </div>
    </div>
    <div style="margin-top: 8px;" class="principle-item">
      <div class="principle-name"><span class="principle-num">7</span> Tidak Mengambil Alih Kewenangan</div>
      <p class="principle-desc">MKT Indonesia maupun mitra dilarang menggunakan mekanisme kolaborasi ini untuk mengambil alih kewenangan resmi pihak lain.</p>
    </div>
  </div>

  <!-- PASAL D: KOMITMEN MITRA -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-letter">D</div>
      <div class="section-title">Komitmen Lembaga Mitra</div>
    </div>
    <p>Dengan melakukan registrasi dan bergabung dalam ekosistem kemitraan MKT Indonesia, lembaga/organisasi menyatakan bersedia:</p>
    <ul>
      <li>Menjaga nama baik, etika, dan reputasi kemitraan kemanusiaan bersama;</li>
      <li>Menghormati aturan, SOP operasional, serta kebijakan masing-masing pihak;</li>
      <li>Memberikan data dan informasi yang benar, akurat, dan tidak menyesatkan publik;</li>
      <li>Melaksanakan komitmen dan peran aksi kemanusiaan yang telah disepakati bersama;</li>
      <li>Menjaga kerahasiaan informasi internal atau data sensitif penyintas bencana yang bersifat terbatas;</li>
      <li>Tidak menyalahgunakan nama, logo, data, jejaring potensi, maupun fasilitas MKT Indonesia untuk kepentingan komersial/politik sepihak;</li>
      <li>Tidak mengatasnamakan MKT Indonesia tanpa adanya persetujuan resmi tertulis;</li>
      <li>Melaporkan atau mengkomunikasikan situasi darurat serta hal-hal penting yang memengaruhi kolaborasi;</li>
      <li>Menyelesaikan segala dinamika atau permasalahan kemitraan melalui komunikasi persuasif dan mekanisme musyawarah yang disepakati.</li>
    </ul>
  </div>

  <!-- PASAL E: PENGGUNAAN DATA & INFORMASI -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-letter">E</div>
      <div class="section-title">Penggunaan Data dan Informasi</div>
    </div>
    <p>Data dan informasi yang diberikan melalui formulir registrasi kemitraan akan digunakan untuk:</p>
    <ul>
      <li>Proses identifikasi, verifikasi profil lembaga, dan penerbitan akun portal mitra resmi;</li>
      <li>Pemetaan potensi kekuatan personel, armada rescue, kompetensi medis/logistik, dan jejaring kolaborasi terpadu;</li>
      <li>Komunikasi cepat dan koordinasi taktis posko siaga darurat kebencanaan;</li>
      <li>Penyusunan program kolaborasi kemanusiaan berkelanjutan dan dokumentasi ekosistem MKT Indonesia.</li>
    </ul>
    <p style="font-size: 10px; color: #64748b; margin-top: 4px;">
      MKT Indonesia berkomitmen mengelola data sesuai peraturan perundang-undangan (UU Perlindungan Data Pribadi). Persetujuan khusus akan dimintakan apabila diperlukan publikasi terbuka di luar kepentingan direktori operasional kemanusiaan.
    </p>
  </div>

  <!-- PASAL F: VERIFIKASI & PERSETUJUAN KEMITRAAN -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-letter">F</div>
      <div class="section-title">Alur Verifikasi dan Persetujuan Kemitraan</div>
    </div>
    <p>
      Pengisian formulir registrasi merupakan tahapan awal dan tidak secara otomatis menjadikan lembaga sebagai mitra tetap. Setiap pengajuan melalui alur kerja berjenjang:
    </p>
    
    <div class="flowchart">
      <div class="step active">1. Registrasi Portal</div>
      <div class="arrow">&rarr;</div>
      <div class="step">2. Verifikasi Data</div>
      <div class="arrow">&rarr;</div>
      <div class="step">3. Klarifikasi PIC</div>
      <div class="arrow">&rarr;</div>
      <div class="step">4. Uji Kesesuaian</div>
      <div class="arrow">&rarr;</div>
      <div class="step active">5. Persetujuan Resmi</div>
    </div>

    <p style="font-size: 10px;">
      Kemitraan dapat diwujudkan melalui: kolaborasi program lapangan, pertukaran keahlian, peningkatan kapasitas & pelatihan, dukungan logistik & armada, pengembangan teknologi & data kebencanaan, hingga jejaring relawan terpadu. Untuk program strategis, para pihak dapat menuangkannya ke dalam naskah <strong>MoU, PKS, MoA, atau Surat Keputusan Bersama</strong>.
    </p>
  </div>

  <!-- PASAL G: PENOLAKAN ATAU PENGAKHIRAN -->
  <div class="section-card">
    <div class="section-header">
      <div class="section-letter">G</div>
      <div class="section-title">Penolakan atau Pengakhiran Kemitraan</div>
    </div>
    <p>MKT Indonesia berhak menolak, menunda, membatasi, atau mengakhiri kemitraan apabila ditemukan:</p>
    <ul>
      <li>Informasi palsu, manipulatif, atau tidak dapat diverifikasi;</li>
      <li>Kegiatan lembaga yang bertentangan dengan hukum, ketertiban umum, atau prinsip kemanusiaan;</li>
      <li>Penyalahgunaan nama, atribut, atau identitas kemitraan MKT Indonesia;</li>
      <li>Pelanggaran berat terhadap komitmen integritas atau keselamatan operasi di lapangan;</li>
      <li>Konflik kepentingan yang merugikan masyarakat luas, penyintas bencana, atau reputasi kemitraan.</li>
    </ul>
    <p style="font-size: 9.5px; color: #64748b;">
      Pengakhiran kemitraan tidak menghapuskan tanggung jawab hukum dan kewajiban moral yang masih berjalan atas aksi kemanusiaan yang telah disepakati sebelumnya.
    </p>
  </div>

  <!-- SIGNATURE & FORMAL CLOSE -->
  <div class="sign-container">
    <div class="sign-box">
      <div class="sign-date">Makassar, Indonesia &bull; 2026</div>
      <div class="sign-inst">MKT INDONESIA</div>
      <div class="stamp-area">
        <div class="stamp-badge">&check; DOKUMEN RESMI TERVALIDASI</div>
      </div>
      <div class="sign-name">PENGURUS & TIM KOORDINASI KEMITRAAN MKT</div>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="doc-footer">
    <span>&copy; 2026 MKT Indonesia. Hak Cipta Dilindungi Undang-Undang.</span>
    <span>Dokumen Regulasi: SK-KMT/MKT-INDONESIA/2026 &bull; Halaman Resmi</span>
  </div>

</body>
</html>
"""

with open('scratch/template_terms.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

# Render to PDF via headless chrome
cmd = [
    '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    '--headless',
    '--disable-gpu',
    '--no-pdf-header-footer',
    '--print-to-pdf=public/docs/syarat-dan-ketentuan-kemitraan-mkt.pdf',
    'scratch/template_terms.html'
]
subprocess.run(cmd, check=True)
print("Aesthetic PDF generated successfully!")
