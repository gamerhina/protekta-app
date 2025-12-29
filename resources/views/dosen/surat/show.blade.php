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

        {{-- Status & Meta Section (Always Visible) --}}
        <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
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

        @if($surat->status === 'diajukan')
            <form id="update-form" method="POST" action="{{ route('dosen.surat.update', $surat) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div id="dynamic-fields-edit" class="space-y-4">
                    <div class="flex items-center justify-center py-10">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-500"></i>
                    </div>
                </div>
            </form>

            <div class="mt-8 flex flex-col md:flex-row justify-between items-center gap-4 pt-6 border-t">
                <form action="{{ route('dosen.surat.destroy', $surat) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan permohonan ini?')" class="w-full md:w-auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full md:w-auto px-6 py-2.5 rounded-full text-sm font-medium transition-colors border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center">
                        <i class="fas fa-trash-alt mr-2"></i> Batalkan Permohonan
                    </button>
                </form>

                <div class="flex items-center gap-3 w-full md:w-auto">
                    <a href="{{ route('dosen.surat.index') }}" class="flex-1 md:flex-none text-center px-6 py-2.5 rounded-full text-sm font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-colors">
                        Kembali
                    </a>
                    <button form="update-form" class="flex-1 md:flex-none btn-pill btn-pill-primary py-2.5" type="submit">
                        Update Permohonan
                    </button>
                </div>
            </div>
        @else
            <div class="space-y-6">
                <div class="pt-2">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                        <i class="fas fa-file-alt text-blue-500"></i> Rincian Data
                    </h2>
                    <div id="dynamic-fields-display" class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                        <!-- Dynamic fields will be injected here -->
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t flex justify-end gap-3">
                @if(in_array($surat->status, ['diproses', 'dikirim']) && ($surat->jenis->allow_download ?? true))
                    <a href="{{ route('dosen.surat.download', $surat) }}" download data-no-ajax class="px-8 py-2.5 rounded-full text-sm font-medium bg-green-600 text-white hover:bg-green-700 transition-all flex items-center justify-center">
                        <i class="fas fa-file-download mr-2"></i> Unduh Surat
                    </a>
                @endif
                <a href="{{ route('dosen.surat.index') }}" class="px-8 py-2.5 rounded-full text-sm font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 transition-all">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const suratData = @json($surat->data ?? []);
        const formFields = @json($surat->jenis?->form_fields ?? []);
        const mahasiswas = @json($mahasiswas ?? []);
        const isEdit = @json($surat->status === 'diajukan');

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

        function renderFieldReadOnly(field) {
            const key = field.key;
            const label = escapeHtml(field.label);
            let value = suratData[key] || '';

            // Mapping from core model if empty in JSON
            if (value === '') {
                if (key === 'tujuan') value = @json($surat->tujuan);
                if (key === 'perihal') value = @json($surat->perihal);
                if (key === 'isi') value = @json($surat->isi);
                if (key === 'penerima_email') value = @json($surat->penerima_email);
                if (key === 'tanggal_surat') value = @json($surat->tanggal_surat?->translatedFormat('d F Y'));
                if (key === 'mahasiswa_id') value = @json($surat->mahasiswa->nama ?? '');
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
                displayValue = `<a href="/storage/${value}" target="_blank" class="text-blue-600 underline font-medium"><i class="fas fa-file-download mr-1"></i> Lihat File</a>`;
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

        function renderFieldEdit(field) {
            const key = escapeHtml(field.key);
            const label = escapeHtml(field.label);
            let value = suratData[key] || '';

            // Mapping from core model if empty in JSON
            if (value === '') {
                if (key === 'tujuan') value = @json($surat->tujuan);
                if (key === 'perihal') value = @json($surat->perihal);
                if (key === 'isi') value = @json($surat->isi);
                if (key === 'penerima_email') value = @json($surat->penerima_email);
                if (key === 'mahasiswa_id') value = @json($surat->mahasiswa_id);
                if (key === 'tanggal_surat') value = @json($surat->tanggal_surat?->format('Y-m-d'));
            }

            if (field.type === 'pemohon' || field.type === 'auto_no_surat') return '';

            if (field.key === 'mahasiswa_id') {
                const options = mahasiswas.map(m => `<option value="${m.id}" ${value == m.id ? 'selected' : ''}>${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
                return `
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <select name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm">
                            <option value="">Pilih Mahasiswa</option>
                            ${options}
                        </select>
                    </div>
                `;
            }

            if (field.key === 'untuk_type') {
                const currentUt = @json($surat->untuk_type) || value;
                return `
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <select name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm">
                            <option value="umum" ${currentUt === 'umum' ? 'selected' : ''}>Umum</option>
                            <option value="mahasiswa" ${currentUt === 'mahasiswa' ? 'selected' : ''}>Mahasiswa</option>
                            <option value="dosen" ${currentUt === 'dosen' ? 'selected' : ''}>Dosen (Saya Sendiri)</option>
                        </select>
                    </div>
                `;
            }

            if (field.type === 'textarea') {
                return `
                    <div class="md:col-span-2 bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <textarea name="form_data[${key}]" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm">${escapeHtml(value)}</textarea>
                    </div>
                `;
            }

            if (field.type === 'date') {
                return `
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <input type="date" name="form_data[${key}]" value="${escapeHtml(value)}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm">
                    </div>
                `;
            }

            if (field.type === 'file') {
                return `
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        ${value ? `<div class="mb-2 text-xs text-blue-600 font-medium whitespace-nowrap overflow-hidden text-ellipsis"><i class="fas fa-paperclip mr-1"></i> <a href="/storage/${value}" target="_blank" class="underline">Lihat File Saat Ini</a></div>` : ''}
                        <input type="file" name="form_files[${key}]" class="text-xs">
                    </div>
                `;
            }

            return `
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <input type="${field.type === 'number' ? 'number' : 'text'}" name="form_data[${key}]" value="${escapeHtml(value)}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm">
                </div>
            `;
        }

        function initDosenSuratShow() {
            if (isEdit) {
                const container = document.getElementById('dynamic-fields-edit');
                if (container) {
                    container.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 gap-5">${formFields.map(renderFieldEdit).join('')}</div>`;
                }
            } else {
                const container = document.getElementById('dynamic-fields-display');
                if (container) {
                    const html = formFields.map(renderFieldReadOnly).filter(h => h !== '').join('');
                    container.innerHTML = html || '<p class="text-sm text-gray-500 italic">Tidak ada rincian data tambahan.</p>';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', initDosenSuratShow);
        window.addEventListener('page-loaded', initDosenSuratShow);
    })();
</script>
@endsection
