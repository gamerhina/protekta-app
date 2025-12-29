@extends('layouts.app')

@section('title', 'Buat Permohonan Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Buat Permohonan Surat</h1>
                <p class="text-sm text-gray-500">Lengkapi data di bawah ini untuk mengajukan permohonan surat.</p>
            </div>
            <a href="{{ route('dosen.surat.index') }}" class="btn-pill btn-pill-secondary">Kembali</a>
        </div>

        <form method="POST" action="{{ route('dosen.surat.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div id="jenis_wrap" class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Surat</label>
                    <select name="surat_jenis_id" id="surat_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none transition-all" required>
                        <option value="">Pilih jenis surat</option>
                        @foreach($jenisList as $j)
                            <option value="{{ $j->id }}" {{ old('surat_jenis_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="tanggal_wrap">
                    {{-- Dynamically injected or stay empty --}}
                </div>
                
                {{-- Hidden Pemohon Wrap --}}
                <div id="pemohon_wrap" class="hidden"></div>

                <div class="md:col-span-2 mt-4">
                    <h2 class="text-lg font-semibold text-gray-800 border-b pb-2 mb-4">Rincian Data</h2>
                </div>

                <div id="dynamic-fields" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2 text-sm text-gray-500 italic py-8 border border-dashed border-gray-200 rounded-xl text-center">
                        Silakan pilih jenis surat untuk menampilkan formulir.
                    </div>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t flex justify-end">
                <button class="btn-pill btn-pill-primary px-10 py-2.5" type="submit">
                    Kirim Permohonan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const jenisList = @json($jenisListPayload);
        const mahasiswas = @json($mahasiswasPayload);
        const currentDosen = @json($currentDosenPayload);

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderPemohonField(field) {
            const key = escapeHtml(field.key);
            return `
                <input type="hidden" name="form_data[${key}][type]" value="dosen">
                <input type="hidden" name="form_data[${key}][id]" value="${escapeHtml(currentDosen.id)}">
            `;
        }

        function renderField(field, isTop = false) {
            const key = escapeHtml(field.key);
            const label = escapeHtml(field.label);
            const requiredAttr = field.required ? 'required' : '';
            const placeholderAttr = escapeHtml(field.placeholder || '');

            if (field.type === 'pemohon' || field.type === 'auto_no_surat') return '';

            // Layout classes: Specific keys like 'perihal' are full width
            const isFullWidth = field.type === 'textarea' || 
                                field.type === 'file' || 
                                ['perihal', 'tujuan', 'isi'].includes(field.key);
            
            const wrapperClass = isTop ? 'bg-white border border-gray-200 rounded-xl p-4' : 
                                `bg-white border border-gray-200 rounded-xl p-4 ${isFullWidth ? 'md:col-span-2' : ''}`;
            
            let html = `<div class="${wrapperClass}">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>`;

            if (field.key === 'untuk_type') {
                html += `<select name="form_data[${key}]" id="field_untuk_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none" ${requiredAttr}>
                            <option value="">Pilih</option>
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen (Saya Sendiri)</option>
                            <option value="umum">Umum</option>
                        </select>`;
            } else if (field.key === 'mahasiswa_id') {
                const optionsHtml = mahasiswas
                    .map(m => `<option value="${escapeHtml(m.id)}">${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`)
                    .join('');
                html += `<div id="field-wrap-mahasiswa_id">
                            <select name="form_data[${key}]" id="field_mahasiswa_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none" ${requiredAttr}>
                                <option value="">Pilih mahasiswa...</option>
                                ${optionsHtml}
                            </select>
                        </div>`;
            } else if (field.type === 'date') {
                const today = `{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}`;
                html += `<input type="date" name="form_data[${key}]" value="${today}" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none" ${requiredAttr}>`;
            } else if (field.type === 'textarea') {
                html += `<textarea name="form_data[${key}]" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none" placeholder="${placeholderAttr}" ${requiredAttr}></textarea>`;
            } else if (field.type === 'file') {
                const exts = Array.isArray(field.extensions) ? field.extensions : [];
                const accept = exts.length ? exts.map(e => `.${e.trim().replace(/^\./, '')}`).join(',') : '';
                const formatLabel = exts.length ? `Format: ${escapeHtml(exts.join(', ').toUpperCase())}` : '';
                const sizeLabel = field.max_kb ? `Maks: ${Math.round(field.max_kb / 1024 * 10) / 10}MB` : '';
                
                html += `<input type="file" name="form_files[${key}]" class="w-full text-sm" ${accept ? `accept="${accept}"` : ''} ${requiredAttr}>`;
                
                if (formatLabel || sizeLabel) {
                    html += `<div class="text-[10px] text-gray-400 mt-1 italic flex gap-3">
                                ${formatLabel ? `<span>${formatLabel}</span>` : ''}
                                ${sizeLabel ? `<span>${sizeLabel}</span>` : ''}
                            </div>`;
                }
            } else if (field.type === 'select' || field.type === 'radio') {
                const options = Array.isArray(field.options) ? field.options : [];
                const optionsHtml = options.map(o => `<option value="${escapeHtml(o.value)}">${escapeHtml(o.label)}</option>`).join('');
                html += `<select name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none" ${requiredAttr}>
                            <option value="">Pilih</option>
                            ${optionsHtml}
                        </select>`;
            } else if (field.type === 'checkbox') {
                html += `<label class="flex items-center gap-2 text-sm text-gray-700 mt-1 cursor-pointer">
                            <input type="checkbox" name="form_data[${key}]" value="1" class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500 border-gray-300 transition-all">
                            <span>${label}</span>
                        </label>`;
            } else {
                const inputType = field.type === 'number' ? 'number' : 'text';
                html += `<input type="${inputType}" name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-1 focus:ring-blue-500 outline-none transition-all" placeholder="${placeholderAttr}" ${requiredAttr}>`;
            }

            html += `</div>`;
            return html;
        }

        function applyConditionalFields() {
            const untukType = document.getElementById('field_untuk_type')?.value;
            const wrap = document.getElementById('field-wrap-mahasiswa_id');
            if (!wrap) return;
            
            const container = wrap.closest('.md\\:col-span-2') || wrap.parentElement;
            if (untukType === 'mahasiswa') {
                container.style.display = 'block';
                document.getElementById('field_mahasiswa_id')?.setAttribute('required', 'required');
            } else {
                container.style.display = 'none';
                document.getElementById('field_mahasiswa_id')?.removeAttribute('required');
            }
        }

        function renderDynamicFields() {
            const jenisId = document.getElementById('surat_jenis_id')?.value;
            const container = document.getElementById('dynamic-fields');
            const pemohonWrap = document.getElementById('pemohon_wrap');
            const tanggalWrap = document.getElementById('tanggal_wrap');
            
            if (!container) return;

            if (!jenisId) {
                container.innerHTML = `<div class="md:col-span-2 text-sm text-gray-500 italic py-8 border border-dashed border-gray-200 rounded-xl text-center">Silakan pilih jenis surat untuk menampilkan formulir.</div>`;
                if (pemohonWrap) pemohonWrap.innerHTML = '';
                if (tanggalWrap) tanggalWrap.innerHTML = '';
                return;
            }

            const jenis = jenisList.find(j => String(j.id) === String(jenisId));
            const fields = Array.isArray(jenis?.form_fields) ? jenis.form_fields : [];

            const pemohonField = fields.find((f) => f && typeof f === 'object' && f.type === 'pemohon');
            const tanggalField = fields.find((f) => f && typeof f === 'object' && f.type === 'date');
            
            const otherFields = fields.filter((f) => {
                if (!f || typeof f !== 'object') return false;
                if (f.type === 'pemohon') return false;
                if (f.type === 'auto_no_surat') return false;
                if (tanggalField && f.key === tanggalField.key && f.type === 'date') return false;
                return true;
            });

            if (pemohonWrap) {
                pemohonWrap.innerHTML = pemohonField ? renderPemohonField(pemohonField) : '';
            }

            if (tanggalWrap) {
                tanggalWrap.innerHTML = tanggalField ? renderField(tanggalField, true) : '';
                tanggalWrap.style.display = tanggalField ? 'block' : 'none';
            }

            container.innerHTML = otherFields.map(f => renderField(f)).join('') || `<div class="md:col-span-2 text-sm text-gray-500 italic py-4">Jenis surat ini belum memiliki konfigurasi field.</div>`;

            document.getElementById('field_untuk_type')?.addEventListener('change', applyConditionalFields);
            applyConditionalFields();
        }

        function initSuratDosenCreate() {
            const jenisSelect = document.getElementById('surat_jenis_id');
            if (!jenisSelect) return;
            if (jenisSelect.dataset.initialized === '1') return;
            jenisSelect.dataset.initialized = '1';

            jenisSelect.addEventListener('change', renderDynamicFields);
            renderDynamicFields();
        }

        // Standardized Init Pattern
        if (document.readyState !== 'loading') {
            initSuratDosenCreate();
        } else {
            document.addEventListener('DOMContentLoaded', initSuratDosenCreate);
        }
        window.addEventListener('page-loaded', initSuratDosenCreate);
    })();
</script>
@endsection
