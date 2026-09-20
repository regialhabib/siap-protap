<table>
    <!-- Bantalan atas agar mulai dari A3 -->
    <tr><td colspan="15"></td></tr>
    <tr><td colspan="15"></td></tr>

    <!-- Baris Judul (Sekarang di Baris 3) -->
    <tr>
        <td></td> <!-- A3 -->
        <td colspan="14" style="font-weight: bold; text-align: center; font-size: 14px;">LAPORAN {{ strtoupper($jenis) }} SERANGAN OPT TANAMAN PERKEBUNAN</td> <!-- B3:O3 -->
    </tr>
    <tr>
        <td></td> <!-- A2 -->
        <td colspan="2">Wilayah Pengamatan</td> <!-- B2:C2 -->
        <td colspan="12">: {{ empty($uppt_id) ? 'Semua Wilayah' : ($data->first()->uppt->nama_uppt ?? '-') }}</td> <!-- D2:O2 -->
    </tr>
    <tr>
        <td></td> <!-- A3 -->
        <td colspan="2">Kabupaten</td> <!-- B3:C3 -->
        <td colspan="12">: Rokan Hilir</td> <!-- D3:O3 -->
    </tr>
    <tr>
        <td></td> <!-- A4 -->
        <td colspan="2">Periode Laporan</td> <!-- B4:C4 -->
        <td colspan="12">: 
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
        <td colspan="15"></td> <!-- A5:O5 empty row like in original? Wait, row 5 is empty -->
    </tr>
    <!-- Table Header Row 1 (Row 6) -->
    <tr>
        <td></td> <!-- A6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">No</td> <!-- B6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Wilayah Pengamatan/Kecamatan</td> <!-- C6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Jenis Komodtas</td> <!-- D6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Luas Komoditi (Ha)</td> <!-- E6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Jenis OPT</td> <!-- F6 -->
        <td colspan="4" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Luas Serangan</td> <!-- G6:J6 -->
        <td colspan="4" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Luas Pengendalian (Ha)</td> <!-- K6:N6 -->
        <td rowspan="2" style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Kondisi serangan</td> <!-- O6 -->
    </tr>
    <!-- Table Header Row 2 (Row 7) -->
    <tr>
        <td></td> <!-- A7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Ringan</td> <!-- G7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Sedang</td> <!-- H7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Berat</td> <!-- I7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Jumlah</td> <!-- J7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">APBD Kabupaten</td> <!-- K7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">APBPD Provinsi</td> <!-- L7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">Masyarakat</td> <!-- M7 -->
        <td style="font-weight: bold; text-align: center; vertical-align: middle; border: 1px solid #000000; background-color: #f2f2f2;">APBN</td> <!-- N7 -->
    </tr>
    <!-- Data Rows -->
    @foreach($data as $d)
    <tr>
        <td></td> <!-- A -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ $loop->iteration }}</td> <!-- B -->
        <td style="vertical-align: middle; border: 1px solid #000000;">{{ $d->uppt->nama_uppt ?? '-' }}</td> <!-- C -->
        <td style="vertical-align: middle; border: 1px solid #000000;">{{ $d->komoditas->nama_komoditas ?? '-' }}</td> <!-- D -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->luas_komoditi_ha) }}</td> <!-- E -->
        <td style="vertical-align: middle; border: 1px solid #000000;">{{ $d->opt->nama_opt ?? '-' }}</td> <!-- F -->
        
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->serangan_ringan) }}</td> <!-- G -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->serangan_sedang) }}</td> <!-- H -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->serangan_berat) }}</td> <!-- I -->
        <td style="text-align: center; vertical-align: middle; font-weight: bold; border: 1px solid #000000;">{{ floatval($d->serangan_jumlah) }}</td> <!-- J -->
        
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_apbd_kab) }}</td> <!-- K -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_apbd_prov) }}</td> <!-- L -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_masyarakat) }}</td> <!-- M -->
        <td style="text-align: center; vertical-align: middle; border: 1px solid #000000;">{{ floatval($d->kendali_apbn) }}</td> <!-- N -->
        
        <td style="vertical-align: middle; border: 1px solid #000000;">{{ $d->kondisi_serangan ?: '-' }}</td> <!-- O -->
    </tr>
    @endforeach
    
    <!-- Empty padding rows as requested by format -->
    @for($i=0; $i<5; $i++)
    <tr>
        <td></td>
        <td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td>
        <td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td>
        <td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td>
        <td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td>
        <td style="border: 1px solid #000000;"></td><td style="border: 1px solid #000000;"></td>
    </tr>
    @endfor

    <tr><td colspan="15"></td></tr>
    
    <!-- Footer Signatures -->
    <tr>
        <td colspan="11"></td>
        <td colspan="4" style="text-align: center;">..........., .......... 20....</td>
    </tr>
    <tr>
        <td colspan="11"></td>
        <td colspan="4" style="text-align: center;">Petugas Pengamat,</td>
    </tr>
    <tr><td colspan="15"></td></tr>
    <tr><td colspan="15"></td></tr>
    <tr><td colspan="15"></td></tr>
    <tr>
        <td colspan="11"></td>
        <td colspan="4" style="text-align: center;">( ................................................. )</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight: bold;">Catatan :</td>
        <td colspan="13"></td>
    </tr>
</table>
