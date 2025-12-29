<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $template_nama ?? 'Dokumen' }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 2cm;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 11pt;
            margin: 3px 0;
        }
        .content {
            margin-top: 20px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 10px;
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table td {
            padding: 5px;
            vertical-align: top;
        }
        table td.label {
            width: 30%;
            font-weight: bold;
        }
        .signature-section {
            margin-top: 40px;
        }
        .signature-table {
            width: 100%;
            border: 1px solid #000;
        }
        .signature-table th,
        .signature-table td {
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }
        .signature-img {
            max-width: 150px;
            max-height: 75px;
            display: block;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>BERITA ACARA SEMINAR</h1>
        <p>Nomor: {{ $seminar_no_surat ?? '-' }}</p>
        <p>Tanggal: {{ $seminar_tanggal ?? '-' }}</p>
    </div>

    <div class="content">
        <div class="section">
            <div class="section-title">Data Mahasiswa</div>
            <table>
                <tr>
                    <td class="label">Nama</td>
                    <td>: {{ $mahasiswa_nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">NPM</td>
                    <td>: {{ $mahasiswa_npm ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Program Studi</td>
                    <td>: {{ $mahasiswa_prodi ?? '-' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Data Seminar</div>
            <table>
                <tr>
                    <td class="label">Jenis Seminar</td>
                    <td>: {{ $seminar_jenis_nama ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Judul</td>
                    <td>: {!! $seminar_judul ?? '-' !!}</td>
                </tr>
                <tr>
                    <td class="label">Lokasi</td>
                    <td>: {{ $seminar_lokasi ?? '-' }}</td>
                </tr>
            </table>
        </div>

        @if(isset($nilai_akhir) && !empty($nilai_akhir))
        <div class="section">
            <div class="section-title">Nilai</div>
            <table>
                <tr>
                    <td class="label">Nilai Akhir</td>
                    <td>: {{ $nilai_akhir }} ({{ $nilai_huruf ?? '-' }})</td>
                </tr>
            </table>
        </div>
        @endif

        <div class="signature-section">
            <div class="section-title">Tim Penguji</div>
            <table class="signature-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Jabatan</th>
                        <th width="35%">Nama / NIP</th>
                        <th width="20%">Tanda Tangan</th>
                        <th width="20%">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($p1_nama))
                    <tr>
                        <td>1</td>
                        <td>Pembimbing 1</td>
                        <td>
                            {{ $p1_nama }}<br>
                            <small>NIP: {{ $p1_nip ?? '-' }}</small>
                        </td>
                        <td>
                            @if(!empty($p1_ttd))
                                <img src="{{ $p1_ttd }}" class="signature-img" alt="TTD P1">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $nilai_p1 ?? '-' }}</td>
                    </tr>
                    @endif

                    @if(isset($p2_nama))
                    <tr>
                        <td>2</td>
                        <td>Pembimbing 2</td>
                        <td>
                            {{ $p2_nama }}<br>
                            <small>NIP: {{ $p2_nip ?? '-' }}</small>
                        </td>
                        <td>
                            @if(!empty($p2_ttd))
                                <img src="{{ $p2_ttd }}" class="signature-img" alt="TTD P2">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $nilai_p2 ?? '-' }}</td>
                    </tr>
                    @endif

                    @if(isset($pembahas_nama))
                    <tr>
                        <td>3</td>
                        <td>Pembahas</td>
                        <td>
                            {{ $pembahas_nama }}<br>
                            <small>NIP: {{ $pembahas_nip ?? '-' }}</small>
                        </td>
                        <td>
                            @if(!empty($pembahas_ttd))
                                <img src="{{ $pembahas_ttd }}" class="signature-img" alt="TTD Pembahas">
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $nilai_pembahas ?? '-' }}</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 10pt; color: #666;">
        <p>Dokumen ini dibuat secara otomatis oleh sistem</p>
    </div>
</body>
</html>
