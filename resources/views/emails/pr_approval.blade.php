<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Approval Purchase Requisition</title>
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
                background: linear-gradient(135deg, #229884, #1a7a6b);
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
                color: #229884;
                font-weight: 600;
            }

            .info-card {
                background-color: #f8fcfb;
                border: 1px solid #e0f0ec;
                border-radius: 10px;
                padding: 25px;
                margin: 25px 0;
            }

            .info-card h3 {
                margin: 0 0 20px;
                color: #229884;
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
                border-bottom: 1px dashed #d0e8e2;
                font-size: 16px;
            }

            .info-list li:last-child {
                border-bottom: none;
            }

            .info-list strong {
                color: #229884;
                min-width: 120px;
            }

            .action-section {
                text-align: center;
                margin: 35px 0;
            }

            .btn-primary {
                display: inline-block;
                background-color: #229884;
                color: #ffffff !important;
                font-weight: 600;
                font-size: 18px;
                padding: 16px 40px;
                border-radius: 8px;
                text-decoration: none;
                box-shadow: 0 4px 15px rgba(34, 152, 132, 0.3);
                transition: all 0.3s;
            }

            .btn-primary:hover {
                background-color: #1d7a6b;
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(34, 152, 132, 0.4);
            }

            .footer {
                background-color: #f0f7f5;
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
                <h1>Approval Purchase Requesition</h1>
                <p>Permintaan persetujuan baru menunggu Anda</p>
            </div>

            <!-- Content -->
            <div class="content">
                <p class="greeting">Yth. Bapak/Ibu {{ $approval->approver->nama_lengkap }},</p>

                <p>Ada Purchase Requesition baru yang membutuhkan persetujuan Anda sebagai
                    <strong>{{ $approval->role }}</strong>:
                </p>

                <!-- Info Card -->
                <div class="info-card">
                    <h3>Detail PR</h3>
                    <ul class="info-list">
                        <li>
                            <strong>PR Number</strong>
                            <span>{{ $pr->pr_number }}</span>
                        </li>
                        <li>
                            <strong>Pengaju</strong>
                            <span>{{ $pr->requested_by }}</span>
                        </li>
                        <li>
                            <strong>Departemen</strong>
                            <span>{{ $pr->department }}</span>
                        </li>
                        <li>
                            <strong>Tanggal PR</strong>
                            <span>{{ \Carbon\Carbon::parse($pr->pr_date)->format('d F Y') }}</span>
                        </li>
                        <li>
                            <strong>Jenis / Detail</strong>
                            <span>{{ $pr->jenis . ' / ' . $pr->detail_jenis }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Action Button -->
                <div class="action-section">
                    <a href="{{ $url }}" class="btn-primary">
                        Check & Approval PR
                    </a>
                </div>

                <p>Terima kasih atas perhatian dan kerjasamanya.</p>
                <p>Hormat kami,<br><strong>DWM</strong></p>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Email ini dikirim otomatis. Mohon tidak membalas email ini.</p>
            </div>
        </div>
    </body>

</html>
