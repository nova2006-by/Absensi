<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Cetak Laporan') - SMK NU Bandar</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm;
        }

        body {
            font-family: 'Inter', 'Segoe UI', ui-sans-serif, system-ui, sans-serif;
            color: #1e293b;
            background: #f1f5f9;
        }

        .kertas {
            max-width: 210mm;
            margin: 2rem auto;
            background: #fff;
            padding: 14mm;
            box-shadow: 0 4px 16px rgb(0 0 0 / 0.08);
        }

        .kop {
            text-align: center;
            border-bottom: 3px double #1e293b;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .kop h1 {
            font-size: 16pt;
            font-weight: 800;
            letter-spacing: 0.04em;
            margin: 0;
        }

        .kop p {
            margin: 2px 0 0;
            font-size: 9.5pt;
            color: #475569;
        }

        .judul-laporan {
            text-align: center;
            font-size: 12pt;
            font-weight: 700;
            text-transform: uppercase;
            text-decoration: underline;
            margin: 18px 0 4px;
        }

        .periode {
            text-align: center;
            font-size: 10pt;
            color: #475569;
            margin: 0 0 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
        }

        th,
        td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background: #e2e8f0;
            font-weight: 700;
            font-size: 9.5pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        td.tengah,
        th.tengah {
            text-align: center;
        }

        td.kanan,
        th.kanan {
            text-align: right;
        }

        .tanda-tangan {
            margin-top: 36px;
            display: flex;
            justify-content: space-between;
            font-size: 10pt;
        }

        .tanda-tangan .kolom {
            width: 45%;
            text-align: center;
        }

        .tanda-tangan .nama {
            margin-top: 52px;
            font-weight: 700;
            text-decoration: underline;
        }

        .kosong {
            text-align: center;
            color: #94a3b8;
            padding: 24px !important;
        }

        .no-print {
            max-width: 210mm;
            margin: 1.5rem auto 0;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: flex-end;
        }

        @media print {
            body {
                background: #fff;
            }

            .kertas {
                margin: 0;
                padding: 0;
                box-shadow: none;
                max-width: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <a href="{{ url()->previous() }}" class="btn btn-soft">← Kembali</a>
        <button type="button" onclick="window.print()" class="btn btn-primary">🖨️ Cetak / Simpan PDF</button>
    </div>

    <div class="kertas">
        @yield('isi')
    </div>
</body>

</html>
