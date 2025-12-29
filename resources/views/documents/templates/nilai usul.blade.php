<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $seminar_jenis_nama ?? 'Dokumen' }} - {{ $mahasiswa_nama ?? '' }}</title>
    <style>
        /* Auto-generated styles - customize as needed */
        body {
            font-family: 'Times New Roman', serif;
            font-size: 12pt;
            line-height: 1.6;
            margin: 2cm;
            color: #000;
        }
        
        h1, h2, h3 {
            text-align: center;
            font-weight: bold;
        }
        
        h1 {
            font-size: 16pt;
            margin-bottom: 10px;
        }
        
        h2 {
            font-size: 14pt;
            margin-bottom: 8px;
        }
        
        p {
            margin: 5px 0;
            text-align: justify;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        table td,
        table th {
            padding: 6px 8px;
            vertical-align: top;
        }
        
        /* Bordered tables (default for all) */
        table.bordered,
        table.bordered td,
        table.bordered th {
            border: 1px solid #000;
        }
        
        table.bordered th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        /* Better formatting */
        strong {
            font-weight: bold;
        }
        
        em {
            font-style: italic;
        }
        
        /* Signature images */
        img {
            display: inline-block;
            max-width: 150px;
            max-height: 75px;
        }
        
        /* Page break control for printing */
        @media print {
            .page-break {
                page-break-after: always;
            }
        }

        /* Styles imported from source DOCX */
body {font-family: 'Calibri'; font-size: 11pt; color: #000000;}
* {font-family: 'Calibri'; font-size: 11pt; color: #000000;}
a.NoteRef {text-decoration: none;}
hr {height: 1px; padding: 0; margin: 1em 0; border: 0; border-top: 1px solid #CCC;}
table {border: 1px solid black; border-spacing: 0px; width : 100%;}
td {border: 1px solid black;}
p, .Normal {margin-bottom: 10pt;}
h1 {font-size: 24pt; font-weight: bold;}
h1 {margin-top: 24pt; margin-bottom: 6pt;}
h2 {font-size: 18pt; font-weight: bold;}
h2 {margin-top: 18pt; margin-bottom: 4pt;}
h3 {font-size: 14pt; font-weight: bold;}
h3 {margin-top: 14pt; margin-bottom: 4pt;}
h4 {font-size: 12pt; font-weight: bold;}
h4 {margin-top: 12pt; margin-bottom: 2pt;}
h5 {font-weight: bold;}
h5 {margin-top: 11pt; margin-bottom: 2pt;}
h6 {font-size: 10pt; font-weight: bold;}
h6 {margin-top: 10pt; margin-bottom: 2pt;}
.Normal Table {table-layout: auto;}
.TableNormal {table-layout: auto;}
.Title {font-size: 36pt; font-weight: bold;}
.Subtitle {font-family: 'Georgia'; font-size: 24pt; color: #666666; font-style: italic;}
.a {table-layout: auto;}
.a0 {table-layout: auto;}
.a1 {table-layout: auto;}
.a2 {table-layout: auto;}
.a3 {table-layout: auto;}
.a4 {table-layout: auto;}
.Hyperlink {color: #0000FF; text-decoration: underline ;}
.Unresolved Mention {color: #605E5C;}

div > *:first-child {page-break-before: auto;}
@page page1 {size: A4 portrait; margin-right: 0.7875in; margin-left: 0.7875in; margin-top: 0.8125in; margin-bottom: 1in; }

    </style>
</head>
<body>
    {{-- Auto-generated from: nilai usul.docx --}}
    {{-- Feel free to customize this template! --}}
    

<div>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt; font-weight: bold;">BERITA ACARA SEMINAR USUL</span></p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">No.</span> <span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nomor_surat }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt;">/UN26.14.11/BA.SEMPRO/</span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $tahun }}</span></p>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Ketua Jurusan Proteksi Tanaman Fakultas Pertanian Unila dengan ini menerangkan bahwa pada Hari </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $hari }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt;"> Tanggal </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $tanggal_seminar }}</span><span style="font-family: 'Roboto'; font-size: 10pt; background: white;"> </span><span style="font-family: 'Times New Roman'; font-size: 12pt;">telah dilakukan seminar usul mahasiswa :</span></p>
<table class="a">
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Nama</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">NPM</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $npm }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Judul</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $judul }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Jurusan</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Proteksi Tanaman</span></p>
</td>
</tr>
</table>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Pembimbing / Pembahas</span></p>
<table class="a0">
<tr>
<td>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">No</span></p>
</td>
<td>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Nama dosen</span></p>
</td>
<td>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Jabatan</span></p>
</td>
<td>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Tanda Tangan</span></p>
</td>
</tr>
<tr>
<td>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">1</span></p>
</td>
<td style="vertical-align: center;">
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_p1 }}</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Pembimbing Utama</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 6pt;">@if(!empty($ttdp1))
    <img src="{{ $ttdp1 }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
</td>
</tr>
<tr>
<td>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">2</span></p>
</td>
<td style="vertical-align: center;">
<p style="text-align: justify; margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_p2 }}</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Pembimbing Pembantu</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 6pt;">@if(!empty($ttdp2))
    <img src="{{ $ttdp2 }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
</td>
</tr>
<tr>
<td>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">3</span></p>
</td>
<td style="vertical-align: center;">
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_pmb }}</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Pembahas</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 6pt;">@if(!empty($ttdpmb))
    <img src="{{ $ttdpmb }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
