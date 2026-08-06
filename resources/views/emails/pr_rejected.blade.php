<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Purchase Requisition Ditolak</title>
        <style>
            body {
                margin: 0;
                padding: 0;
                background-color: #f4f6f9;
                font-family: Arial, Helvetica, sans-serif;
                color: #333333;
                line-height: 1.6;
            }

            .container {
                max-width: 600px;
                margin: 30px auto;
                background-color: #ffffff;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            }

            .header {
                background: linear-gradient(135deg, #d9534f, #c9302c);
                color: #ffffff;
                padding: 40px 30px;
                text-align: center;
            }

            .header h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
            }

            .header p {
                margin: 10px 0 0;
                font-size: 16px;
                opacity: 0.95;
            }

            .content {
                padding: 40px 30px;
            }

            .greeting {
                font-size: 18px;
                margin-bottom: 10px;
                color: #d9534f;
                font-weight: 600;
            }

            .info-card {
                background-color: #fdf8f8;
                border: 1px solid #f2dede;
                border-radius: 10px;
                padding: 25px;
                margin: 25px 0;
            }

            .info-card h3 {
                margin: 0 0 20px;
                color: #d9534f;
                font-size: 20px;
                text-align: center;
            }

            .info-list {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .info-list li {
                display: flex;
                justify-content: space-between;
                padding: 12px 0;
                border-bottom: 1px dashed #f2dede;
                font-size: 16px;
            }

            .info-list li:last-child {
                border-bottom: none;
            }

            .info-list strong {
                color: #d9534f;
                min-width: 120px;
            }

            .action-section {
                text-align: center;
                margin: 35px 0;
            }

            .btn-primary {
                display: inline-block;
                background-color: #d9534f;
                color: #ffffff !important;
                font-weight: 600;
                font-size: 18px;
                padding: 16px 40px;
                border-radius: 8px;
                text-decoration: none;
                box-shadow: 0 4px 15px rgba(217, 83, 79, 0.3);
                transition: all 0.3s;
            }

            .btn-primary:hover {
                background-color: #c9302c;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(217, 83, 79, 0.4);
            }

            .footer {
                background-color: #fcf8f8;
                padding: 25px;
                text-align: center;
                font-size: 14px;
                color: #666666;
            }

            @media only screen and (max-width: 600px) {
                .container {
                    margin: 10px;
                    border-radius: 10px;
                }

                .header {
                    padding: 30px 20px;
                }

                .header h1 {
                    font-size: 24px;
                }

                .content {
                    padding: 25px 20px;
                }

                .info-list li {
                    flex-direction: column;
                    gap: 5px;
                }
            }
        </style>
    </head>

    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>Purchase Requisition Ditolak</h1>
                <p>Permintaan Purchase Requisition Anda telah ditolak</p>
            </div>

            <!-- Content -->
            <div class="content">
                <p class="greeting">Yth. Bapak/Ibu {{ $pr->user->nama_lengkap }},</p>

                <p>Kami ingin menginformasikan bahwa pengajuan Purchase Requisition Anda berikut ini
                    <strong>Ditolak</strong> oleh <strong>{{ $approval->approver->nama_lengkap }}
                        ({{ $approval->role }})</strong>:
                </p>

                <!-- Info Card -->
                <div class="info-card">
                    <h3>Detail PR & Alasan</h3>
                    <ul class="info-list">
                        <li>
                            <strong>PR Number</strong>
                            <span>{{ $pr->pr_number ?? '-' }}</span>
                        </li>
                        <li>
                            <strong>No. Doc</strong>
                            <span>{{ $pr->no_doc }}</span>
                        </li>
                        <li>
                            <strong>Tanggal PR</strong>
                            <span>{{ \Carbon\Carbon::parse($pr->pr_date)->format('d F Y') }}</span>
                        </li>
                        <li>
                            <strong>Jenis / Detail</strong>
                            <span>{{ $pr->jenis . ' / ' . $pr->detail_jenis }}</span>
                        </li>
                        <li>
                            <strong>Catatan</strong>
                            <span style="color: #c9302c; font-weight: bold;">{{ $approval->catatan ?? '-' }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="action-section">
                    <a href="{{ $url }}" class="btn-primary">
                        Lihat Riwayat PR
                    </a>
                </div>

                <p>Silakan klik tombol di atas untuk melihat detail pengajuan PR Anda pada aplikasi.</p>
                <p>Terima kasih atas perhatiannya.</p>
                <p>Hormat kami,<br><strong>System Purchase Requisition</strong></p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Email ini dikirim otomatis. Mohon tidak membalas email ini.</p>
            </div>
        </div>
    </body>

</html>
