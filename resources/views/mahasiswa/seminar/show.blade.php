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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Core Details (Col-span 2) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Header Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden relative">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <i class="fas fa-graduation-cap text-8xl"></i>
                    </div>
                    
                    <div class="relative">
                        <div class="flex flex-wrap items-center gap-2 mb-4">
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-full text-center">
                                {{ $seminar->seminarJenis->nama ?? 'N/A' }}
                            </span>
                            <span class="inline-flex font-bold rounded-full text-[10px] px-3 py-1 uppercase tracking-wider text-center
                                @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                                @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                                @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                                @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                                @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                                @endif">
                                {{ $seminar->status == 'belum_lengkap' ? 'Belum Lengkap' : ucwords(str_replace('_', ' ', $seminar->status)) }}
                            </span>
                        </div>

                        <h2 class="text-xl md:text-2xl font-semibold text-gray-800 leading-tight mb-4 text-center sm:text-left">
                            {!! $seminar->judul !!}
                        </h2>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fas fa-id-card text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pendaftar</p>
                                <p class="text-sm font-bold text-gray-800">{{ optional($seminar->mahasiswa)->nama ?? Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 font-mono">{{ $seminar->mahasiswa->npm ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Evaluators Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- P1 -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">P1</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembimbing 1</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}</p>
                    </div>

                    <!-- P2 -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-green-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">P2</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembimbing 2</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}</p>
                    </div>

                    <!-- PMB -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-purple-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">PMB</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembahas</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}</p>
                    </div>
                </div>

                <!-- Requirements Files -->
                @if($seminar->berkas_syarat && is_array($seminar->berkas_syarat) && count($seminar->berkas_syarat) > 0)
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-paperclip text-blue-500"></i> Berkas Persyaratan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach($seminar->berkas_syarat as $key => $path)
                            @if(is_string($path) && $path !== '')
                                <div class="group flex items-center justify-between p-3 rounded-2xl bg-gray-50 border border-transparent hover:border-blue-200 hover:bg-white transition-all">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500 group-hover:bg-red-500 group-hover:text-white transition-all">
                                            <i class="fas fa-file-pdf"></i>
                                        </div>
                                        <div class="truncate">
                                            <p class="text-xs font-bold text-gray-700 truncate capitalize">{{ str_replace('_', ' ', is_string($key) ? $key : basename($path)) }}</p>
                                            <p class="text-[10px] text-gray-400 font-mono">{{ strtoupper(pathinfo($path, PATHINFO_EXTENSION)) }} • PDF</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('mahasiswa.seminar.files.show', ['path' => $path]) }}" 
                                       target="_blank" 
                                       data-no-ajax 
                                       class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-500 hover:text-white transition-all">
                                        <i class="fas fa-external-link-alt text-[10px]"></i>
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Right Column: Schedule & Metadata -->
            <div class="space-y-6">
                <!-- Schedule Card -->
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden border-t-4 border-t-blue-500">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-6">Jadwal Pelaksanaan</h3>
                    
                    <div class="space-y-6">
                        <!-- Date -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 flex-shrink-0">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</p>
                                <p class="text-sm font-bold text-gray-800">
                                    {{ $seminar->tanggal ? $seminar->tanggal->translatedFormat('l, d F Y') : 'N/A' }}
                                </p>
                            </div>
                        </div>

                        <!-- Time -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-600 flex-shrink-0">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Waktu</p>
                                <p class="text-sm font-bold text-gray-800">{{ $seminar->waktu_mulai }} - {{ $seminar->waktu_selesai ?: 'Selesai' }}</p>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600 flex-shrink-0">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi</p>
                                <p class="text-sm font-bold text-gray-800 line-clamp-2">{{ $seminar->lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">P1</span>
                                {{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}
                            </h3>
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
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">P2</span>
                                {{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}
                            </h3>
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
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center text-sm mr-2 flex-shrink-0">PMB</span>
                                {{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}
                            </h3>
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
