@php
    $headerStyle = 'font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;';
    $cellStyle = 'vertical-align: middle; border: 1px solid #000000;';
    $numberStyle = 'text-align: center; vertical-align: middle; border: 1px solid #000000;';
    $months = \App\Services\PeriodePengamatan::quarterMonths($filters['tahun'], $filters['triwulan']);
    $label = \App\Services\PeriodePengamatan::quarterLabel($filters['triwulan']);
@endphp

<table>
    <tr><td colspan="16"></td></tr>
    <tr><td colspan="16"></td></tr>
    <tr>
        <td></td>
        <td colspan="15" style="font-weight: bold; text-align: center; font-size: 14px;">LAPORAN SERANGAN OPT PENTING TANAMAN PERKEBUNAN</td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2">Provinsi</td>
        <td colspan="13">: Jambi</td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2">Triwulan</td>
        <td colspan="13">: {{ $label }} ({{ $months[0]->translatedFormat('F Y') }} – {{ $months[2]->translatedFormat('F Y') }})</td>
    </tr>
    <tr><td colspan="16"></td></tr>
    <tr>
        <td></td>
        <td rowspan="2" style="{{ $headerStyle }}">No</td>
        <td rowspan="2" style="{{ $headerStyle }}">Kabupaten/Kota</td>
        <td rowspan="2" style="{{ $headerStyle }}">Jenis Komoditas</td>
        <td rowspan="2" style="{{ $headerStyle }}">Luas Komoditi (Ha)</td>
        <td rowspan="2" style="{{ $headerStyle }}">Jenis OPT</td>
        <td colspan="3" style="{{ $headerStyle }}">Luas Serangan (Ha)</td>
        <td colspan="5" style="{{ $headerStyle }}">Luas Pengendalian (Ha)</td>
        <td rowspan="2" style="{{ $headerStyle }}">Kondisi Serangan</td>
    </tr>
    <tr>
        <td></td>
        <td style="{{ $headerStyle }}">Ringan</td>
        <td style="{{ $headerStyle }}">Berat</td>
        <td style="{{ $headerStyle }}">Jumlah</td>
        <td style="{{ $headerStyle }}">APBD I</td>
        <td style="{{ $headerStyle }}">APBD II</td>
        <td style="{{ $headerStyle }}">Masyarakat</td>
        <td style="{{ $headerStyle }}">APBN</td>
        <td style="{{ $headerStyle }}">Jumlah</td>
    </tr>
    @foreach($data as $row)
        <tr>
            <td></td>
            <td style="{{ $numberStyle }}">{{ $loop->iteration }}</td>
            <td style="{{ $cellStyle }}">{{ $row->kabupaten }}</td>
            <td style="{{ $cellStyle }}">{{ $row->komoditas }}</td>
            <td style="{{ $numberStyle }}">{{ $row->luas_komoditi_ha }}</td>
            <td style="{{ $cellStyle }}">{{ $row->opt }}{{ $row->jenis_opt !== '' ? ' — '.$row->jenis_opt : '' }}</td>
            <td style="{{ $numberStyle }}">{{ $row->serangan_ringan }}</td>
            <td style="{{ $numberStyle }}">{{ $row->serangan_berat }}</td>
            <td style="font-weight: bold; {{ $numberStyle }}">{{ $row->serangan_jumlah }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_apbd_prov }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_apbd_kab }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_masyarakat }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_apbn }}</td>
            <td style="font-weight: bold; {{ $numberStyle }}">{{ $row->kendali_jumlah }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kondisi_label }}</td>
        </tr>
    @endforeach

    @for($i = 0; $i < 5; $i++)
        <tr>
            <td></td>
            @for($column = 0; $column < 14; $column++)<td style="border: 1px solid #000000;"></td>@endfor
        </tr>
    @endfor

    <tr><td colspan="16"></td></tr>
    <tr>
        <td colspan="11"></td>
        <td colspan="4" style="text-align: center;">..................., {{ now()->translatedFormat('d F Y') }}</td>
    </tr>
    <tr>
        <td colspan="11"></td>
        <td colspan="4" style="text-align: center;">Pejabat Berwenang,</td>
    </tr>
    <tr><td colspan="16"></td></tr>
    <tr><td colspan="16"></td></tr>
    <tr><td colspan="16"></td></tr>
    <tr>
        <td colspan="11"></td>
        <td colspan="4" style="text-align: center;">( ........................................ )</td>
    </tr>
    <tr>
        <td colspan="11"></td>
        <td colspan="4" style="text-align: center;">NIP. ........................................</td>
    </tr>
</table>
