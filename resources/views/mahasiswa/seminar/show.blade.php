@extends('layouts.app')

@section('title', 'Seminar Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Seminar Details</h1>
            <div class="flex space-x-2 justify-center sm:justify-start">
                <a href="{{ route('mahasiswa.dashboard') }}" class="btn-pill btn-pill-secondary">
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Jenis Seminar</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->seminarJenis->nama ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Status</h3>
                    <p class="mt-1">
                        <span class="inline-flex font-semibold rounded-full px-3 py-1 text-xs leading-5
                            @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                            @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                            @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                            @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                            @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                            @endif">
                            @if($seminar->status == 'belum_lengkap')
                                Belum Lengkap
                            @else
                                {{ ucwords(str_replace('_', ' ', $seminar->status)) }}
                            @endif
                        </span>
                    </p>
                </div>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-500">Judul</h3>
                <div class="mt-1 text-gray-900 whitespace-pre-wrap">{!! $seminar->judul !!}</div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Tanggal</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('d F Y') : 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Waktu</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->waktu_mulai }} - {{ $seminar->waktu_selesai }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Lokasi</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->lokasi }}</p>
                </div>
            </div>




            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Pembimbing 1</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->p1Dosen->nama ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Pembimbing 2</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->p2Dosen->nama ?? 'N/A' }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Pembahas</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->pembahasDosen->nama ?? 'N/A' }}</p>
                </div>
            </div>

            @if($seminar->berkas_syarat && is_array($seminar->berkas_syarat) && count($seminar->berkas_syarat) > 0)
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Berkas Syarat</h3>
                    <div class="mt-2 text-sm">
                        @foreach($seminar->berkas_syarat as $key => $path)
                            @if(is_string($path) && $path !== '')
                                <div class="flex items-center py-1">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    <a href="{{ route('mahasiswa.seminar.files.show', ['path' => $path]) }}" 
                                       target="_blank" 
                                       data-no-ajax
                                       class="text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ is_string($key) ? ($key . ': ') : '' }}{{ basename($path) }}
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            @use('App\Helpers\Terbilang')
            @php
                $nilaiP1 = $seminar->nilai->first(function ($n) use ($seminar) {
                    return $n->jenis_penilai === 'p1' && $n->dosen_id == $seminar->p1_dosen_id;
                });
                $nilaiP2 = $seminar->nilai->first(function ($n) use ($seminar) {
                    return $n->jenis_penilai === 'p2' && $n->dosen_id == $seminar->p2_dosen_id;
                });
                $nilaiPembahas = $seminar->nilai->first(function ($n) use ($seminar) {
                    return $n->jenis_penilai === 'pembahas' && $n->dosen_id == $seminar->pembahas_dosen_id;
                });
            @endphp

            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Rekapitulasi Nilai Seminar</h3>
                
                @if($nilaiP1 || $nilaiP2 || $nilaiPembahas)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <!-- Pembimbing 1 -->
                        <div class="border-2 border-blue-200 rounded-lg p-5 {{ $nilaiP1 ? 'bg-blue-50' : 'bg-gray-50' }}">
                            <h4 class="font-semibold text-lg text-blue-900 mb-3">Pembimbing 1</h4>
                            <p class="text-sm text-gray-700 mb-2">{{ $seminar->p1Dosen->nama ?? 'N/A' }}</p>
                            @if($nilaiP1)
                                @if($nilaiP1->assessmentScores->count() > 0)
                                    <div class="space-y-2 mb-4 border-t border-blue-200 pt-3 mt-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                                        @foreach($nilaiP1->assessmentScores as $score)
                                            <div class="flex justify-between text-sm gap-2">
                                                <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                                <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="bg-white border-2 border-blue-300 rounded-lg p-4 mb-3">
                                    <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                                    <p class="text-3xl font-bold text-blue-700 break-words">{{ number_format($nilaiP1->nilai_angka, 2) }}</p>
                                    <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                                        {{ ucwords(Terbilang::convert($nilaiP1->nilai_angka)) }}
                                    </p>
                                </div>
                                @if($nilaiP1->catatan)
                                    <div class="text-sm text-gray-700 bg-white p-3 rounded border border-blue-200">
                                        <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                                        <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiP1->catatan }}</p>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                            @endif
                        </div>

                        <!-- Pembimbing 2 -->
                        <div class="border-2 border-green-200 rounded-lg p-5 {{ $nilaiP2 ? 'bg-green-50' : 'bg-gray-50' }}">
                            <h4 class="font-semibold text-lg text-green-900 mb-3">Pembimbing 2</h4>
                            <p class="text-sm text-gray-700 mb-2">{{ $seminar->p2Dosen->nama ?? 'N/A' }}</p>
                            @if($nilaiP2)
                                @if($nilaiP2->assessmentScores->count() > 0)
                                    <div class="space-y-2 mb-4 border-t border-green-200 pt-3 mt-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                                        @foreach($nilaiP2->assessmentScores as $score)
                                            <div class="flex justify-between text-sm gap-2">
                                                <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                                <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="bg-white border-2 border-green-300 rounded-lg p-4 mb-3">
                                    <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                                    <p class="text-3xl font-bold text-green-700 break-words">{{ number_format($nilaiP2->nilai_angka, 2) }}</p>
                                    <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                                        {{ ucwords(Terbilang::convert($nilaiP2->nilai_angka)) }}
                                    </p>
                                </div>
                                @if($nilaiP2->catatan)
                                    <div class="text-sm text-gray-700 bg-white p-3 rounded border border-green-200">
                                        <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                                        <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiP2->catatan }}</p>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                            @endif
                        </div>

                        <!-- Pembahas -->
                        <div class="border-2 border-purple-200 rounded-lg p-5 {{ $nilaiPembahas ? 'bg-purple-50' : 'bg-gray-50' }}">
                            <h4 class="font-semibold text-lg text-purple-900 mb-3">Pembahas</h4>
                            <p class="text-sm text-gray-700 mb-2">{{ $seminar->pembahasDosen->nama ?? 'N/A' }}</p>
                            @if($nilaiPembahas)
                                @if($nilaiPembahas->assessmentScores->count() > 0)
                                    <div class="space-y-2 mb-4 border-t border-purple-200 pt-3 mt-3">
                                        <p class="text-xs font-semibold text-gray-600 uppercase">Aspek Penilaian:</p>
                                        @foreach($nilaiPembahas->assessmentScores as $score)
                                            <div class="flex justify-between text-sm gap-2">
                                                <span class="text-gray-600 break-words flex-1">{{ $score->assessmentAspect->nama_aspek }}</span>
                                                <span class="font-medium text-gray-900 flex-shrink-0">{{ $score->nilai }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <div class="bg-white border-2 border-purple-300 rounded-lg p-4 mb-3">
                                    <p class="text-sm text-gray-600 mb-1">Nilai Akhir:</p>
                                    <p class="text-3xl font-bold text-purple-700 break-words">{{ number_format($nilaiPembahas->nilai_angka, 2) }}</p>
                                    <p class="text-sm italic text-gray-600 mt-2 break-words overflow-wrap-anywhere">
                                        {{ ucwords(Terbilang::convert($nilaiPembahas->nilai_angka)) }}
                                    </p>
                                </div>
                                @if($nilaiPembahas->catatan)
                                    <div class="text-sm text-gray-700 bg-white p-3 rounded border border-purple-200">
                                        <p class="font-semibold text-xs text-gray-600 mb-1">Catatan:</p>
                                        <p class="italic break-words overflow-wrap-anywhere">{{ $nilaiPembahas->catatan }}</p>
                                    </div>
                                @endif
                            @else
                                <p class="text-gray-500 italic text-center py-4">Belum dinilai</p>
                            @endif
                        </div>
                    </div>

                    @php
                        $p1Percentage = $seminar->seminarJenis->p1_weight ?? 35;
                        $p2Percentage = $seminar->seminarJenis->p2_weight ?? 35;
                        $pembahasPercentage = $seminar->seminarJenis->pembahas_weight ?? 30;
                        $finalScore = $seminar->calculateWeightedScore();
                    @endphp
                    
                    <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-400 rounded-lg p-6">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="flex-shrink-0">
                                <h4 class="text-lg font-semibold text-gray-800 mb-2">Nilai Akhir Keseluruhan</h4>
                                <p class="text-sm text-gray-600">
                                    P1 ({{ $p1Percentage }}%) + P2 ({{ $p2Percentage }}%) + Pembahas ({{ $pembahasPercentage }}%)
                                </p>
                            </div>
                            <div class="text-left md:text-right flex-shrink min-w-0">
                                <p class="text-4xl md:text-5xl font-bold text-orange-600 break-words">{{ number_format($finalScore, 2) }}</p>
                                <p class="text-sm md:text-base italic text-gray-700 mt-2 break-words overflow-wrap-anywhere">
                                    {{ ucwords(Terbilang::convert($finalScore)) }}
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <p class="text-gray-600">Belum ada penilaian untuk seminar ini.</p>
                    </div>
                @endif
            </div>

            <!-- Signature Details -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-medium text-gray-500">Tanda Tangan Elektronik</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    @php
                        $signatures = $seminar->signatures->keyBy('jenis_penilai');
                        $p1Signature = $signatures['p1'] ?? null;
                        $p2Signature = $signatures['p2'] ?? null;
                        $pembahasSignature = $signatures['pembahas'] ?? null;
                    @endphp

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembimbing 1</h4>
                        @if($p1Signature)
                            <div class="mt-2 text-center">
                                <div class="py-4 text-green-600 font-bold border border-green-200 bg-green-50 rounded">
                                    <i class="fas fa-check-circle"></i> DIGITAL SIGNED
                                </div>
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $p1Signature->tanggal_ttd ? $p1Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500 italic mt-2">Belum ditandatangani</p>
                        @endif
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembimbing 2</h4>
                        @if($p2Signature)
                            <div class="mt-2 text-center">
                                <div class="py-4 text-green-600 font-bold border border-green-200 bg-green-50 rounded">
                                    <i class="fas fa-check-circle"></i> DIGITAL SIGNED
                                </div>
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $p2Signature->tanggal_ttd ? $p2Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500 italic mt-2">Belum ditandatangani</p>
                        @endif
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembahas</h4>
                        @if($pembahasSignature)
                            <div class="mt-2 text-center">
                                <div class="py-4 text-green-600 font-bold border border-green-200 bg-green-50 rounded">
                                    <i class="fas fa-check-circle"></i> DIGITAL SIGNED
                                </div>
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $pembahasSignature->tanggal_ttd ? $pembahasSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500 italic mt-2">Belum ditandatangani</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
