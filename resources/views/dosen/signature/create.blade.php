@extends('layouts.app')

@section('title', 'E-Signature')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Tanda Tangan Digital</h1>

        <div class="mb-6">
            <h2 class="text-lg font-medium text-gray-700">Informasi Seminar</h2>
            <p class="text-gray-600 font-medium mb-1">Judul:</p>
            <div class="text-gray-700 mb-2 whitespace-pre-wrap">{!! $seminar->judul !!}</div>
            <p class="text-gray-600">Tanggal: {{ $seminar->tanggal->translatedFormat('d F Y') }}</p>
            <p class="text-gray-600">Jenis: {{ $seminar->seminarJenis->nama ?? 'N/A' }}</p>
            <p class="text-gray-600">Mahasiswa: {{ $seminar->mahasiswa->nama ?? 'N/A' }}</p>
        </div>

        <div class="mb-8">
            <h2 class="text-lg font-medium text-gray-700 mb-4">Buat Tanda Tangan Anda</h2>

            <form id="signatureForm" action="{{ route('dosen.signature.store', ['seminarId' => $seminar->id, 'evaluatorType' => $evaluatorType]) }}" method="POST">
                @csrf
                <div class="mb-4 bg-white border border-blue-200 rounded-xl p-4 shadow-sm">
                    <div class="signature-pad-wrapper-{{ $evaluatorType }}">
                        <input
                            type="hidden"
                            name="signature"
                            class="signature-input-{{ $evaluatorType }}"
                        >

                        <button
                            type="button"
                            class="toggle-signature-btn-{{ $evaluatorType }} btn-pill btn-pill-info text-xs px-4 py-2 mb-2 w-full sm:w-auto justify-center"
                        >
                            <i class="fas fa-signature mr-2"></i>
                            Buat / Ubah Tanda Tangan
                        </button>

                        <div class="signature-pad-container-{{ $evaluatorType }} hidden">
                            <canvas
                                width="360"
                                height="120"
                                style="touch-action: none !important;"
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

                    <p class="text-xs text-gray-500 mt-2">
                        Gunakan mouse atau sentuhan untuk membuat tanda tangan.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <button type="submit" class="btn-pill btn-pill-primary w-full sm:w-auto">
                        Simpan Tanda Tangan
                    </button>
                </div>
            </form>
        </div>

        <div>
            <h2 class="text-lg font-medium text-gray-700 mb-4">Petunjuk Penggunaan</h2>
            <ul class="list-disc pl-5 space-y-2 text-gray-600">
                <li>Gambar tanda tangan dengan mouse atau layar sentuh di atas kotak yang disediakan</li>
                <li>Gunakan tombol "Bersihkan" untuk menghapus tanda tangan dan menggambar ulang</li>
                <li>Klik "Simpan Tanda Tangan" setelah selesai</li>
            </ul>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @vite('resources/js/signature-pad.js')
    <script>
        (function() {
            function initSignatureValidation() {
                const form = document.querySelector('form');
                const signatureInput = document.querySelector('input[name="signature"]');
                if (!form || !signatureInput) return;

                if (form.dataset.initialized === 'true') return;

                form.addEventListener('submit', function (e) {
                    if (!signatureInput.value) {
                        e.preventDefault();
                        alert('Silakan buat tanda tangan terlebih dahulu.');
                    }
                });

                form.dataset.initialized = 'true';
            }

            // Standardized Init Pattern
            if (document.readyState !== 'loading') {
                initSignatureValidation();
            } else {
                document.addEventListener('DOMContentLoaded', initSignatureValidation);
            }
            window.addEventListener('page-loaded', initSignatureValidation);
        })();
    </script>
@endsection
