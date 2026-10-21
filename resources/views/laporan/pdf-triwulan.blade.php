<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Triwulan</title>
    <style>
        body { font-family: sans-serif; font-size: 9px; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 16px; }
        .header h3 { margin: 0; padding: 0; font-size: 14px; font-weight: bold; }
        .meta-table { width: 100%; margin-bottom: 10px; border: none; font-size: 11px; }
        .meta-table td { padding: 2px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 4px; text-align: center; vertical-align: middle; }
        table.data-table th { background-color: #f2f2f2; font-weight: bold; }
        table.data-table td.left { text-align: left; }

        .footer { width: 100%; margin-top: 24px; font-size: 11px; }
        .footer-table { width: 100%; border: none; }
        .footer-table td { text-align: center; width: 50%; }
    </style>
</head>
<body>
    @php
        $hektare = fn ($value) => \App\Services\PengamatanHistoryService::hektare((float) $value);
        $months = \App\Services\PeriodePengamatan::quarterMonths($filters['tahun'], $filters['triwulan']);
        $label = \App\Services\PeriodePengamatan::quarterLabel($filters['triwulan']);
    @endphp

    <div class="header">
        <h3>LAPORAN SERANGAN OPT PENTING TANAMAN PERKEBUNAN</h3>
    </div>

    <table class="meta-table">
        <tr>
            <td width="15%">Provinsi</td>
            <td width="2%">:</td>
            <td width="83%">Jambi</td>
        </tr>
        <tr>
            <td>Triwulan</td>
            <td>:</td>
            <td>{{ $label }} ({{ $months[0]->translatedFormat('F Y') }} – {{ $months[2]->translatedFormat('F Y') }})</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2">No</th>
                <th rowspan="2">Kabupaten/Kota</th>
                <th rowspan="2">Jenis Komoditas</th>
                <th rowspan="2">Luas Komoditi (Ha)</th>
                <th rowspan="2">Jenis OPT</th>
                <th colspan="3">Luas Serangan (Ha)</th>
                <th colspan="5">Luas Pengendalian (Ha)</th>
                <th rowspan="2">Kondisi Serangan</th>
            </tr>
            <tr>
                <th>Ringan</th>
                <th>Berat</th>
                <th>Jumlah</th>
                <th>APBD I</th>
                <th>APBD II</th>
                <th>Masyarakat</th>
                <th>APBN</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="left">{{ $row->kabupaten }}</td>
                    <td class="left">{{ $row->komoditas }}</td>
                    <td>{{ $hektare($row->luas_komoditi_ha) }}</td>
                    <td class="left">{{ $row->opt }}{{ $row->jenis_opt !== '' ? ' — '.$row->jenis_opt : '' }}</td>
                    <td>{{ $hektare($row->serangan_ringan) }}</td>
                    <td>{{ $hektare($row->serangan_berat) }}</td>
                    <td><strong>{{ $hektare($row->serangan_jumlah) }}</strong></td>
                    <td>{{ $hektare($row->kendali_apbd_prov) }}</td>
                    <td>{{ $hektare($row->kendali_apbd_kab) }}</td>
                    <td>{{ $hektare($row->kendali_masyarakat) }}</td>
                    <td>{{ $hektare($row->kendali_apbn) }}</td>
                    <td><strong>{{ $hektare($row->kendali_jumlah) }}</strong></td>
                    <td>{{ $row->kondisi_label }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="14">Nihil / Tidak ada data pengamatan pada triwulan ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table class="footer-table">
            <tr>
                <td></td>
                <td>..................., {{ now()->translatedFormat('d F Y') }}<br><br>Pejabat Berwenang,<br><br><br><br><br>( ........................................ )<br>NIP. ........................................</td>
            </tr>
        </table>
    </div>
</body>
</html>
