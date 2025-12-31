<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Penerimaan Material</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f6f9;
                color: #333;
                margin: 0;
                padding: 20px;
            }

            .container {
                max-width: 800px;
                margin: 0 auto;
            }

            .card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                padding: 30px;
                margin-bottom: 20px;
            }

            .header {
                text-align: center;
                margin-bottom: 30px;
            }

            .header h1 {
                color: #2c3e50;
                margin: 0;
            }

            .header p {
                color: #7f8c8d;
                margin: 10px 0 0;
            }

            .info-card {
                background: #e8f4fd;
                border-left: 5px solid #3498db;
                padding: 15px;
                margin-bottom: 30px;
                border-radius: 8px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }

            th {
                background-color: #3498db;
                color: white;
                padding: 12px;
                text-align: left;
            }

            td {
                padding: 12px;
                border-bottom: 1px solid #ddd;
            }

            tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            .footer {
                text-align: center;
                margin-top: 40px;
                color: #95a5a6;
                font-size: 14px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <div class="card header">
                <h1>Penerimaan Material Berhasil</h1>
                <p>Material Document: <strong>{{ $data['material_doc'] }}</strong></p>
            </div>

            <div class="card info-card">
                <p><strong>Selamat!</strong> Data material dengan Material Document
                    <strong>{{ $data['material_doc'] }}</strong> telah diterima dan sudah tersimpan di gudang.</p>
            </div>

            <div class="card">
                <h2 style="color: #2c3e50; margin-top: 0;">Daftar Barang</h2>
                <table>
                    <thead>
                        <tr>
                            <th>MID</th>
                            <th>Nama Barang</th>
                            <th>PO Number</th>
                            <th>Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['list'] as $item)
                            <tr>
                                <td>{{ $item['mid'] }}</td>
                                <td>{{ $item['nama_barang'] }}</td>
                                <td>{{ $item['po_number'] }}</td>
                                <td>{{ $item['gr_qty'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <p>Terima kasih atas kerjasamanya.</p>
                <p>Email ini dikirim otomatis oleh sistem.</p>
            </div>
        </div>
    </body>

</html>
