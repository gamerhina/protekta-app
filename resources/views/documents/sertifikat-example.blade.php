<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Seminar</title>
    <style>
        @page {
            margin: 20mm;
        }
        
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 16pt;
            font-weight: normal;
            margin: 0 0 5px 0;
        }
        
        .header p {
            font-size: 11pt;
            margin: 2px 0;
        }
        
        .content {
            margin: 20px 0;
        }
        
        .content table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .content table td {
            padding: 5px;
            vertical-align: top;
        }
        
        .content table td:first-child {
            width: 150px;
        }
        
        .content table td:nth-child(2) {
            width: 10px;
        }
        
        .signatures {
            margin-top: 50px;
            display: table;
            width: 100%;
        }
        
        .signature-box {
            display: table-cell;
            text-align: center;
            padding: 10px;
            width: 33.33%;
        }
        
        .signature-box p {
            margin: 5px 0;
        }
        
        .signature-box .label {
            font-weight: bold;
            margin-bottom: 60px;
        }
        
        .signature-box img {
            width: 150px;
            height: 75px;
            object-fit: contain;
            display: block;
            margin: 0 auto 10px auto;
        }
        
        .signature-box .name {
            font-weight: bold;
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 180px;
            padding-bottom: 2px;
        }
        
        .signature-box .nip {
            font-size: 11pt;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        .center {
            text-align: center;
        }
        
        .bold {
            font-weight: bold;
        }
        
        .italic {
            font-style: italic;
        }
        
        .underline {
            text-decoration: underline;
        }
        
        .nilai-box {
            border: 2px solid #000;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        
        .nilai-box h3 {
            margin: 0 0 10px 0;
            font-size: 14pt;
        }
        
        .nilai-box .nilai-angka {
            font-size: 24pt;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .nilai-box .nilai-huruf {
            font-size: 36pt;
            font-weight: bold;
            color: #0066cc;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    {{-- HALAMAN 1: SERTIFIKAT --}}
    <div class="page">
        <div class="header">
            <h1>UNIVERSITAS XYZ</h1>
            <h2>FAKULTAS TEKNOLOGI INFORMASI</h2>
            <p>Jl. Contoh No. 123, Jakarta 12345</p>
            <p>Telp: (021) 123-4567 | Email: info@universitas.ac.id</p>
        </div>
        
        <div class="content">
            <h2 class="center">SERTIFIKAT {{ strtoupper($seminar_jenis_nama ?? 'SEMINAR') }}</h2>
            <p class="center">Nomor: {{ $seminar_no_surat ?? '-' }}</p>
            
            <p style="margin-top: 30px;">Dengan ini menerangkan bahwa:</p>
            
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong>{{ $mahasiswa_nama ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td>NPM</td>
                    <td>:</td>
                    <td>{{ $mahasiswa_npm ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Program Studi</td>
                    <td>:</td>
                    <td>{{ $mahasiswa_prodi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Judul</td>
                    <td>:</td>
                    <td><em>{{ $seminar_judul ?? '-' }}</em></td>
                </tr>
            </table>
            
            <p>Telah melaksanakan <strong>{{ $seminar_jenis_nama ?? 'Seminar' }}</strong> pada:</p>
            
            <table>
                <tr>
                    <td>Hari/Tanggal</td>
                    <td>:</td>
                    <td>{{ $seminar_hari ?? '-' }}, {{ $seminar_tanggal ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Waktu</td>
                    <td>:</td>
                    <td>{{ $seminar_waktu_mulai ?? '-' }} WIB</td>
                </tr>
                <tr>
                    <td>Tempat</td>
                    <td>:</td>
                    <td>{{ $seminar_lokasi ?? '-' }}</td>
                </tr>
            </table>
            
            @if(isset($nilai_akhir) && $nilai_akhir)
            <div class="nilai-box">
                <h3>NILAI AKHIR</h3>
                <div class="nilai-angka">{{ $nilai_akhir }}</div>
                <div class="nilai-huruf">{{ $nilai_huruf ?? '-' }}</div>
            </div>
            @endif
            
            <p style="margin-top: 30px;">Demikian sertifikat ini dibuat untuk dapat digunakan sebagaimana mestinya.</p>
        </div>
        
        <div class="signatures">
            <div class="signature-box">
                <p class="label">Pembimbing 1</p>
                @if(!empty($p1_ttd))
                <img src="{{ $p1_ttd }}" alt="TTD Pembimbing 1">
                @else
                <div style="height: 75px;"></div>
                @endif
                <p class="name">{{ $p1_nama ?? '-' }}</p>
                <p class="nip">NIP. {{ $p1_nip ?? '-' }}</p>
            </div>
            
            <div class="signature-box">
                <p class="label">Pembimbing 2</p>
                @if(!empty($p2_ttd))
                <img src="{{ $p2_ttd }}" alt="TTD Pembimbing 2">
                @else
                <div style="height: 75px;"></div>
                @endif
                <p class="name">{{ $p2_nama ?? '-' }}</p>
                <p class="nip">NIP. {{ $p2_nip ?? '-' }}</p>
            </div>
            
            <div class="signature-box">
                <p class="label">Pembahas</p>
                @if(!empty($pembahas_ttd))
                <img src="{{ $pembahas_ttd }}" alt="TTD Pembahas">
                @else
                <div style="height: 75px;"></div>
                @endif
                <p class="name">{{ $pembahas_nama ?? '-' }}</p>
                <p class="nip">NIP. {{ $pembahas_nip ?? '-' }}</p>
            </div>
        </div>
    </div>
    
    {{-- PAGE BREAK --}}
    <div class="page-break"></div>
    
    {{-- HALAMAN 2: LEMBAR PERSETUJUAN (Contoh gambar dipanggil 2x) --}}
    <div class="page">
        <div class="header">
            <h1>LEMBAR PERSETUJUAN</h1>
            <h2>{{ strtoupper($seminar_jenis_nama ?? 'SEMINAR') }}</h2>
        </div>
        
        <div class="content">
            <table>
                <tr>
                    <td>Nama</td>
                    <td>:</td>
                    <td><strong>{{ $mahasiswa_nama ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td>NPM</td>
                    <td>:</td>
                    <td>{{ $mahasiswa_npm ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Judul</td>
                    <td>:</td>
                    <td><em>{{ $seminar_judul ?? '-' }}</em></td>
                </tr>
            </table>
            
            <p style="margin-top: 30px;">Telah disetujui dan diuji pada {{ $seminar_tanggal ?? '-' }}</p>
            
            <h3 style="margin-top: 40px;">PEMBIMBING</h3>
            
            <table style="margin-top: 20px;">
                <tr>
                    <td style="width: 50%; vertical-align: top;">
                        <p><strong>Pembimbing 1</strong></p>
                        @if(!empty($p1_ttd))
                        {{-- PENGGUNAAN GAMBAR YANG SAMA UNTUK KEDUA KALINYA --}}
                        <img src="{{ $p1_ttd }}" alt="TTD Pembimbing 1" style="width: 150px; height: 75px; object-fit: contain; display: block; margin: 20px 0;">
                        @else
                        <div style="height: 75px; margin: 20px 0;"></div>
                        @endif
                        <p class="bold">{{ $p1_nama ?? '-' }}</p>
                        <p>NIP. {{ $p1_nip ?? '-' }}</p>
                    </td>
                    <td style="width: 50%; vertical-align: top;">
                        <p><strong>Pembimbing 2</strong></p>
                        @if(!empty($p2_ttd))
                        {{-- PENGGUNAAN GAMBAR YANG SAMA UNTUK KEDUA KALINYA --}}
                        <img src="{{ $p2_ttd }}" alt="TTD Pembimbing 2" style="width: 150px; height: 75px; object-fit: contain; display: block; margin: 20px 0;">
                        @else
                        <div style="height: 75px; margin: 20px 0;"></div>
                        @endif
                        <p class="bold">{{ $p2_nama ?? '-' }}</p>
                        <p>NIP. {{ $p2_nip ?? '-' }}</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 40px;">PENGUJI</h3>
            
            <div style="margin-top: 20px;">
                <p><strong>Pembahas</strong></p>
                @if(!empty($pembahas_ttd))
                {{-- PENGGUNAAN GAMBAR YANG SAMA UNTUK KETIGA KALINYA --}}
                <img src="{{ $pembahas_ttd }}" alt="TTD Pembahas" style="width: 150px; height: 75px; object-fit: contain; display: block; margin: 20px 0;">
                @else
                <div style="height: 75px; margin: 20px 0;"></div>
                @endif
                <p class="bold">{{ $pembahas_nama ?? '-' }}</p>
                <p>NIP. {{ $pembahas_nip ?? '-' }}</p>
            </div>
        </div>
    </div>
</body>
</html>
