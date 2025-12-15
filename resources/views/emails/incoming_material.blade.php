<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    </head>

    <body>
        <p>Data material dengan Material Document <strong>{{ $data['material_doc'] }}</strong> telah diterima dan sudah
            ada di gudang.</p>

        <p>Berikut daftar barang:</p>

        <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
            <tr>
                <th>MID</th>
                <th>Nama Barang</th>
                <th>PO Number</th>
                <th>Qty</th>
            </tr>
            @foreach ($data['list'] as $item)
                <tr>
                    <td>{{ $item['mid'] }}</td>
                    <td>{{ $item['nama_barang'] }}</td>
                    <td>{{ $item['po_number'] }}</td>
                    <td>{{ $item['gr_qty'] }}</td>
                </tr>
            @endforeach
        </table>

        <p>Terima kasih.</p>

    </body>

</html>
