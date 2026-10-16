@php
    $pengamatan = $data->last();
    $kabupaten_nama = 'Rokan Hilir';
    
    if ($pengamatan && $pengamatan->uppt && $pengamatan->uppt->kabupaten) {
        $kabupaten_nama = $pengamatan->uppt->kabupaten->nama_kabupaten;
    } elseif ($data->isEmpty() && !empty($uppt_id)) {
        $uppt = \App\Models\Uppt::with('kabupaten')->find($uppt_id);
        if ($uppt && $uppt->kabupaten) {
            $kabupaten_nama = $uppt->kabupaten->nama_kabupaten;
        }
    }
    
    $petugas_nama = $pengamatan?->user?->name ?? Auth::user()->name;
    $tanggal_pengamatan = $pengamatan && $pengamatan->tanggal_pengamatan ? \Carbon\Carbon::parse($pengamatan->tanggal_pengamatan)->translatedFormat('d F Y') : now()->translatedFormat('d F Y');
@endphp

<table>
    <!-- Bantalan atas agar mulai dari A3 -->
    <tr><td colspan="16"></td></tr>
    <tr><td colspan="16"></td></tr>

    <!-- Baris Judul (Sekarang di Baris 3) -->
    <tr>
        <td></td> <!-- A3 -->
        <td colspan="15" style="font-weight: bold; text-align: center; font-size: 14px;">LAPORAN {{ strtoupper($jenis) }} SERANGAN OPT TANAMAN PERKEBUNAN</td>
    </tr>
    <tr>
        <td></td> <!-- A2 -->
        <td colspan="2">Wilayah Pengamatan</td> <!-- B2:C2 -->
        <td colspan="13">: {{ empty($uppt_id) ? 'Semua Wilayah' : ($data->first()->uppt->nama_uppt ?? '-') }}</td>
    </tr>
    <tr>
        <td></td> <!-- A3 -->
        <td colspan="2">Kabupaten</td> <!-- B3:C3 -->
        <td colspan="13">: {{ $kabupaten_nama }}</td>
    </tr>
    <tr>
        <td></td> <!-- A4 -->
        <td colspan="2">Periode Laporan</td> <!-- B4:C4 -->
        <td colspan="13">:
            @if($jenis == 'bulanan') 
                {{ \Carbon\Carbon::create()->month((int)$bulan)->translatedFormat('F') }} {{ $tahun }}
            @elseif($jenis == 'triwulan')
                Kuartal {{ $triwulan }} Tahun {{ $tahun }}
            @else
                Tahun {{ $tahun }}
            @endif
        </td> <!-- D4:O4 -->
    </tr>
    <tr>
        <td colspan="16"></td>
    </tr>
    <!-- Table Header Row 1 (Row 6) -->
    <tr>
        <td></td> <!-- A6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">No</td> <!-- B6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Wilayah Pengamatan / Kecamatan</td> <!-- C6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Jenis Komodtas</td> <!-- D6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Luas Komoditi (Ha)</td> <!-- E6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Jenis OPT</td> <!-- F6 -->
        <td colspan="3" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Luas Serangan (Ha)</td>
        <td colspan="5" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Luas Pengendalian (Ha)</td>
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Kondisi Serangan (Ha)</td>
    </tr>
    <!-- Table Header Row 2 (Row 7) -->
    <tr>
        <td></td> <!-- A7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Ringan</td>
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Berat</td>
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Jumlah</td>
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">APBD Kabupaten</td> <!-- K7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">APBPD Provinsi</td> <!-- L7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Masyarakat</td> <!-- M7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">APBN</td>
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Jumlah</td>
    </tr>
    <!-- Data Rows -->
    @foreach($data as $d)
    <tr>
        <td></td> <!-- A -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ $loop->iteration }}</td> <!-- B -->
        <td style="vertical-align: middle; border: 1px solid #000000;">{{ $d->uppt->nama_uppt ?? '-' }}{{ $d->kecamatan ? ' / ' . $d->kecamatan->nama_kecamatan : '' }}</td> <!-- C -->
        <td style="vertical-align: middle; border: 1px solid #000000;">{{ $d->komoditas->nama_komoditas ?? '-' }}</td> <!-- D -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->luas_komoditi_ha) }}</td> <!-- E -->
        <td style="vertical-align: middle; border: 1px solid #000000;">{{ $d->opt->nama_opt ?? '-' }}{{ $d->jenisOpt ? ' — '.$d->jenisOpt->nama_jenis_opt : '' }}</td> <!-- F -->
        
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->serangan_ringan) }}</td>
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->serangan_berat) }}</td>
        <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #000000;">{{ floatval($d->serangan_jumlah) }}</td>
        
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_apbd_kab) }}</td> <!-- K -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_apbd_prov) }}</td> <!-- L -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_masyarakat) }}</td> <!-- M -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_apbn) }}</td> <!-- N -->
        <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #000000;">{{ floatval($d->kendali_jumlah) }}</td>
        
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ number_format($d->kondisi_serangan_ha, 2) }}</td>
    </tr>
    @endforeach
    
    <!-- Empty padding rows as requested by format -->
    @for($i=0; $i<5; $i++)
    <tr>
        <td></td>
        @for($column=0; $column<15; $column++)<td style="border: 1px solid #000000;"></td>@endfor
    </tr>
    @endfor

    <tr><td colspan="16"></td></tr>
    
    <!-- Footer Signatures -->
    <tr>
        <td colspan="12"></td>
        <td colspan="4" style="text-align: center;">{{ $kabupaten_nama }}, {{ $tanggal_pengamatan }}</td>
    </tr>
    <tr>
        <td colspan="12"></td>
        <td colspan="4" style="text-align: center;">Petugas Pengamat,</td>
    </tr>
    <tr><td colspan="16"></td></tr>
    <tr><td colspan="16"></td></tr>
    <tr><td colspan="16"></td></tr>
    <tr>
        <td colspan="12"></td>
        <td colspan="4" style="text-align: center;">( {{ $petugas_nama }} )</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">Catatan :</td>
        <td colspan="14"></td>
    </tr>
</table>
