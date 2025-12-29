@extends('layouts.app')

@section('title', 'Detail Permohonan Surat')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
        <div class="flex items-start justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Detail Permohonan Surat</h1>
                <p class="text-sm text-gray-500">Jenis: <strong>{{ $surat->jenis->nama ?? '-' }}</strong></p>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Status & Meta Section --}}
            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 mb-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="md:col-span-1">
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Status</div>
                        <div>
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full 
                                @if($surat->status == 'diajukan') bg-yellow-100 text-yellow-800
                                @elseif($surat->status == 'diproses') bg-blue-100 text-blue-800
                                @elseif($surat->status == 'dikirim') bg-green-100 text-green-800
                                @elseif($surat->status == 'ditolak') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($surat->status) }}
                            </span>
                            @if($surat->status === 'dikirim')
                                <p class="text-[10px] text-gray-500 mt-1 font-medium italic">Silakan cek email Anda.</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Nomor Surat</div>
                        <div class="text-sm font-semibold text-gray-700 font-mono">{{ $surat->no_surat ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Tanggal Diajukan</div>
                        <div class="text-sm font-semibold text-gray-700">{{ $surat->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }}</div>
                    </div>

                    @if($surat->status === 'dikirim' && $surat->sent_at)
                        <div>
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Dikirim Email</div>
                            <div class="text-sm font-semibold text-green-600">{{ $surat->sent_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H:i') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="pt-2">
                <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-file-alt text-blue-500"></i> Rincian Data
                </h2>
                <div id="dynamic-fields-mahasiswa" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                    <!-- Dynamic fields will be injected here -->
                </div>
            </div>
        </div>

        <div class="mt-10 pt-6 border-t flex justify-end gap-3">
            @if(in_array($surat->status, ['diproses', 'dikirim']) && ($surat->jenis->allow_download ?? true))
                <a href="{{ route('mahasiswa.surat.download', $surat) }}" download data-no-ajax class="px-8 py-2.5 rounded-full text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition-all flex items-center justify-center">
                    <i class="fas fa-file-download mr-2"></i> Unduh Surat
                </a>
            @endif
            <a href="{{ route('mahasiswa.surat.index') }}" class="px-8 py-2.5 rounded-full text-sm font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-all">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const suratData = @json($surat->data ?? []);
        const formFields = @json($surat->jenis?->form_fields ?? []);

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function formatIndonesianDate(dateStr) {
            if (!dateStr) return '-';
            try {
                const date = new Date(dateStr);
                if (isNaN(date.getTime())) return dateStr;
                const options = { day: 'numeric', month: 'long', year: 'numeric' };
                return new Intl.DateTimeFormat('id-ID', options).format(date);
            } catch (e) {
                return dateStr;
            }
        }

        function renderField(field) {
            const key = field.key;
            const label = escapeHtml(field.label);
            let value = suratData[key];

            // Mapping from core model if empty in JSON
            if (value === undefined || value === null || value === '') {
                if (key === 'tujuan') value = @json($surat->tujuan);
                if (key === 'perihal') value = @json($surat->perihal);
                if (key === 'isi') value = @json($surat->isi);
                if (key === 'penerima_email') value = @json($surat->penerima_email);
                if (key === 'tanggal_surat') value = @json($surat->tanggal_surat?->translatedFormat('d F Y'));
            }

            if (['no_surat', 'status'].includes(key)) return '';
            if (field.type === 'pemohon') return '';

            let displayValue = value || '-';

            // Special handling for labels (select/radio/checkbox)
            if (['select', 'radio', 'checkbox'].includes(field.type) && field.options) {
                const options = Array.isArray(field.options) ? field.options : [];
                if (Array.isArray(value)) {
                    displayValue = value.map(val => {
                        const opt = options.find(o => String(o.value) === String(val));
                        return opt ? opt.label : val;
                    }).join(', ');
                } else {
                    const opt = options.find(o => String(o.value) === String(value));
                    displayValue = opt ? opt.label : value;
                }
            }
            
            if (field.type === 'file' && value) {
                displayValue = `<a href="/storage/${value}" target="_blank" class="text-blue-600 underline font-medium"><i class="fas fa-eye mr-1"></i> Lihat File</a>`;
            } else if (field.type === 'date' && value) {
                displayValue = formatIndonesianDate(value);
            } else if (field.type === 'checkbox' && (!field.options || field.options.length === 0)) {
                displayValue = value ? '<span class="text-green-600 font-bold">Ya</span>' : 'Tidak';
            } else if (Array.isArray(value) && !field.options) {
                displayValue = value.join(', ');
            }

            return `
                <div class="${field.type === 'textarea' ? 'md:col-span-2' : ''}">
                    <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">${label}</div>
                    <div class="text-sm text-gray-700 ${field.type === 'textarea' ? 'p-3 bg-gray-50 rounded-lg whitespace-pre-wrap' : 'font-semibold'}">
                        ${displayValue}
                    </div>
                </div>
            `;
        }

        function initMahasiswaSuratShow() {
            const container = document.getElementById('dynamic-fields-mahasiswa');
            if (!container) return;
            
            const html = formFields.map(renderField).filter(h => h !== '').join('');
            container.innerHTML = html || '<p class="text-sm text-gray-500 italic">Tidak ada rincian data tambahan.</p>';
        }

        // Standardized Init Pattern
        if (document.readyState !== 'loading') {
            initMahasiswaSuratShow();
        } else {
            document.addEventListener('DOMContentLoaded', initMahasiswaSuratShow);
        }
        window.addEventListener('page-loaded', initMahasiswaSuratShow);
    })();
</script>
@endsection
