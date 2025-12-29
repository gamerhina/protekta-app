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

        @if($templates->count() > 0)
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">📄 Generate Document</h3>
                <p class="text-sm text-gray-600 mb-3">Pilih template untuk generate dokumen dari data seminar ini:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($templates as $template)
                        <a href="{{ route('admin.document.preview', [$template->id, $seminar->id]) }}" 
                           class="flex items-center justify-between bg-white border border-gray-300 rounded-md p-3 hover:bg-blue-50 hover:border-blue-400 transition">
                            <div class="flex-1">
                                <div class="font-medium text-gray-900 text-sm">{{ $template->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $template->kode }}</div>
                            </div>
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h3 class="text-lg font-medium text-gray-500">No. Surat</h3>
                    <p class="mt-1 text-gray-900">{{ $seminar->no_surat }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-medium text-gray-500">Status</h3>
                    <p class="mt-1 text-gray-900">
                        <span class="inline-flex font-semibold rounded-full
                            @if($seminar->status == 'belum_lengkap') text-[10px] px-2 py-0.5
                            @else text-xs px-2 leading-5
                            @endif
                            @if($seminar->status == 'diajukan') bg-yellow-100 text-yellow-800
                            @elseif($seminar->status == 'disetujui') bg-blue-100 text-blue-800
                            @elseif($seminar->status == 'ditolak') bg-red-100 text-red-800
                            @elseif($seminar->status == 'belum_lengkap') bg-orange-100 text-orange-800
                            @elseif($seminar->status == 'selesai') bg-green-100 text-green-800
                            @endif">
                            @if($seminar->status == 'belum_lengkap')
                                Belum Lengkap
                            @else
                                {{ ucfirst($seminar->status) }}
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

            <div>
                <h3 class="text-lg font-medium text-gray-500">Jenis Seminar</h3>
                <p class="mt-1 text-gray-900">{{ $seminar->seminarJenis->nama ?? 'N/A' }}</p>
            </div>

            <div>
                <h3 class="text-lg font-medium text-gray-500">Mahasiswa</h3>
                <p class="mt-1 text-gray-900">{{ $seminar->mahasiswa->nama ?? 'N/A' }} ({{ $seminar->mahasiswa->npm ?? 'N/A' }})</p>
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
                    <div class="mt-2">
                        @foreach($seminar->berkas_syarat as $key => $path)
                            @if(is_string($path) && $path !== '')
                                <div class="flex items-center py-1">
                                    <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    <a href="#" onclick="openPdf('{{ $path }}')" class="text-blue-600 hover:underline hover:text-blue-800">
                                        {{ is_string($key) ? ($key . ': ') : '' }}{{ $path }}
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

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