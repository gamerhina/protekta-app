@extends('layouts.app')

@section('title', 'Buat Jenis Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Buat Jenis Surat</h1>

        <form method="POST" action="{{ route('admin.suratjenis.store') }}">
            @csrf

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Surat</label>
                    <input name="nama" value="{{ old('nama') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode (tanpa spasi)</label>
                    <input name="kode" value="{{ old('kode') }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', '1') ? 'checked' : '' }}>
                        <label for="aktif" class="text-sm text-gray-700 font-medium">Aktif</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="allow_download" value="1" id="allow_download" {{ old('allow_download', '1') ? 'checked' : '' }}>
                        <label for="allow_download" class="text-sm text-gray-700 font-medium">Izinkan Pemohon Unduh Surat</label>
                    </div>
                </div>

                <div class="pt-6 border-t">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-800">Form Permohonan (Custom Fields)</h2>
                            <p class="text-sm text-gray-500">Tambahkan field sesuai kebutuhan: nama, jenis isian, placeholder, aturan, wajib/tidak.</p>
                        </div>
                        <button type="button" id="add-field" class="btn-pill btn-pill-secondary">
                            <i class="fas fa-plus"></i> Tambah Field
                        </button>
                    </div>

                    <div class="overflow-hidden border border-gray-100 rounded-2xl shadow-sm">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-2 py-3 bg-gray-50 w-8"></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Label</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Key</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Tipe</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Placeholder</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Aturan</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Wajib</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="fields-body" class="divide-y divide-gray-100 bg-white">
                                <tr id="no-fields-row">
                                    <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada field. Klik “Tambah Field”.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Catatan opsi untuk <strong>Select/Radio/Checkbox</strong>: isi per baris format <span class="font-mono">value|label</span> (contoh: <span class="font-mono">mhs|Mahasiswa</span>).
                        Untuk <strong>File</strong>: isi ekstensi dipisah koma (contoh: <span class="font-mono">pdf,jpg,png</span>) dan max size dalam KB.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.suratjenis.index') }}" class="btn-pill btn-pill-secondary">Batal</a>
                <button class="btn-pill btn-pill-primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const fieldTypes = [
        { value: 'pemohon', label: 'Pemohon (Pilih Mahasiswa/Dosen)' },
        { value: 'auto_no_surat', label: 'Nomor Surat (Auto)' },
        { value: 'date', label: 'Tanggal (Date)' },
        { value: 'text', label: 'Text' },
        { value: 'textarea', label: 'Textarea' },
        { value: 'email', label: 'Email' },
        { value: 'number', label: 'Number' },
        { value: 'select', label: 'Dropdown (Select)' },
        { value: 'radio', label: 'Radio Button' },
        { value: 'checkbox', label: 'Checklist (Checkbox)' },
        { value: 'file', label: 'File Upload' },
    ];

    function buildTypeOptions(selected) {
        return fieldTypes.map(t => `<option value="${t.value}" ${selected === t.value ? 'selected' : ''}>${t.label}</option>`).join('');
    }

    function buildRow(index, data = {}) {
        const pemohonSources = Array.isArray(data.pemohon_sources)
            ? data.pemohon_sources
            : (Array.isArray(data.sources) ? data.sources : ['mahasiswa','dosen']);
        const label = data.label || '';
        const key = data.key || '';
        const type = data.type || 'text';
        const placeholder = data.placeholder || '';
        const required = data.required ? 'checked' : '';
        const options = Array.isArray(data.options)
            ? data.options.map(o => `${o.value}|${o.label}`).join('\n')
            : (data.options || '');
        const extensions = Array.isArray(data.extensions) ? data.extensions.join(',') : (data.extensions || '');
        const maxKb = data.max_kb || '';

        const rulesCell = `
            <div class="space-y-2">
                <div class="pemohon-wrap ${type === 'pemohon' ? '' : 'hidden'}">
                    <div class="text-xs text-gray-500 mb-1">Sumber pemohon</div>
                    <label class="inline-flex items-center gap-2 text-xs mr-3">
                        <input type="checkbox" name="form_fields[${index}][pemohon_sources][]" value="mahasiswa" ${pemohonSources.includes('mahasiswa') ? 'checked' : ''}>
                        Mahasiswa
                    </label>
                    <label class="inline-flex items-center gap-2 text-xs">
                        <input type="checkbox" name="form_fields[${index}][pemohon_sources][]" value="dosen" ${pemohonSources.includes('dosen') ? 'checked' : ''}>
                        Dosen
                    </label>
                </div>
                <div class="options-wrap ${['select','radio','checkbox'].includes(type) ? '' : 'hidden'}">
                    <textarea name="form_fields[${index}][options]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="value|label\nvalue2|label2">${options || ''}</textarea>
                </div>
                <div class="file-wrap ${type === 'file' ? '' : 'hidden'}">
                    <div class="grid grid-cols-1 gap-2">
                        <input name="form_fields[${index}][extensions]" value="${extensions}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="pdf,jpg,png">
                        <input type="number" min="0" name="form_fields[${index}][max_kb]" value="${maxKb}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="Max KB (contoh 5120)">
                    </div>
                </div>
                <div class="text-xs text-gray-400" data-hint>
                    ${type === 'pemohon' ? 'Akan menghasilkan field Pemohon (pilih mahasiswa/dosen) pada form permohonan.' : ''}
                    ${type === 'auto_no_surat' ? 'Nomor surat otomatis mengikuti jenis surat.' : ''}
                </div>
            </div>
        `;

        return `
            <tr class="field-row">
                <td class="px-2 py-3 align-middle text-center cursor-move text-gray-400 hover:text-gray-600 drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][label]" value="${label}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Contoh: Tujuan/Instansi" required>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][key]" value="${key}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="contoh: tujuan" required>
                    <div class="text-xs text-gray-400 mt-1">Gunakan snake_case.</div>
                </td>
                <td class="px-4 py-3 align-top">
                    <select name="form_fields[${index}][type]" class="field-type w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        ${buildTypeOptions(type)}
                    </select>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][placeholder]" value="${placeholder}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="(opsional)">
                </td>
                <td class="px-4 py-3 align-top">${rulesCell}</td>
                <td class="px-4 py-3 align-top">
                    <input type="checkbox" name="form_fields[${index}][required]" value="1" ${required}>
                </td>
                <td class="px-4 py-3 align-top text-right">
                    <button type="button" class="remove-field text-red-600 hover:underline text-sm">Hapus</button>
                </td>
            </tr>
        `;
    }

    function updateRowVisibility(row) {
        const type = row.querySelector('.field-type')?.value;
        const pemohonWrap = row.querySelector('.pemohon-wrap');
        const optionsWrap = row.querySelector('.options-wrap');
        const fileWrap = row.querySelector('.file-wrap');
        const hint = row.querySelector('[data-hint]');

        if (pemohonWrap) {
            pemohonWrap.classList.toggle('hidden', type !== 'pemohon');
        }
        if (optionsWrap) {
            optionsWrap.classList.toggle('hidden', !['select','radio','checkbox'].includes(type));
        }
        if (fileWrap) {
            fileWrap.classList.toggle('hidden', type !== 'file');
        }
        if (hint) {
            hint.textContent = type === 'pemohon'
                ? 'Akan menghasilkan field Pemohon (pilih mahasiswa/dosen) pada form permohonan.'
                : (type === 'auto_no_surat' ? 'Nomor surat otomatis mengikuti jenis surat.' : '');
        }
    }

    function reindexFields() {
        const body = document.getElementById('fields-body');
        const rows = body.querySelectorAll('tr.field-row');
        rows.forEach((row, idx) => {
            row.querySelectorAll('input, select, textarea').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace(/form_fields\[\d+\]/, `form_fields[${idx}]`));
                }
            });
        });
    }

    function initFormFieldsBuilder() {
        const addBtn = document.getElementById('add-field');
        const body = document.getElementById('fields-body');
        const emptyRow = document.getElementById('no-fields-row');
        if (!addBtn || !body) return;

        if (addBtn.dataset.initialized === '1') return;
        addBtn.dataset.initialized = '1';

        // Initialize Sortable
        new Sortable(body, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-blue-50',
            onEnd: function() {
                reindexFields();
            }
        });

        function nextIndex() {
            return body.querySelectorAll('tr.field-row').length;
        }

        function ensureEmptyRow() {
            const hasRows = body.querySelectorAll('tr.field-row').length > 0;
            if (emptyRow) {
                emptyRow.style.display = hasRows ? 'none' : '';
            }
        }

        addBtn.addEventListener('click', () => {
            const idx = nextIndex();
            body.insertAdjacentHTML('beforeend', buildRow(idx));
            ensureEmptyRow();
            reindexFields(); // Ensure correct index
        });

        body.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-field');
            if (!btn) return;
            btn.closest('tr')?.remove();
            ensureEmptyRow();
            reindexFields();
        });

        body.addEventListener('change', (e) => {
            if (!e.target.classList.contains('field-type')) return;
            updateRowVisibility(e.target.closest('tr'));
        });

        ensureEmptyRow();
    }

    // Initialize via Protekta to ensure dependencies (Sortable) are ready
    window.Protekta.registerInit(initFormFieldsBuilder);
</script>
@endsection
