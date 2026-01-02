@extends('layouts.app')

@section('title', 'Buat Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Buat Surat</h1>
                <p class="text-sm text-gray-500">Membuat permohonan surat dari sisi admin.</p>
            </div>
            <a href="{{ route('admin.surat.index') }}" class="btn-pill btn-pill-secondary">Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.surat.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5" id="top-grid">
                    <div id="jenis_wrap" class="bg-white border border-gray-200 rounded-xl p-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Surat</label>
                        <select name="surat_jenis_id" id="surat_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="">Pilih jenis surat</option>
                            @foreach($jenisList as $j)
                                <option value="{{ $j->id }}" {{ old('surat_jenis_id') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="no_surat_wrap">
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                            <div class="text-sm font-medium text-slate-800 mb-1">Nomor Surat</div>
                            <div class="flex items-center gap-3">
                                <input name="no_surat" id="no_surat" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white" placeholder="001">
                            </div>
                            <div class="text-xs text-slate-500 mt-2">Default otomatis mengikuti jenis surat, tapi admin bisa edit manual.</div>
                        </div>
                    </div>

                    <div id="pemohon_wrap"></div>
                    <div id="tanggal_wrap"></div>
                </div>

                <div class="md:col-span-2">
                    <h2 class="text-lg font-semibold text-gray-800">Form Permohonan</h2>
                    <p class="text-sm text-gray-500">Field akan muncul sesuai konfigurasi pada Jenis Surat.</p>
                </div>

                <div id="dynamic-fields" class="md:col-span-2 space-y-4"></div>
            </div>

            <div class="mt-8 flex justify-end">
                <button class="btn-pill btn-pill-primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const jenisList = @json($jenisList->map(fn($j) => ['id' => $j->id, 'nama' => $j->nama, 'form_fields' => $j->form_fields]));
        const dosens = @json($dosens);
        const mahasiswas = @json($mahasiswas);

    function escapeHtml(str) {
        return String(str ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    async function refreshNoSurat() {
        const jenisId = document.getElementById('surat_jenis_id')?.value;
        const input = document.getElementById('no_surat');
        if (!input) return;
        if (!jenisId) {
            input.value = '';
            return;
        }

        try {
            const url = `{{ route('admin.surat.next-no-surat') }}?surat_jenis_id=${encodeURIComponent(jenisId)}`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            if (!input.dataset.userEdited || input.dataset.userEdited !== '1') {
                input.value = data?.next_no_surat || '';
            }
        } catch (e) {
            // ignore
        }
    }

    function renderPemohonField(field) {
        const sources = Array.isArray(field.pemohon_sources) && field.pemohon_sources.length
            ? field.pemohon_sources
            : ['mahasiswa','dosen'];

        const dosenOptions = dosens.map(d => `<option value="dosen:${d.id}">${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
        const mhsOptions = mahasiswas.map(m => `<option value="mahasiswa:${m.id}" data-email="${escapeHtml(m.email || '')}">${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
        const optionsHtml = `
            ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
            ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
        `;

        return `
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">${escapeHtml(field.label)}</label>
                <input type="hidden" class="pemohon-type" name="form_data[${escapeHtml(field.key)}][type]" value="">
                <input type="hidden" class="pemohon-id" name="form_data[${escapeHtml(field.key)}][id]" value="">
                <select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md">
                    <option value="">Pilih pemohon</option>
                    ${optionsHtml}
                </select>
            </div>
        `;
    }

    function renderField(field) {
        const key = escapeHtml(field.key);
        const label = escapeHtml(field.label);
        const requiredAttr = field.required ? 'required' : '';
        const placeholderAttr = escapeHtml(field.placeholder || '');

        if (field.type === 'pemohon') {
            return renderPemohonField(field);
        }
        if (field.type === 'auto_no_surat') {
            return '';
        }

        if (field.type === 'date') {
            const today = `{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}`;
            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <input type="date" name="form_data[${key}]" value="${today}" class="w-full px-3 py-2 border border-gray-300 rounded-md" ${requiredAttr}>
                </div>
            `;
        }

        if (field.type === 'textarea') {
            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <textarea name="form_data[${key}]" rows="5" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="${placeholderAttr}" ${requiredAttr}></textarea>
                </div>
            `;
        }

        if (field.type === 'file') {
            const accept = Array.isArray(field.extensions) && field.extensions.length ? field.extensions.map(e => `.${e.trim().replace(/^\./, '')}`).join(',') : '';
            const exts = Array.isArray(field.extensions) ? field.extensions : [];
            const formatLabel = exts.length ? escapeHtml(exts.join(', ').toUpperCase()) : 'FILE';
            const sizeLabel = field.max_kb ? `${Math.round(field.max_kb / 1024 * 10) / 10}MB` : '5MB';

            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4 group hover:border-blue-200 transition-all">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">${label}</h3>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                ${field.required ? 'WAJIB' : 'OPSIONAL'} • ${formatLabel}
                            </p>
                        </div>
                        <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">BELUM ADA</span>
                    </div>
                    <div class="relative group/input">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Berkas</label>
                        <input 
                            type="file" 
                            name="form_files[${key}]" 
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all" 
                            ${accept ? `accept="${accept}"` : ''} 
                            ${requiredAttr}
                        >
                        <div class="text-[10px] text-gray-400 mt-2 italic flex gap-3 px-1">
                            <span>Maks ukuran: <span class="font-bold text-gray-600">${sizeLabel}</span></span>
                        </div>
                    </div>
                </div>
            `;
        }

        if (field.type === 'select' || field.type === 'radio') {
            const options = Array.isArray(field.options) ? field.options : [];
            const optionsHtml = options.map(o => `<option value="${escapeHtml(o.value)}">${escapeHtml(o.label)}</option>`).join('');
            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <select name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md" ${requiredAttr}>
                        <option value="">Pilih</option>
                        ${optionsHtml}
                    </select>
                </div>
            `;
        }

        if (field.type === 'checkbox') {
            const options = Array.isArray(field.options) ? field.options : [];
            if (options.length) {
                const items = options.map((o, idx) => `
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="form_data[${key}][]" value="${escapeHtml(o.value)}">
                        ${escapeHtml(o.label)}
                    </label>
                `).join('');
                return `
                    <div class="bg-white border border-gray-200 rounded-xl p-4">
                        <div class="text-sm font-medium text-gray-700 mb-2">${label}</div>
                        <div class="space-y-2">${items}</div>
                    </div>
                `;
            }

            return `
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="form_data[${key}]" value="1">
                        ${label}
                    </label>
                </div>
            `;
        }

        const typeMap = {
            text: 'text',
            email: 'email',
            number: 'number',
        };
        const inputType = typeMap[field.type] || 'text';

        return `
            <div class="bg-white border border-gray-200 rounded-xl p-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                <input type="${inputType}" name="form_data[${key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="${placeholderAttr}" ${requiredAttr}>
            </div>
        `;
    }

    function wirePemohonDynamic(container) {
        container.querySelectorAll('.pemohon-select').forEach((select) => {
            const card = select.closest('.bg-white');
            const typeInput = card?.querySelector('.pemohon-type');
            const idInput = card?.querySelector('.pemohon-id');
            if (!typeInput || !idInput) return;

            function sync() {
                const v = select.value || '';
                const [t, id] = v.split(':');
                typeInput.value = t || '';
                idInput.value = id || '';
            }

            select.addEventListener('change', sync);
            sync();
        });
    }

    function renderDynamicFields() {
        const jenisId = document.getElementById('surat_jenis_id')?.value;
        const container = document.getElementById('dynamic-fields');
        const pemohonWrap = document.getElementById('pemohon_wrap');
        const noSuratWrap = document.getElementById('no_surat_wrap');
        if (!container) return;

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

        if (!jenisId) {
            container.innerHTML = `<div class="text-sm text-gray-500">Pilih jenis surat untuk menampilkan form.</div>`;
            if (pemohonWrap) pemohonWrap.innerHTML = '';
            const tanggalWrap = document.getElementById('tanggal_wrap');
            if (tanggalWrap) tanggalWrap.innerHTML = '';
            return;
        }

        if (pemohonWrap) {
            if (pemohonField) {
                pemohonWrap.innerHTML = renderPemohonField(pemohonField);
                pemohonWrap.classList.remove('hidden');
                wirePemohonDynamic(pemohonWrap);
            } else {
                pemohonWrap.innerHTML = '';
                pemohonWrap.classList.add('hidden');
            }
        }

        const tanggalWrap = document.getElementById('tanggal_wrap');
        if (tanggalWrap) {
            if (tanggalField) {
                tanggalWrap.innerHTML = renderField(tanggalField);
                tanggalWrap.classList.remove('hidden');
            } else {
                tanggalWrap.innerHTML = '';
                tanggalWrap.classList.add('hidden');
            }
        }

        container.innerHTML = otherFields.map(renderField).join('') || `<div class="text-sm text-gray-500">Jenis surat ini belum memiliki konfigurasi field.</div>`;
    }

    function initSuratCreate() {
        const jenisSelect = document.getElementById('surat_jenis_id');
        if (!jenisSelect) return;
        if (jenisSelect.dataset.initialized === '1') return;
        jenisSelect.dataset.initialized = '1';

        const noSuratInput = document.getElementById('no_surat');
        if (noSuratInput) {
            noSuratInput.addEventListener('input', () => {
                noSuratInput.dataset.userEdited = '1';
            });
        }

        jenisSelect.addEventListener('change', () => {
            refreshNoSurat();
            renderDynamicFields();
        });

        refreshNoSurat();
        renderDynamicFields();
    }

    // Standardized Init Pattern
    if (document.readyState !== 'loading') {
        initSuratCreate();
    } else {
        document.addEventListener('DOMContentLoaded', initSuratCreate);
    }
    window.addEventListener('page-loaded', initSuratCreate);
    })();
</script>
@endsection
