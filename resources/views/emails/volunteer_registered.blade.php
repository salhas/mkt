<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Registrasi Relawan - MKT Indonesia</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-w: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #f97316 0%, #f59e0b 100%);
            color: #ffffff;
            padding: 35px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .content {
            padding: 35px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 15px;
        }
        .info-box {
            background-color: #fff7ed;
            border-left: 4px solid #f97316;
            padding: 20px;
            border-radius: 12px;
            margin: 25px 0;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 8px 0;
            font-size: 14px;
        }
        .info-table td.label {
            font-weight: 600;
            color: #64748b;
            width: 40%;
        }
        .info-table td.value {
            font-weight: 700;
            color: #0f172a;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            background-color: #ea580c;
            color: #ffffff;
        }
        .badge-blood {
            background-color: #e11d48;
            color: #ffffff;
        }
        .steps {
            background-color: #f1f5f9;
            border-radius: 16px;
            padding: 20px;
            margin: 25px 0;
        }
        .steps h4 {
            margin: 0 0 10px 0;
            font-size: 14px;
            color: #334155;
        }
        .steps ul {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #475569;
        }
        .steps li {
            margin-bottom: 6px;
        }
        .footer {
            background-color: #090d16;
            color: #94a3b8;
            padding: 25px 30px;
            text-align: center;
            font-size: 12px;
        }
        .footer strong {
            color: #ffffff;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f97316 0%, #f59e0b 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
            margin-top: 15px;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>Yayasan MKT Indonesia</h1>
            <p>Mitra Kemanusiaan Terpadu</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Halo, {{ $volunteer->name }}! 👋
            </div>
            <p>
                Selamat datang dan terima kasih telah mendaftarkan diri sebagai bagian dari <strong>Ekosistem Kemanusiaan Yayasan MKT Indonesia</strong>. Registrasi Anda telah berhasil tercatat dalam sistem database operasional kami.
            </p>

            <!-- Volunteer Details Box -->
            <div class="info-box">
                <table class="info-table">
                    <tr>
                        <td class="label">Nama Relawan:</td>
                        <td class="value">{{ $volunteer->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Alamat Email:</td>
                        <td class="value">{{ $volunteer->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Nomor WhatsApp/HP:</td>
                        <td class="value">{{ $volunteer->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Peran (Role):</td>
                        <td class="value">
                            <span class="badge">{{ $volunteer->role }}</span>
                        </td>
                    </tr>
                    @if($volunteer->blood_type)
                    <tr>
                        <td class="label">Golongan Darah:</td>
                        <td class="value">
                            <span class="badge badge-blood">Golongan Darah {{ $volunteer->blood_type }}</span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Status Keanggotaan:</td>
                        <td class="value" style="color: #059669;">{{ $volunteer->status }}</td>
                    </tr>
                    <tr>
                        <td class="label">Tanggal Terdaftar:</td>
                        <td class="value">{{ \Carbon\Carbon::parse($volunteer->registered_at)->format('d F Y') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Steps Info -->
            <div class="steps">
                <h4>📌 Langkah Selanjutnya & Aktivitas Relawan:</h4>
                <ul>
                    <li><strong>Siaga Bencana & Rescue:</strong> Tim Komando MKT akan menghubungi Anda jika terdapat operasi tanggap darurat bencana di wilayah sekitar Anda.</li>
                    <li><strong>Pendonor Darah Darurat:</strong> Data golongan darah Anda akan masuk dalam list siaga 24/7 untuk kebutuhan transfusi medis darurat.</li>
                    <li><strong>Pelatihan & Sertifikasi:</strong> Informasi jadwal pelatihan SAR & pertolongan pertama akan dikirimkan secara berkala.</li>
                </ul>
            </div>

            <p style="text-align: center;">
                <a href="{{ config('app.url') }}/login" class="button">Buka Portal Relawan MKT</a>
            </p>

            <p style="font-size: 13px; color: #64748b; margin-top: 25px;">
                Jika Anda memiliki pertanyaan atau butuh bantuan lebih lanjut, silakan hubungi Call Center Darurat kami di <strong>+62 812-3456-7890</strong> (24 Jam) atau email ke <strong>info@mkt.or.id</strong>.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin: 0 0 5px 0;"><strong>Yayasan Mitra Kemanusiaan Terpadu (MKT) Indonesia</strong></p>
            <p style="margin: 0;">Perumahan Insignia Oasis Blok B1-11 No 7 | Hotline 24/7: +62 812-3456-7890</p>
            <p style="margin: 10px 0 0 0; opacity: 0.7; font-size: 11px;">
                &copy; {{ date('Y') }} MKT Indonesia. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