</td>
</tr>
</table>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">mahasiswa tersebut diatas dinyatakan </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt; font-weight: bold;">{{ $dinyatakan }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt;"> dengan nilai akhir rata-rata: </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt; font-weight: bold;">{{ $nilai_akhir }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt; font-weight: bold;"> </span><span style="font-family: 'Times New Roman'; font-size: 12pt;">dengan huruf mutu: </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt; font-weight: bold;">{{ $nilai_huruf }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt;"> . Seminar usul dilakukan yang bersangkutan untuk menyelesaikan proposal ( Usul Penelitian) sesuai dengan ketentuan yang berlaku di Fakultas Pertanian Unila.</span></p>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Demikian berita acara ini dibuat dengan sebenarnya apabila di kemudian hari terdapat kekeliruan dalam berita acara ini, akan diperbaiki sebagaimana mestinya.</span></p>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Mengetahui,</span>				<span style="font-family: 'Times New Roman'; font-size: 12pt;"> </span>		<span style="font-family: 'Times New Roman'; font-size: 12pt;">Bandar Lampung, </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $tanggal_pelaksanaan_seminar }}</span></p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Ketua Jurusan</span>						<span style="font-family: 'Times New Roman'; font-size: 12pt;">Pembimbing Utama</span></p>
<p style="margin-bottom: 0pt; margin-left: -0.0625in; margin-right: 0in;"><img border="0" style="width: 530px; height: 378px;" src="data:image/png;base64,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"/>					<span style="font-family: 'Times New Roman';">        </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 6pt;">@if(!empty($ttdp1))
    <img src="{{ $ttdp1 }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
<table class="a1">
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Dr. Tri Maryono, S.P., M.Si.</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_p1 }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">NIP 198002082005011002</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">NIP </span><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nip_p1 }}</span></p>
</td>
</tr>
</table>
<p>&nbsp;</p>
<div style="page-break-before: always; height: 0; margin: 0; padding: 0; overflow: hidden;">&#160;</div>
<p style="margin-bottom: 0pt; margin-left: 2in; margin-right: 0in;"><br />
</p>
<p style="margin-bottom: 0pt; margin-left: 2in; margin-right: 0in;"><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000; font-weight: bold;">NILAI SEMINAR USUL</span></p>
<p>&nbsp;</p>
<table class="a2">
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Nama</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">NPM</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $npm }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Judul</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $judul }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Jurusan</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">:</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Proteksi Tanaman</span></p>
</td>
</tr>
</table>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000;">Hari/ Tgl Sem. Usul : </span>	<span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $hari }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt;">/</span><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $tanggal_seminar }}</span></p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000;">Tim penguji, nilai dan paraf</span>	<span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000;">:</span></p>
<table class="a3">
<tr>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">No</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">Nama Dosen</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">Jabatan</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">Nilai</span></p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">(N)</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 10pt;">Bobot</span></p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 10pt;">(B)</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">NXB</span></p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">%</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">Tanda Tangan</span></p>
</td>
</tr>
<tr>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">1.  </span></p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: nil;">
<p style="margin-bottom: 0pt; margin-left: 0in; margin-right: -0.17291666666667in;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_p1 }}</span></p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 10pt;">Pembimbing Utama</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">{{ $nilai_p1 }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman';">{{ $bbtp1 }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: nil;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nialip1xbbt1 }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 6pt;">@if(!empty($ttdp1))
    <img src="{{ $ttdp1 }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
