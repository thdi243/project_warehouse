<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>SO WFG Report</title>
        <style>
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                background-color: #f8fafc;
                color: #334155;
                margin: 0;
                padding: 30px 20px;
                line-height: 1.6;
            }

            .container {
                max-width: 700px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
                overflow: hidden;
            }

            .header {
                background: linear-gradient(135deg, #3b82f6, #2563eb);
                color: white;
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
                opacity: 0.95;
                font-size: 16px;
            }

            .content {
                padding: 40px 40px 30px;
            }

            .greeting {
                font-size: 18px;
                margin-bottom: 25px;
            }

            .highlight-box {
                background: #eff6ff;
                border-left: 5px solid #3b82f6;
                padding: 20px;
                border-radius: 8px;
                margin: 25px 0;
                font-size: 16px;
            }

            .highlight-box strong {
                color: #1e40af;
            }

            .closing {
                margin-top: 40px;
                font-size: 16px;
            }

            .signature {
                margin-top: 30px;
                color: #64748b;
            }

            .signature strong {
                color: #1e293b;
                font-size: 18px;
            }

            .footer {
                background: #f1f5f9;
                padding: 25px;
                text-align: center;
                font-size: 14px;
                color: #64748b;
            }

            .emoji {
                font-size: 20px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <!-- Header -->
            <div class="header">
                <h1>Laporan SO WFG</h1>
                <p>Laporan harian Warehouse Finishing Goods</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    Halo <strong>{{ $recipientName }}</strong>,
                </div>

                <p>Terlampir laporan <strong>SO WFG</strong> tanggal <strong>{{ $tanggal }}</strong> untuk
                    principal <strong>{{ $principal }}</strong>.</p>

                <div class="highlight-box">
                    <p>Silakan diperiksa dan ditindaklanjuti sesuai kebutuhan. <span class="emoji">🙏</span></p>
                </div>

                <div class="closing">
                    <p>Terima kasih atas perhatian dan kerjasamanya.</p>
                </div>

                <div class="signature">
                    <p>Salam,</p>
                    <p><strong>Warehouse Team</strong></p>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Email ini dikirim secara otomatis oleh sistem Warehouse Management.</p>
                <p>Mohon tidak membalas email ini.</p>
            </div>
        </div>
    </body>

</html>
