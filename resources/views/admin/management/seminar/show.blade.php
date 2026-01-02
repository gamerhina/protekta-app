@extends('layouts.app')

@section('title', 'Seminar Details')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-semibold text-gray-800">Seminar Details</h1>
            <div class="flex space-x-2 justify-center sm:justify-start">
                <a href="{{ route('admin.seminar.edit', $seminar->id) }}" class="btn-pill btn-pill-warning">
                    Edit
                </a>
                <a href="{{ route('admin.seminar.index') }}" class="btn-pill btn-pill-secondary">
                    Back to List
                </a>
            </div>
        </div>

        @php
            $templates = \App\Models\DocumentTemplate::where('aktif', true)
                ->where(function($q) use ($seminar) {
                    $q->whereNull('seminar_jenis_id')
                      ->orWhere('seminar_jenis_id', $seminar->seminar_jenis_id);
                })
                ->get();
        @endphp

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
                            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-[10px] font-bold uppercase tracking-wider rounded-full">
                                {{ $seminar->seminarJenis->nama ?? 'N/A' }}
                            </span>
                            <span class="inline-flex font-bold rounded-full text-[10px] px-3 py-1 uppercase tracking-wider
                                @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                                @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                                @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                                @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                                @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                                @endif">
                                {{ $seminar->status == 'belum_lengkap' ? 'Belum Lengkap' : ucfirst($seminar->status) }}
                            </span>
                            @if($seminar->no_surat)
                                <span class="px-3 py-1 bg-gray-100 text-gray-600 text-[10px] font-mono font-bold rounded-full">
                                    #{{ $seminar->no_surat }}
                                </span>
                            @endif
                        </div>

                        <h2 class="text-xl md:text-2xl font-semibold text-gray-800 leading-tight mb-4">
                            {!! $seminar->judul !!}
                        </h2>

                        <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                            <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600">
                                <i class="fas fa-user-graduate text-xl"></i>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Mahasiswa</p>
                                <p class="text-sm font-bold text-gray-800">{{ $seminar->mahasiswa->nama ?? 'N/A' }}</p>
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
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p1Dosen->nama ?? 'N/A' }}</p>
                    </div>

                    <!-- P2 -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-green-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-green-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">P2</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembimbing 2</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->p2Dosen->nama ?? 'N/A' }}</p>
                    </div>

                    <!-- PMB -->
                    <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-purple-200 transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <span class="w-8 h-8 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">PMB</span>
                            <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembahas</span>
                        </div>
                        <p class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">{{ $seminar->pembahasDosen->nama ?? 'N/A' }}</p>
                    </div>
                </div>

                <!-- Requirements Files -->
                @if($seminar->berkas_syarat && is_array($seminar->berkas_syarat) && count($seminar->berkas_syarat) > 0)
                <div class="bg-white rounded-3xl border border-gray-200 p-6 overflow-hidden">
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
                                    <a href="#" onclick="openPdf('{{ $path }}')" class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-500 hover:text-white transition-all">
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

                @if($templates->count() > 0)
                <!-- Quick Actions Card -->
                <div class="bg-white rounded-3xl border border-gray-200 p-6 shadow-sm overflow-hidden">
                    <h3 class="text-sm font-bold text-gray-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <i class="fas fa-bolt text-yellow-500"></i> Generate Dokumen
                    </h3>
                    <div class="space-y-2">
                        @foreach($templates as $template)
                            <a href="{{ route('admin.document.preview', [$template->id, $seminar->id]) }}" 
                               class="flex items-center justify-between bg-gray-50 border border-transparent rounded-2xl p-3 hover:bg-blue-50 hover:border-blue-200 transition-all group">
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-800 text-xs truncate">{{ $template->nama }}</div>
                                    <div class="text-[10px] text-gray-400 uppercase font-mono tracking-tighter">{{ $template->kode }}</div>
                                </div>
                                <div class="w-7 h-7 rounded-full bg-white flex items-center justify-center text-gray-400 group-hover:bg-blue-500 group-hover:text-white transition-all">
                                    <i class="fas fa-chevron-right text-[10px]"></i>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>

        <div class="space-y-6 mt-6">
            <!-- Score Recapitulation with Terbilang -->
            @include('admin.management.seminar.score_recapitulation')

            <!-- Email Tracking -->
            @include('admin.management.seminar.email_tracking')

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
                            <div class="mt-2">
                                <img src="{{ route('admin.seminar.files.show', ['path' => $p1Signature->tanda_tangan]) }}" alt="Tanda Tangan P1" class="max-w-full h-auto border border-gray-200">
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $p1Signature->tanggal_ttd ? $p1Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500">Belum ditandatangani</p>
                        @endif
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembimbing 2</h4>
                        @if($p2Signature)
                            <div class="mt-2">
                                <img src="{{ route('admin.seminar.files.show', ['path' => $p2Signature->tanda_tangan]) }}" alt="Tanda Tangan P2" class="max-w-full h-auto border border-gray-200">
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $p2Signature->tanggal_ttd ? $p2Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500">Belum ditandatangani</p>
                        @endif
                    </div>

                    <div class="border border-gray-200 rounded-lg p-4">
                        <h4 class="font-medium text-gray-700">Pembahas</h4>
                        @if($pembahasSignature)
                            <div class="mt-2">
                                <img src="{{ route('admin.seminar.files.show', ['path' => $pembahasSignature->tanda_tangan]) }}" alt="Tanda Tangan Pembahas" class="max-w-full h-auto border border-gray-200">
                                <p class="mt-1 text-sm text-gray-600">Tanggal: {{ $pembahasSignature->tanggal_ttd ? $pembahasSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y H:i') : 'N/A' }}</p>
                            </div>
                        @else
                            <p class="text-gray-500">Belum ditandatangani</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Seminar Button -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="mt-4">
                <form action="{{ route('admin.seminar.destroy', $seminar->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus seminar ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-pill btn-pill-danger">
                        Delete Seminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
