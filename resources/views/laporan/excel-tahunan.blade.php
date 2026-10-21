@php
    $headerStyle = 'font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2; white-space: normal;';
    $dataStyle = 'vertical-align: middle; border: 1px solid #000000; white-space: normal;';
    $numberStyle = 'text-align: center; vertical-align: middle; border: 1px solid #000000; white-space: normal;';
@endphp

<table>
    <tr><td colspan="18" style="height: 12px;"></td></tr>
    <tr><td colspan="18" style="height: 12px;"></td></tr>
    <tr><td colspan="18" style="height: 12px;"></td></tr>
    <tr>
        <td></td>
        <td colspan="16" style="font-family: Calibri; font-size: 14px; font-weight: bold; text-align: center; vertical-align: middle;">
            LAPORAN TAHUNAN SERANGAN OPT TANAMAN PERKEBUNAN PROVINSI JAMBI TAHUN {{ $tahun }}
        </td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2" style="font-weight: bold;">Provinsi</td>
        <td colspan="14">: Jambi</td>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <td colspan="2" style="font-weight: bold;">Tahun Laporan</td>
        <td colspan="14">: {{ $tahun }} (Februari {{ $tahun }} – Januari {{ $tahun + 1 }})</td>
        <td></td>
    </tr>
    <tr><td colspan="18" style="height: 10px;"></td></tr>
    <tr>
        <td></td>
        <th rowspan="2" style="{{ $headerStyle }}">No</th>
        <th rowspan="2" style="{{ $headerStyle }}">Kabupaten/Kota</th>
        <th rowspan="2" style="{{ $headerStyle }}">Jenis Komoditas</th>
        <th rowspan="2" style="{{ $headerStyle }}">Luas Komoditas (Ha)</th>
        <th rowspan="2" style="{{ $headerStyle }}">Jenis OPT</th>
        <th rowspan="2" style="{{ $headerStyle }}">Luas Pengendalian (Ha)</th>
        <th colspan="3" style="{{ $headerStyle }}">Luas Serangan (Ha)</th>
        <th colspan="5" style="{{ $headerStyle }}">Luas Pengendalian (Ha)</th>
        <th rowspan="2" style="{{ $headerStyle }}">Cara Pengendalian</th>
        <th rowspan="2" style="{{ $headerStyle }}">Kerugian Hasil (Rp 000)</th>
        <td></td>
    </tr>
    <tr>
        <td></td>
        <th style="{{ $headerStyle }}">Ringan</th>
        <th style="{{ $headerStyle }}">Berat</th>
        <th style="{{ $headerStyle }}">Jumlah</th>
        <th style="{{ $headerStyle }}">APBD I</th>
        <th style="{{ $headerStyle }}">APBD II</th>
        <th style="{{ $headerStyle }}">Masyarakat</th>
        <th style="{{ $headerStyle }}">APBN</th>
        <th style="{{ $headerStyle }}">Jumlah</th>
        <td></td>
    </tr>
    @forelse($data as $row)
        <tr>
            <td></td>
            <td style="{{ $numberStyle }}">{{ $loop->iteration }}</td>
            <td style="{{ $dataStyle }}">{{ $row->kabupaten }}</td>
            <td style="{{ $dataStyle }}">{{ $row->komoditas }}</td>
            <td style="{{ $numberStyle }}">{{ $row->luas_komoditi_ha }}</td>
            <td style="{{ $dataStyle }}">{{ $row->opt }}{{ $row->jenis_opt !== '' ? ' — '.$row->jenis_opt : '' }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_jumlah }}</td>
            <td style="{{ $numberStyle }}">{{ $row->serangan_ringan }}</td>
            <td style="{{ $numberStyle }}">{{ $row->serangan_berat }}</td>
            <td style="font-weight: bold; {{ $numberStyle }}">{{ $row->serangan_jumlah }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_apbd_prov }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_apbd_kab }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_masyarakat }}</td>
            <td style="{{ $numberStyle }}">{{ $row->kendali_apbn }}</td>
            <td style="font-weight: bold; {{ $numberStyle }}">{{ $row->kendali_jumlah }}</td>
            <td style="{{ $dataStyle }}">{{ $row->cara_pengendalian }}</td>
            <td style="{{ $numberStyle }}"></td>
            <td></td>
        </tr>
    @empty
        <tr>
            <td></td>
            <td colspan="16" style="{{ $numberStyle }}">Tidak ada data pada tahun {{ $tahun }}.</td>
            <td></td>
        </tr>
    @endforelse
    @for ($i = 0; $i < 5; $i++)
        <tr>
            <td></td>
            @for ($column = 0; $column < 16; $column++)
                <td style="{{ $dataStyle }}"></td>
            @endfor
            <td></td>
        </tr>
    @endfor
    <tr><td colspan="18" style="height: 10px;"></td></tr>
    <tr>
        <td colspan="10"></td>
        <td colspan="7" style="text-align: center;">Provinsi Jambi, {{ now()->translatedFormat('d F Y') }}</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="10"></td>
        <td colspan="7" style="text-align: center;">Pejabat Berwenang,</td>
        <td></td>
    </tr>
    <tr><td colspan="18" style="height: 60px;"></td></tr>
    <tr>
        <td colspan="10"></td>
        <td colspan="7" style="text-align: center;">( ........................................ )</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="10"></td>
        <td colspan="7" style="text-align: center;">NIP. ........................................</td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">Catatan:</td>
        <td colspan="15"></td>
        <td></td>
    </tr>
</table>
