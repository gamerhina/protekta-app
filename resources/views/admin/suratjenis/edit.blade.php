@extends('layouts.app')

@section('title', 'Edit Jenis Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Edit Jenis Surat</h1>
                <p class="text-sm text-gray-500">Kelola jenis surat dan template DOCX.</p>
            </div>
            <a href="{{ route('admin.suratjenis.index') }}" class="btn-pill btn-pill-secondary">Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.suratjenis.update', $suratJenis) }}">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input name="nama" value="{{ old('nama', $suratJenis->nama) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode</label>
                    <input name="kode" value="{{ old('kode', $suratJenis->kode) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', $suratJenis->aktif) ? 'checked' : '' }}>
                        <label for="aktif" class="text-sm text-gray-700 font-medium">Aktif</label>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="allow_download" value="1" id="allow_download" {{ old('allow_download', $suratJenis->allow_download) ? 'checked' : '' }}>
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

                    @php
                        $fields = old('form_fields', (is_array($suratJenis->form_fields) ? $suratJenis->form_fields : []));
                    @endphp

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
                                    <th class="px-4 py-3 bg-gray-50 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="fields-body" class="divide-y divide-gray-100 bg-white">
                                @if(empty($fields))
                                    <tr id="no-fields-row">
                                        <td colspan="8" class="px-6 py-6 text-center text-sm text-gray-500">Belum ada field. Klik “Tambah Field”.</td>
                                    </tr>
                                @else
                                    @foreach($fields as $i => $f)
                                        @php
                                            $type = $f['type'] ?? 'text';
                                            $optionsText = '';
                                            if (isset($f['options']) && is_array($f['options'])) {
                                                $optionsText = collect($f['options'])->map(fn($o) => ($o['value'] ?? '') . '|' . ($o['label'] ?? ($o['value'] ?? '')))->implode("\n");
                                            } elseif (isset($f['options']) && is_string($f['options'])) {
                                                $optionsText = $f['options'];
                                            }
                                            $extText = '';
                                            if (isset($f['extensions']) && is_array($f['extensions'])) {
                                                $extText = implode(',', $f['extensions']);
                                            } elseif (isset($f['extensions']) && is_string($f['extensions'])) {
                                                $extText = $f['extensions'];
                                            }
                                        @endphp
                                        <tr class="field-row">
                                            <td class="px-2 py-3 align-middle text-center cursor-move text-gray-400 hover:text-gray-600 drag-handle">
                                                <i class="fas fa-grip-vertical"></i>
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <input name="form_fields[{{ $i }}][label]" value="{{ $f['label'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <input name="form_fields[{{ $i }}][key]" value="{{ $f['key'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" required>
                                                <div class="text-xs text-gray-400 mt-1">Gunakan snake_case.</div>
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <select name="form_fields[{{ $i }}][type]" class="field-type w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                                    @foreach([
                                                        'pemohon' => 'Pemohon (Dropdown Mahasiswa/Dosen)',
                                                        'auto_no_surat' => 'Nomor Surat (Auto)',
                                                        'date' => 'Tanggal (Date)',
                                                        'text' => 'Text',
                                                        'textarea' => 'Textarea',
                                                        'email' => 'Email',
                                                        'number' => 'Number',
                                                        'select' => 'Dropdown (Select)',
                                                        'radio' => 'Radio Button',
                                                        'checkbox' => 'Checklist (Checkbox)',
                                                        'file' => 'File Upload',
                                                    ] as $v => $lbl)
                                                        <option value="{{ $v }}" {{ ($type === $v) ? 'selected' : '' }}>{{ $lbl }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <input name="form_fields[{{ $i }}][placeholder]" value="{{ $f['placeholder'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="(opsional)">
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <div class="space-y-2">
                                                    @php
                                                        $pemohonSources = $f['pemohon_sources'] ?? $f['sources'] ?? ['mahasiswa', 'dosen'];
                                                        if (!is_array($pemohonSources)) {
                                                            $pemohonSources = ['mahasiswa', 'dosen'];
                                                        }
                                                    @endphp
                                                    <div class="pemohon-wrap {{ $type === 'pemohon' ? '' : 'hidden' }}">
                                                        <div class="text-xs text-gray-500 mb-1">Sumber pemohon</div>
                                                        <label class="inline-flex items-center gap-2 text-xs mr-3">
                                                            <input type="checkbox" name="form_fields[{{ $i }}][pemohon_sources][]" value="mahasiswa" {{ in_array('mahasiswa', $pemohonSources, true) ? 'checked' : '' }}>
                                                            Mahasiswa
                                                        </label>
                                                        <label class="inline-flex items-center gap-2 text-xs">
                                                            <input type="checkbox" name="form_fields[{{ $i }}][pemohon_sources][]" value="dosen" {{ in_array('dosen', $pemohonSources, true) ? 'checked' : '' }}>
                                                            Dosen
                                                        </label>
                                                    </div>
                                                    <div class="options-wrap {{ in_array($type, ['select','radio','checkbox'], true) ? '' : 'hidden' }}">
                                                        <textarea name="form_fields[{{ $i }}][options]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="value|label\nvalue2|label2">{{ $optionsText }}</textarea>
                                                    </div>
                                                    <div class="file-wrap {{ $type === 'file' ? '' : 'hidden' }}">
                                                        <div class="grid grid-cols-1 gap-2">
                                                            <input name="form_fields[{{ $i }}][extensions]" value="{{ $extText }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="pdf,jpg,png">
                                                            <input type="number" min="0" name="form_fields[{{ $i }}][max_kb]" value="{{ $f['max_kb'] ?? '' }}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="Max KB (contoh 5120)">
                                                        </div>
                                                    </div>
                                                    <div class="text-xs text-gray-400" data-hint>
                                                        {{ $type === 'pemohon' ? 'Akan menghasilkan field Pemohon (pilih mahasiswa/dosen) pada form permohonan.' : ($type === 'auto_no_surat' ? 'Nomor surat otomatis mengikuti jenis surat.' : '') }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 align-top">
                                                <input type="checkbox" name="form_fields[{{ $i }}][required]" value="1" {{ !empty($f['required']) ? 'checked' : '' }}>
                                            </td>
                                            <td class="px-4 py-3 align-top text-right">
                                                <button type="button" class="remove-field text-red-600 hover:underline text-sm">Hapus</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-gray-500 mt-3">
                        Catatan opsi untuk <strong>Select/Radio/Checkbox</strong>: isi per baris format <span class="font-mono">value|label</span>.
                        Untuk <strong>File</strong>: isi ekstensi dipisah koma (contoh: <span class="font-mono">pdf,jpg,png</span>) dan max size dalam KB.
                    </p>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button class="btn-pill btn-pill-primary" type="submit">Simpan</button>
            </div>
        </form>

        <div class="mt-10 pt-8 border-t">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Template Surat</h2>
            @if($suratJenis->template)
                <div class="flex items-center justify-between gap-4 bg-slate-50 border border-slate-200 rounded-xl p-4">
                    <div>
                        <div class="text-sm font-medium text-slate-800">{{ $suratJenis->template->nama }}</div>
                        <div class="text-xs text-slate-600">{{ $suratJenis->template->file_path }}</div>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.surattemplate.index', $suratJenis) }}" class="btn-pill btn-pill-secondary">Daftar Template</a>
                        <a href="{{ route('admin.surattemplate.edit', [$suratJenis, $suratJenis->template]) }}" class="btn-pill btn-pill-secondary">Kelola Template</a>
                    </div>
                </div>
            @else
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
                    <div class="text-sm text-amber-900">Belum ada template untuk jenis surat ini.</div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('admin.surattemplate.index', $suratJenis) }}" class="btn-pill btn-pill-secondary">Daftar Template</a>
                        <a href="{{ route('admin.surattemplate.create', $suratJenis) }}" class="btn-pill btn-pill-primary">Upload Template</a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    function buildTypeOptions(selected) {
        const types = [
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
        return types.map(t => `<option value="${t.value}" ${selected === t.value ? 'selected' : ''}>${t.label}</option>`).join('');
    }

    function buildRow(index) {
        return `
            <tr class="field-row">
                <td class="px-2 py-3 align-middle text-center cursor-move text-gray-400 hover:text-gray-600 drag-handle">
                    <i class="fas fa-grip-vertical"></i>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][label]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Contoh: Tujuan/Instansi" required>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][key]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="contoh: tujuan" required>
                    <div class="text-xs text-gray-400 mt-1">Gunakan snake_case.</div>
                </td>
                <td class="px-4 py-3 align-top">
                    <select name="form_fields[${index}][type]" class="field-type w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        ${buildTypeOptions('text')}
                    </select>
                </td>
                <td class="px-4 py-3 align-top">
                    <input name="form_fields[${index}][placeholder]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="(opsional)">
                </td>
                <td class="px-4 py-3 align-top">
                    <div class="space-y-2">
                        <div class="pemohon-wrap hidden">
                            <div class="text-xs text-gray-500 mb-1">Sumber pemohon</div>
                            <label class="inline-flex items-center gap-2 text-xs mr-3">
                                <input type="checkbox" name="form_fields[${index}][pemohon_sources][]" value="mahasiswa" checked>
                                Mahasiswa
                            </label>
                            <label class="inline-flex items-center gap-2 text-xs">
                                <input type="checkbox" name="form_fields[${index}][pemohon_sources][]" value="dosen" checked>
                                Dosen
                            </label>
                        </div>
                        <div class="options-wrap hidden">
                            <textarea name="form_fields[${index}][options]" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="value|label\nvalue2|label2"></textarea>
                        </div>
                        <div class="file-wrap hidden">
                            <div class="grid grid-cols-1 gap-2">
                                <input name="form_fields[${index}][extensions]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="pdf,jpg,png">
                                <input type="number" min="0" name="form_fields[${index}][max_kb]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs" placeholder="Max KB (contoh 5120)">
                            </div>
                        </div>
                        <div class="text-xs text-gray-400" data-hint></div>
                    </div>
                </td>
                <td class="px-4 py-3 align-top">
                    <input type="checkbox" name="form_fields[${index}][required]" value="1">
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
            const emptyRow = document.getElementById('no-fields-row');
            if (!emptyRow) return;
            const hasRows = body.querySelectorAll('tr.field-row').length > 0;
            emptyRow.style.display = hasRows ? 'none' : '';
        }

        addBtn.addEventListener('click', () => {
            const idx = nextIndex();
            body.insertAdjacentHTML('beforeend', buildRow(idx));
            ensureEmptyRow();
            reindexFields(); // Ensure new row has correct index
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

        // Initialize existing rows visibility
        body.querySelectorAll('tr.field-row').forEach(updateRowVisibility);
        ensureEmptyRow();
    }

    // Initialize on both DOMContentLoaded (initial load) and page-loaded (AJAX)
    document.addEventListener('DOMContentLoaded', initFormFieldsBuilder);
    window.addEventListener('page-loaded', initFormFieldsBuilder);

    // Also try immediately in case the script is injected via AJAX and dependencies are already ready
    initFormFieldsBuilder();
</script>
@endsection