</td>
</tr>
<tr>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">2.</span></p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: nil;">
<p style="margin-bottom: 0pt; margin-left: 0in; margin-right: -0.17291666666667in;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_p2 }}</span></p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 10pt;">Pembimbing Pembantu</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">{{ $nilai_p2 }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
<p style="text-align: center; margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman';">{{ $bbtp2 }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: nil; border-right-style: nil;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nilaip2xbbt2 }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 6pt;">@if(!empty($ttdp2))
    <img src="{{ $ttdp2 }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
</td>
</tr>
<tr>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">3.</span></p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: nil;">
<p style="margin-bottom: 0pt; margin-left: 0in; margin-right: -0.17291666666667in;"><span lang='en-US' style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_pmb }}</span></p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 10pt;">Pembahas</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">{{ $nilai_pmb }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span lang='en-US' style="font-family: 'Times New Roman';">{{ $bbtp3 }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nilaipmbxbbtpmb }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 6pt;">@if(!empty($ttdpmb))
    <img src="{{ $ttdpmb }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
</td>
</tr>
<tr>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: nil;">
<p>&nbsp;</p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
</td>
<td style="border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p>&nbsp;</p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 10pt;">100 %</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: nil; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: nil;">
<p style="text-align: center; margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nilai_akhir }}</span></p>
</td>
<td style="vertical-align: center; border-top-style: solid; border-top-width: 0.2pt; border-left-style: solid; border-left-width: 0.2pt; border-bottom-style: solid; border-bottom-width: 0.2pt; border-right-style: solid; border-right-width: 0.2pt;">
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman';">(HM : </span><span style="font-family: 'Times New Roman'; font-weight: bold;">{{ $nilai_huruf }}</span><span style="font-family: 'Times New Roman';"> )</span></p>
</td>
</tr>
</table>
<p style="margin-bottom: 0pt;">	</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000;">Mahasiswa tersebut dinyatakan </span><span style="font-family: 'Times New Roman'; font-size: 12pt; font-weight: bold;">{{ $dinyatakan }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000; font-weight: bold;"> Kolokium</span><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000;"> dan selanjutnya yang bersangkutan </span><span style="font-family: 'Times New Roman'; font-size: 12pt; font-weight: bold;">{{ $diperkenankan }}</span><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000;"> untuk melanjutkan penelitian.</span></p>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt; color: #000000;">Demikan nilai mahasiswa tersebut, kami buat dengan sebenarnya.</span></p>
<p>&nbsp;</p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Mengetahui,</span>				<span style="font-family: 'Times New Roman'; font-size: 12pt;"> </span>		<span style="font-family: 'Times New Roman'; font-size: 12pt;">Bandar Lampung, </span><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $tanggal_seminar }}</span></p>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Ketua Jurusan</span>						<span style="font-family: 'Times New Roman'; font-size: 12pt;">Pembimbing Utama</span></p>
<p style="margin-bottom: 0pt; margin-left: -0.0625in; margin-right: 0in;"><img border="0" style="width: 530px; height: 378px;" src="data:image/png;base64,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"/>					<span style="font-family: 'Times New Roman';">      </span><span style="font-family: 'Times New Roman';">@if(!empty($ttdp1))
    <img src="{{ $ttdp1 }}" style="max-width: 150px; max-height: 75px;">
@endif</span></p>
<table class="a4">
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">Dr. Tri Maryono, S.P., M.Si.</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nama_p1 }}</span></p>
</td>
</tr>
<tr>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">NIP 198002082005011002</span></p>
</td>
<td>
<p style="margin-bottom: 0pt;"><span style="font-family: 'Times New Roman'; font-size: 12pt;">NIP </span><span style="font-family: 'Times New Roman'; font-size: 12pt;">{{ $nip_p1 }}</span></p>
</td>
</tr>
</table>
<p>&nbsp;</p>
</div>


    {{-- Footer --}}
    <div style="margin-top: 40px; text-align: center; font-size: 10pt; color: #666;">
        <p>Dokumen ini dibuat secara otomatis oleh sistem</p>
    </div>
</body>
</html>