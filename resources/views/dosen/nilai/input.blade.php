@extends('layouts.app')

@section('title', 'Input Nilai')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Input Nilai Seminar</h1>

        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Detail Seminar</h2>
            <div class="space-y-3 mb-6 bg-gray-50 p-4 rounded-md border border-gray-200">
                <p><strong>Mahasiswa:</strong> {{ $seminar->mahasiswa->nama ?? 'N/A' }} ({{ $seminar->mahasiswa->npm ?? 'N/A' }})</p>
                <div>
                    <p><strong>Judul:</strong></p>
                    <div class="mt-1 text-gray-800">
                        {!! $seminar->judul !!}
                    </div>
                </div>
                <p><strong>Jenis:</strong> {{ $seminar->seminarJenis->nama ?? 'N/A' }}</p>

                <p><strong>Tanggal:</strong> {{ $seminar->tanggal->translatedFormat('d F Y') }}</p>
                <p><strong>Lokasi:</strong> {{ $seminar->lokasi }}</p>
                <p><strong>Peran Tim Penguji:</strong>
                    @if($evaluatorType == 'p1') Pembimbing 1
                    @elseif($evaluatorType == 'p2') Pembimbing 2
                    @else Pembahas
                    @endif
                </p>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Berkas Persyaratan</h2>
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                @php
                    $berkas = is_array($seminar->berkas_syarat) ? $seminar->berkas_syarat : [];
                    $syaratItems = is_array($seminar->seminarJenis?->berkas_syarat_items) ? $seminar->seminarJenis->berkas_syarat_items : [];
                @endphp

                @if(!empty($berkas))
                    <div class="space-y-2">
                        @foreach($syaratItems as $item)
                            @php
                                $key = $item['key'] ?? null;
                                $label = $item['label'] ?? $key;
                                $filePath = $berkas[$key] ?? null;
                            @endphp
                            @if($filePath)
                                <div class="flex items-center justify-between py-2 border-b border-gray-200 last:border-0">
                                    <span class="text-sm font-medium text-gray-700">{{ $label }}</span>
                                    <a href="{{ asset('uploads/' . $filePath) }}" target="_blank" class="text-blue-600 hover:underline text-sm flex items-center gap-1">
                                        <i class="fas fa-file-download"></i> Lihat Berkas
                                    </a>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 italic">Tidak ada berkas yang diunggah.</p>
                @endif
            </div>
        </div>

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('dosen.nilai.store', $seminar) }}" method="POST" id="nilaiForm">
            @csrf

            <h2 class="text-xl font-semibold text-gray-800 mb-4">Form Penilaian</h2>

            @if($aspects->count() > 0)
                <div class="grid grid-cols-1 gap-4 mb-6">
                    @foreach($aspects as $aspect)
                        <div class="rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50/70 to-white/90 p-4 shadow-sm">
                            <label for="aspect_{{ $aspect->id }}" class="block text-sm font-semibold text-gray-800 mb-1">
                                {{ $aspect->nama_aspek }}
                            </label>
                            <p class="text-xs text-gray-500 mb-2">Skala nilai 0-100</p>
                            <div class="flex items-center gap-3">
                                <input
                                    type="number"
                                    name="aspect_{{ $aspect->id }}"
                                    id="aspect_{{ $aspect->id }}"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    value="{{ old('aspect_' . $aspect->id, $existingScores[$aspect->id] ?? '') }}"
                                    class="w-28 px-3 py-2 border border-blue-200 rounded-lg aspect-input text-sm font-semibold text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('aspect_' . $aspect->id) border-red-500 focus:ring-red-500 @enderror"
                                    placeholder="0-100"
                                    data-percentage="{{ $aspect->persentase }}"
                                    required
                                />
                                <span class="text-sm text-gray-500">poin</span>
                            </div>
                            @error('aspect_' . $aspect->id)
                                <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mb-6 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                    <p>Aspek penilaian untuk jenis seminar ini belum dikonfigurasi. Silakan hubungi administrator.</p>
                </div>
            @endif

            <div class="mb-6 border border-gray-200 rounded-lg p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanda Tangan</label>
                <div class="signature-pad-wrapper-{{ $evaluatorType }}">
                    <input
                        type="hidden"
                        name="signature"
                        class="signature-input-{{ $evaluatorType }}"
                    >

                    <div class="flex justify-center mb-2">
                        <button
                            type="button"
                            class="toggle-signature-btn-{{ $evaluatorType }} btn-pill btn-pill-info text-sm px-6 py-2 w-full sm:w-auto justify-center"
                        >
                            <i class="fas fa-signature mr-2"></i>
                            Buat / Ubah Tanda Tangan
                        </button>
                    </div>

                    <div class="signature-pad-container-{{ $evaluatorType }} hidden">
                        <canvas
                            width="360"
                            height="120"
                            class="signature-canvas-{{ $evaluatorType }} border border-blue-200 rounded bg-white cursor-crosshair w-full"
                        ></canvas>
                        <button
                            type="button"
                            class="clear-signature-btn-{{ $evaluatorType }} text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full sm:w-auto"
                        >
                            Bersihkan
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Gunakan mouse atau sentuhan untuk membuat tanda tangan.</p>
            </div>

            <div class="mb-6">
                <label for="catatan" class="block text-sm font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
                <textarea
                    name="catatan"
                    id="catatan"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md @error('catatan') border-red-500 @enderror"
                    placeholder="Tambahkan catatan untuk penilaian ini"
                >{{ old('catatan', $existingNilai->catatan ?? '') }}</textarea>
                @error('catatan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between pt-6">
                <a href="{{ route('dosen.dashboard') }}" class="btn-pill btn-pill-secondary">
                    Batal
                </a>
                @if($aspects->count() > 0)
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Simpan Nilai
                    </button>
                @endif
            </div>
        </form>

        <!-- Tanda Tangan Section -->
        @if($existingSignature)
            <div class="mt-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Tanda Tangan Anda</h3>
                <div class="flex items-center space-x-4">
                    <img src="{{ asset('uploads/' . $existingSignature->tanda_tangan) }}"
                         alt="Tanda Tangan"
                         class="h-16 border border-gray-300 rounded bg-white">
                    <div>
                        <p class="text-sm text-gray-600">
                            <strong> Ditandatangani pada:</strong>
                            {{ $existingSignature->tanggal_ttd ? $existingSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') : 'N/A' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            Status: <span class="text-green-600 font-medium">Sudah Ditandatangani</span>
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
    @vite('resources/js/signature-pad.js')
    <script>
        (function() {
            function initNilaiForm() {
                const form = document.getElementById('nilaiForm');
                const signatureInput = document.querySelector('.signature-input-{{ $evaluatorType }}');
                const hasExistingSignature = {{ $existingSignature ? 'true' : 'false' }};

                if (!form || !signatureInput) return;
                if (form.dataset.initialized === 'true') return;

                form.addEventListener('submit', function (e) {
                    if (!signatureInput.value) {
                        if (hasExistingSignature) {
                            if (!confirm('Perubahan nilai akan disimpan menggunakan tanda tangan yang sudah ada. Lanjutkan?')) {
                                e.preventDefault();
                            }
                        } else {
                            if (!confirm('Anda belum membubuhkan tanda tangan. Lanjutkan tanpa tanda tangan?')) {
                                e.preventDefault();
                            }
                        }
                    }
                });

                form.dataset.initialized = 'true';
            }

            // Standardized Init Pattern
            if (document.readyState !== 'loading') {
                initNilaiForm();
            } else {
                document.addEventListener('DOMContentLoaded', initNilaiForm);
            }
            window.addEventListener('page-loaded', initNilaiForm);
        })();
    </script>
@endsection
