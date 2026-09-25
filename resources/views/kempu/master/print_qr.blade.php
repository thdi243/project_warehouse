<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Cetak Label QR Code Kempu</title>
        <!-- QRCode.js Library -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
        <!-- html2canvas & jsPDF for Direct PDF Export -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

        <style id="page-style">
            @page {
                size: A4 portrait;
                margin: 4mm 5mm;
            }
        </style>

        <style>
            * {
                box-sizing: border-box;
                font-family: Arial, Helvetica, sans-serif;
                margin: 0;
                padding: 0;
            }

            body {
                background-color: #334155;
                padding: 20px 10px;
                min-height: 100vh;
            }

            /* --- TOP NAVIGATION BAR --- */
            .no-print-bar {
                background: #0f172a;
                color: #ffffff;
                padding: 12px 24px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-radius: 10px;
                margin: 0 auto 24px auto;
                max-width: 900px;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
                flex-wrap: wrap;
                gap: 12px;
            }

            .bar-info {
                display: flex;
                flex-direction: column;
                gap: 3px;
            }

            .bar-title {
                font-size: 16px;
                font-weight: 700;
                color: #f8fafc;
            }

            .bar-subtitle {
                font-size: 12px;
                color: #94a3b8;
            }

            .bar-controls {
                display: flex;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .paper-toggle-group {
                display: flex;
                background: #1e293b;
                padding: 3px;
                border-radius: 8px;
                border: 1px solid #334155;
            }

            .paper-btn {
                background: transparent;
                border: none;
                color: #94a3b8;
                padding: 6px 14px;
                font-size: 12px;
                font-weight: 600;
                border-radius: 6px;
                cursor: pointer;
                transition: all 0.2s;
            }

            .paper-btn.active {
                background: #2563eb;
                color: #ffffff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            }

            .btn-action {
                border: none;
                padding: 8px 18px;
                font-size: 13px;
                font-weight: 700;
                border-radius: 6px;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                transition: background 0.2s;
            }

            .btn-print {
                background: #10b981;
                color: white;
            }

            .btn-print:hover {
                background: #059669;
            }

            .btn-pdf {
                background: #0284c7;
                color: white;
            }

            .btn-pdf:hover {
                background: #0369a1;
            }

            .btn-close-view {
                background: #475569;
                color: white;
            }

            .btn-close-view:hover {
                background: #64748b;
            }

            /* --- PREVIEW CONTAINER --- */
            .sheets-container {
                display: flex;
                flex-direction: column;
                gap: 28px;
                align-items: center;
            }

            /* --- A4 SHEET WRAPPER (SCREEN PREVIEW) --- */
            .sheet-a4 {
                background: #ffffff;
                width: 210mm;
                height: 297mm;
                max-height: 297mm;
                box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.4);
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                padding: 5mm;
                position: relative;
                box-sizing: border-box;
                overflow: hidden;
            }

            /* --- A5 SLOT (HALF OF A4) --- */
            .a5-slot {
                width: 100%;
                height: 138mm;
                max-height: 138mm;
                box-sizing: border-box;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 2mm 0;
            }

            /* --- LABEL CARD --- */
            .label-card {
                background: #ffffff;
                border: 3.5px solid #000000;
                border-radius: 14px;
                padding: 12px 20px;
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 8px;
                box-sizing: border-box;
                position: relative;
                overflow: hidden;
            }

            /* --- WATERMARK FOOTER DETAILS --- */
            .label-watermark-meta {
                font-size: 12px;
                font-style: italic;
                font-weight: 500;
                color: #475569;
                letter-spacing: 0.3px;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                z-index: 2;
                position: relative;
                margin-top: 6px;
                padding: 0;
                line-height: 1;
                white-space: nowrap;
            }

            .label-watermark-meta .meta-item {
                display: inline-flex;
                align-items: baseline;
                gap: 4px;
            }

            .label-watermark-meta .meta-val {
                font-weight: 700;
                color: #0f172a;
            }

            .label-watermark-meta .meta-sep {
                color: #94a3b8;
                font-weight: 700;
                font-size: 11px;
            }

            .id-kempu-val {
                font-size: 84px;
                font-weight: 1000;
                color: #000000;
                letter-spacing: 4px;
                line-height: 1.1;
                text-align: center;
            }

            .qr-wrapper {
                background: #ffffff;
                padding: 6px;
                border: 2.5px solid #000000;
                border-radius: 10px;
                width: 250px;
                height: 250px;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .qr-wrapper img,
            .qr-wrapper canvas {
                width: 100% !important;
                height: 100% !important;
                display: block;
            }

            .label-footer {
                font-size: 12px;
                font-weight: 700;
                color: #000000;
                letter-spacing: 1.5px;
                text-transform: uppercase;
                font-style: italic;
            }

            /* --- DASHED CUT LINE (GARIS POTONG) --- */
            .cut-line {
                width: 100%;
                height: 11mm;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                margin: 0;
            }

            .cut-line::before {
                content: '';
                position: absolute;
                left: 0;
                right: 0;
                top: 50%;
                border-top: 1.5px dashed #94a3b8;
            }

            .cut-line span {
                background: #ffffff;
                padding: 0 12px;
                font-size: 10px;
                font-weight: 700;
                color: #64748b;
                position: relative;
                z-index: 1;
                letter-spacing: 0.5px;
            }

            /* ========================================================
               MODE KERTAS A5 (JIKA USER INGIN CETAK NATIVE PADA KERTAS A5)
               ======================================================== */
            body.paper-mode-a5 .sheet-a4 {
                display: contents !important;
            }

            body.paper-mode-a5 .a5-slot {
                background: #ffffff;
                width: 210mm;
                height: 148.5mm;
                max-height: 148.5mm;
                box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.4);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 5mm;
                box-sizing: border-box;
            }

            body.paper-mode-a5 .cut-line {
                display: none !important;
            }

            /* ========================================================
               PRINT MEDIA STYLING
               ======================================================== */
            @media print {

                html,
                body {
                    background: #ffffff !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .no-print-bar {
                    display: none !important;
                }

                .sheets-container {
                    display: block !important;
                }

                /* Default Print: Kertas A4 (2 Label A5 per Lembar) */
                body.paper-mode-a4 .sheet-a4 {
                    box-shadow: none !important;
                    width: 100% !important;
                    height: 288mm !important;
                    max-height: 288mm !important;
                    padding: 3mm 0 !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    overflow: hidden !important;
                }

                body.paper-mode-a4 .sheet-a4:last-of-type,
                body.paper-mode-a4 .sheets-container>.sheet-a4:last-child {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }

                body.paper-mode-a4 .a5-slot {
                    height: 138mm !important;
                    max-height: 138mm !important;
                    padding: 1mm 0 !important;
                }

                body.paper-mode-a4 .label-card {
                    border: 3.5px solid #000000 !important;
                    height: 100% !important;
                    box-shadow: none !important;
                }

                body.paper-mode-a4 .cut-line::before {
                    border-top: 1.5px dashed #000000 !important;
                }

                body.paper-mode-a4 .cut-line span {
                    color: #000000 !important;
                }

                /* Print: Kertas Native A5 (1 Label per Lembar) */
                body.paper-mode-a5 .sheet-a4 {
                    display: contents !important;
                }

                body.paper-mode-a5 .cut-line {
                    display: none !important;
                }

                body.paper-mode-a5 .a5-slot {
                    background: #ffffff !important;
                    box-shadow: none !important;
                    width: 100% !important;
                    height: 139mm !important;
                    max-height: 139mm !important;
                    padding: 2mm 0 !important;
                    page-break-after: always !important;
                    break-after: page !important;
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: center !important;
                    box-sizing: border-box !important;
                    margin: 0 !important;
                }

                body.paper-mode-a5 .sheets-container>.sheet-a4:last-child>.a5-slot:last-child,
                body.paper-mode-a5 .sheet-a4:last-of-type .a5-slot:last-of-type {
                    page-break-after: auto !important;
                    break-after: auto !important;
                }

                body.paper-mode-a5 .label-card {
                    border: 3.5px solid #000000 !important;
                    height: 100% !important;
                    box-shadow: none !important;
                }

                .label-watermark-meta {
                    color: #333333 !important;
                    background: transparent !important;
                }

                .label-watermark-meta .meta-val {
                    color: #000000 !important;
                    font-weight: 700 !important;
                }

                .label-watermark-meta .meta-sep {
                    color: #666666 !important;
                }
            }
        </style>
    </head>

    <body class="paper-mode-a4">

        <!-- TOP CONTROLS (TIDAK TERCETAK) -->
        <div class="no-print-bar">
            <div class="bar-info">
                <div class="bar-title">&#128438; Cetak Label QR Code Kempu (Ukuran A5)</div>
                <div class="bar-subtitle">
                    Total: <strong>{{ $kempuList->count() }} Label</strong> &bull;
                    <span id="pageCountLabel">Estimasi: {{ ceil($kempuList->count() / 2) }} Lembar Kertas A4</span>
                </div>
            </div>

            <div class="bar-controls">
                <!-- Pilihan Ukuran Kertas -->
                <div class="paper-toggle-group" title="Pilih ukuran kertas yang ada pada printer Anda">
                    <button type="button" class="paper-btn active" id="btnModeA4" onclick="setPaperSize('A4')">
                        &#128196; Kertas A4 (2 Label / Lembar)
                    </button>
                    <button type="button" class="paper-btn" id="btnModeA5" onclick="setPaperSize('A5')">
                        &#128195; Kertas A5 (1 Label / Lembar)
                    </button>
                </div>

                <!-- Tombol Print -->
                <button class="btn-action btn-print" id="btnPrintNow" onclick="doPrint()">
                    &#128438; Cetak Sekarang
                </button>

                <!-- Tombol Download PDF -->
                <button class="btn-action btn-pdf" id="btnDownloadPdf" onclick="downloadPdf()">
                    &#128190; Download PDF
                </button>

                <!-- Tombol Kembali / Tutup -->
                <button class="btn-action btn-close-view" onclick="window.close(); if(!window.closed) history.back();">
                    &times; Tutup
                </button>
            </div>
        </div>

        <!-- CONTAINER SHEET A4 -->
        <div class="sheets-container" id="sheetsContainer">
            @foreach ($kempuList->chunk(2) as $chunk)
                <div class="sheet-a4">
                    <!-- LABEL 1 (BAGIAN ATAS - UKURAN A5) -->
                    @php
                        $kempu1 = $chunk->first();
                        $uName1 =
                            Auth::user()?->nama_lengkap ??
                            (Auth::user()?->username ?? ($kempu1->printedBy?->nama_lengkap ?? 'SYSTEM'));
                        $tglPrint1 = date('d/m/Y H:i:s');
                        $count1 = ($kempu1->print_count ?? 0) + 1;
                    @endphp
                    <div class="a5-slot">
                        <div class="label-card">
                            <div class="id-kempu-val" style="position: relative; z-index: 2;">{{ $kempu1->id_kempu }}
                            </div>
                            <div class="qr-wrapper" id="qr-{{ $kempu1->id }}" style="position: relative; z-index: 2;">
                            </div>

                            <!-- WATERMARK FOOTER METADATA -->
                            <div class="label-watermark-meta">
                                <span class="meta-item">Printed by: <span class="meta-val"
                                        id="print-user-{{ $kempu1->id }}">{{ $uName1 }}</span></span>
                                <span class="meta-sep">&bull;</span>
                                <span class="meta-item">Date: <span class="meta-val"
                                        id="print-date-{{ $kempu1->id }}">{{ $tglPrint1 }}</span></span>
                                <span class="meta-sep">&bull;</span>
                                <span class="meta-item">Reprint: <span class="meta-val"
                                        id="print-count-{{ $kempu1->id }}">{{ $count1 }}</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- GARIS POTONG A5 (DI TENGAH LEMBAR A4) -->
                    <div class="cut-line">
                        <span></span>
                    </div>

                    <!-- LABEL 2 (BAGIAN BAWAH - UKURAN A5) -->
                    @if ($chunk->count() > 1)
                        @php
                            $kempu2 = $chunk->last();
                            $uName2 =
                                Auth::user()?->nama_lengkap ??
                                (Auth::user()?->username ?? ($kempu2->printedBy?->nama_lengkap ?? 'SYSTEM'));
                            $tglPrint2 = date('d/m/Y H:i:s');
                            $count2 = ($kempu2->print_count ?? 0) + 1;
                        @endphp
                        <div class="a5-slot">
                            <div class="label-card">
                                <div class="id-kempu-val" style="position: relative; z-index: 2;">
                                    {{ $kempu2->id_kempu }}</div>
                                <div class="qr-wrapper" id="qr-{{ $kempu2->id }}"
                                    style="position: relative; z-index: 2;"></div>

                                <!-- WATERMARK FOOTER METADATA -->
                                <div class="label-watermark-meta">
                                    <span class="meta-item">Printed by: <span class="meta-val"
                                            id="print-user-{{ $kempu2->id }}">{{ $uName2 }}</span></span>
                                    <span class="meta-sep">&bull;</span>
                                    <span class="meta-item">Date: <span class="meta-val"
                                            id="print-date-{{ $kempu2->id }}">{{ $tglPrint2 }}</span></span>
                                    <span class="meta-sep">&bull;</span>
                                    <span class="meta-item">Reprint: <span class="meta-val"
                                            id="print-count-{{ $kempu2->id }}">{{ $count2 }}</span></span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <script>
            // 1. Inisialisasi QR Code untuk semua item
            window.addEventListener('DOMContentLoaded', function() {
                @foreach ($kempuList as $kempu)
                    new QRCode(document.getElementById("qr-{{ $kempu->id }}"), {
                        text: "{{ $kempu->id_kempu }}",
                        width: 200,
                        height: 200,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });
                @endforeach

                // Auto trigger print / preset paper jika dibuka dengan parameter url
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('paper') === 'A5') {
                    setPaperSize('A5');
                }
                if (urlParams.get('auto') === '1') {
                    setTimeout(() => {
                        doPrint();
                    }, 800);
                }
            });

            // 2. Fungsi Cetak & Rekam Log Cetak ke Database saat tombol diklik
            let isSubmittingPrint = false;

            async function doPrint() {
                if (isSubmittingPrint) return;
                isSubmittingPrint = true;

                const btnPrint = document.getElementById('btnPrintNow');
                const originalText = btnPrint ? btnPrint.innerHTML : '';
                if (btnPrint) {
                    btnPrint.disabled = true;
                    btnPrint.innerHTML = '&#9203; Menyiapkan cetakan...';
                }

                try {
                    const response = await fetch("{{ route('kempu.master.record-print') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            ids: @json($kempuList->pluck('id'))
                        })
                    });

                    const data = await response.json();
                    if (data.status && data.items) {
                        data.items.forEach(item => {
                            const elUser = document.getElementById(`print-user-${item.id}`);
                            if (elUser) elUser.innerText = item.printed_by;
                            const elDate = document.getElementById(`print-date-${item.id}`);
                            if (elDate) elDate.innerText = item.printed_at;
                            const elCount = document.getElementById(`print-count-${item.id}`);
                            if (elCount) elCount.innerText = item.print_count;
                        });
                    }
                } catch (err) {
                    console.error("Gagal mencatat log cetak:", err);
                } finally {
                    if (btnPrint) {
                        btnPrint.disabled = false;
                        btnPrint.innerHTML = originalText;
                    }
                    isSubmittingPrint = false;
                    window.print();
                }
            }

            // Keyboard shortcut Ctrl+P / Cmd+P
            window.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    doPrint();
                }
            });

            // 3. Fungsi Download PDF Langsung (A4 / A5)
            let isDownloadingPdf = false;

            async function downloadPdf() {
                if (isDownloadingPdf) return;
                isDownloadingPdf = true;

                const btnPdf = document.getElementById('btnDownloadPdf');
                const originalText = btnPdf ? btnPdf.innerHTML : '';
                if (btnPdf) {
                    btnPdf.disabled = true;
                    btnPdf.innerHTML = '&#9203; Menyiapkan PDF...';
                }

                try {
                    // Catat log cetak ke database
                    fetch("{{ route('kempu.master.record-print') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        body: JSON.stringify({
                            ids: @json($kempuList->pluck('id'))
                        })
                    }).then(res => res.json()).then(data => {
                        if (data.status && data.items) {
                            data.items.forEach(item => {
                                const elUser = document.getElementById(`print-user-${item.id}`);
                                if (elUser) elUser.innerText = item.printed_by;
                                const elDate = document.getElementById(`print-date-${item.id}`);
                                if (elDate) elDate.innerText = item.printed_at;
                                const elCount = document.getElementById(`print-count-${item.id}`);
                                if (elCount) elCount.innerText = item.print_count;
                            });
                        }
                    }).catch(e => console.error("Gagal catat log cetak:", e));

                    if (!window.jspdf || !window.html2canvas) {
                        throw new Error("Library PDF belum siap dimuat.");
                    }

                    const {
                        jsPDF
                    } = window.jspdf;
                    const isA5 = document.body.classList.contains('paper-mode-a5');
                    const nowStr = new Date().toISOString().slice(0, 10);

                    if (isA5) {
                        const pdf = new jsPDF({
                            orientation: 'landscape',
                            unit: 'mm',
                            format: 'a5'
                        });
                        const slots = document.querySelectorAll('.a5-slot');
                        for (let i = 0; i < slots.length; i++) {
                            if (btnPdf) btnPdf.innerHTML = `&#9203; Memproses PDF (${i + 1}/${slots.length})...`;
                            const slot = slots[i];
                            const canvas = await html2canvas(slot, {
                                scale: 2,
                                useCORS: true,
                                backgroundColor: '#ffffff',
                                logging: false,
                                onclone: (clonedDoc) => {
                                    clonedDoc.querySelectorAll('.a5-slot').forEach(el => {
                                        el.style.boxShadow = 'none';
                                        el.style.margin = '0';
                                    });
                                }
                            });
                            const imgData = canvas.toDataURL('image/jpeg', 0.95);
                            if (i > 0) pdf.addPage('a5', 'landscape');
                            pdf.addImage(imgData, 'JPEG', 0, 0, 210, 148.5);
                        }
                        pdf.save(`QR_Kempu_A5_${nowStr}.pdf`);
                    } else {
                        const pdf = new jsPDF({
                            orientation: 'portrait',
                            unit: 'mm',
                            format: 'a4'
                        });
                        const sheets = document.querySelectorAll('.sheet-a4');
                        for (let i = 0; i < sheets.length; i++) {
                            if (btnPdf) btnPdf.innerHTML = `&#9203; Memproses PDF (${i + 1}/${sheets.length})...`;
                            const sheet = sheets[i];
                            const canvas = await html2canvas(sheet, {
                                scale: 2,
                                useCORS: true,
                                backgroundColor: '#ffffff',
                                logging: false,
                                onclone: (clonedDoc) => {
                                    clonedDoc.querySelectorAll('.sheet-a4').forEach(el => {
                                        el.style.boxShadow = 'none';
                                        el.style.margin = '0';
                                    });
                                }
                            });
                            const imgData = canvas.toDataURL('image/jpeg', 0.95);
                            if (i > 0) pdf.addPage('a4', 'portrait');
                            pdf.addImage(imgData, 'JPEG', 0, 0, 210, 297);
                        }
                        pdf.save(`QR_Kempu_A4_${nowStr}.pdf`);
                    }
                } catch (err) {
                    console.error("Gagal mendownload PDF:", err);
                    alert("Gagal mengekspor PDF langsung: " + (err.message || err) +
                        "\n\nTips: Anda juga bisa menggunakan tombol 'Cetak Sekarang' dan pilih 'Save as PDF'.");
                } finally {
                    if (btnPdf) {
                        btnPdf.disabled = false;
                        btnPdf.innerHTML = originalText;
                    }
                    isDownloadingPdf = false;
                }
            }

            // 4. Fungsi Toggle Ukuran Kertas (A4 / A5)
            function setPaperSize(size) {
                const pageStyle = document.getElementById('page-style');
                const btnA4 = document.getElementById('btnModeA4');
                const btnA5 = document.getElementById('btnModeA5');
                const pageCountLabel = document.getElementById('pageCountLabel');
                const totalItems = {{ $kempuList->count() }};

                if (size === 'A5') {
                    document.body.classList.remove('paper-mode-a4');
                    document.body.classList.add('paper-mode-a5');

                    btnA4.classList.remove('active');
                    btnA5.classList.add('active');

                    pageStyle.innerHTML = `
                        @page {
                            size: A5 landscape;
                            margin: 4mm 5mm;
                        }
                    `;
                    pageCountLabel.innerText = `Estimasi: ${totalItems} Lembar Kertas A5`;
                } else {
                    document.body.classList.remove('paper-mode-a5');
                    document.body.classList.add('paper-mode-a4');

                    btnA5.classList.remove('active');
                    btnA4.classList.add('active');

                    pageStyle.innerHTML = `
                        @page {
                            size: A4 portrait;
                            margin: 4mm 5mm;
                        }
                    `;
                    pageCountLabel.innerText = `Estimasi: ${Math.ceil(totalItems / 2)} Lembar Kertas A4`;
                }
            }
        </script>
    </body>

</html>
