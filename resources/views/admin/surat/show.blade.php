@extends('layouts.app')

@section('title', 'Detail Surat')

@section('content')
<div id="surat-show-container" data-id="{{ $surat->id }}" class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Detail Surat</h1>
                <p class="text-sm text-gray-500">Jenis: <strong>{{ $surat->jenis->nama ?? '-' }}</strong></p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.surat.index') }}" class="btn-pill btn-pill-secondary">Kembali</a>
                <form method="POST" action="{{ route('admin.surat.destroy', $surat) }}" onsubmit="return confirm('Hapus permohonan surat ini?')">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 shadow-sm"
                        title="Hapus"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="mb-8 p-6 bg-slate-50 border border-slate-200 rounded-2xl">
            <h2 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4">Preview & Pengiriman</h2>
            @if($surat->jenis && $surat->jenis->template)
                <div class="flex flex-col gap-2">
                    <a id="btn-download-docx-surat" class="btn-pill btn-pill-secondary flex items-center gap-2" href="{{ route('admin.surattemplate.download', [$surat->jenis, $surat->jenis->template, $surat]) }}" download data-no-ajax target="_blank">
                        <i class="fas fa-file-word text-blue-500"></i> Download DOCX
                    </a>

                    <form id="email-send-form" method="POST" action="{{ route('admin.surattemplate.send', [$surat->jenis, $surat->jenis->template, $surat]) }}" class="flex-1 flex flex-col md:flex-row items-center gap-3">
                        @csrf
                        <?php 
                            $applicantEmail = '';
                            if ($surat->pemohon_type === 'mahasiswa' && $surat->pemohonMahasiswa) {
                                $applicantEmail = $surat->pemohonMahasiswa->email;
                            } elseif ($surat->pemohon_type === 'dosen' && $surat->pemohonDosen) {
                                $applicantEmail = $surat->pemohonDosen->email;
                            }
                        ?>
                        <div class="relative flex-1 w-full">
                            <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input name="to" id="input-recipient" value="{{ $surat->penerima_email ?? $applicantEmail }}" class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm bg-white" placeholder="{{ $applicantEmail ?: 'email@penerima.com' }}" required title="Email Penerima">
                        </div>
                        <button class="btn-pill btn-pill-primary w-full md:w-auto px-6 text-sm flex items-center justify-center gap-2" type="button" id="btn-show-email-preview">
                            <i class="fa-solid fa-paper-plane"></i> 
                            Kirim WA / Email
                        </button>
                        {{-- Hidden fields for subject and body from preview modal --}}
                        <input type="hidden" name="subject" id="hidden-email-subject">
                        <textarea name="body" id="hidden-email-body" class="hidden"></textarea>
                    </form>
                </div>
            @else
                <div class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-center gap-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Template surat belum tersedia untuk jenis permohonan ini.</span>
                </div>
            @endif
        </div>

        <form method="POST" action="{{ route('admin.surat.update', $surat) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="bg-white border border-gray-200 rounded-2xl p-6 mb-8 shadow-sm">
                <h2 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Informasi Utama</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Nomor Surat</label>
                        <input name="no_surat" id="no_surat" value="{{ old('no_surat', $surat->no_surat) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm" placeholder="Otomatis">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Tanggal Surat</label>
                        <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', $surat->tanggal_surat?->format('Y-m-d')) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm" required>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Status Progress</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white text-sm font-medium" required>
                            @foreach(['diajukan','diproses','dikirim','ditolak'] as $st)
                                <option value="{{ $st }}" {{ old('status', $surat->status) === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-8">
                <div class="flex items-center gap-3 mb-6">
                    <h2 class="text-lg font-bold text-gray-800">Isian Data Permohonan</h2>
                    <div class="h-px flex-1 bg-gray-100"></div>
                </div>
                
                <div id="dynamic-fields-admin" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Dynamic fields will be injected here -->
                    <div class="md:col-span-2 py-10 text-center text-gray-400 italic">
                        <i class="fas fa-spinner fa-spin mr-2"></i> Sinkronisasi Form...
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between pt-6 border-t">
                <div class="text-sm text-gray-400">
                    Sistem Ref: <strong>#{{ $surat->id }}</strong>
                </div>
                <div class="flex items-center gap-3">
                    <button class="btn-pill btn-pill-primary px-10 shadow-lg shadow-blue-100" type="submit">Simpan Perubahan</button>
                </div>
            </div>
        </form>
    </div>

    {{-- Scripts and Modals inside content section to ensure AJAX compatibility --}}
    <!-- Email Preview Modal -->
    <div id="email-preview-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" data-modal-toggle="email-preview-modal"></div>
            <div class="relative bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:max-w-2xl sm:w-full z-10">
                <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex items-center justify-between mb-4 border-b pb-4">
                        <h3 class="text-lg font-bold text-gray-900" id="modal-title">Kirim WA / Email</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-500" data-modal-toggle="email-preview-modal"><i class="fas fa-times"></i></button>
                    </div>
                    <div id="modal-loading-state" class="py-10 text-center"><i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-3"></i><p class="text-xs text-gray-400">Menyiapkan pratinjau...</p></div>
                    <div id="modal-editor-state" class="space-y-4 hidden text-sm">
                        <div><label class="block text-[10px] font-bold text-gray-400 uppercase">Penerima</label><div id="preview-recipient" class="font-semibold p-2 bg-gray-50 rounded"></div></div>
                        <div><label class="block text-[10px] font-bold text-gray-400 uppercase">Subjek</label><input type="text" id="preview-subject" class="w-full px-3 py-2 border rounded-lg"></div>
                        <div><label class="block text-[10px] font-bold text-gray-400 uppercase">Isi Pesan</label><textarea id="preview-body" rows="6" class="w-full px-3 py-2 border rounded-lg"></textarea></div>
                        <div class="pt-3 border-t"><label class="block text-[10px] font-bold text-blue-500 uppercase">Kirim WhatsApp Ke:</label>
                            <select id="wa-recipient-select-surat" class="w-full px-3 py-2 border rounded-lg bg-blue-50 text-blue-700 font-semibold">
                                @if($surat->pemohon_type === 'mahasiswa' && $surat->pemohonMahasiswa && $surat->pemohonMahasiswa->wa) <option value="{{ $surat->pemohonMahasiswa->wa }}">Pemohon: {{ $surat->pemohonMahasiswa->nama }}</option> @endif
                                @if($surat->pemohon_type === 'dosen' && $surat->pemohonDosen && $surat->pemohonDosen->wa) <option value="{{ $surat->pemohonDosen->wa }}">Pemohon: {{ $surat->pemohonDosen->nama }}</option> @endif
                                <option value="">Pilih Manual</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row-reverse gap-3 rounded-b-2xl">
                    <button type="button" id="btn-confirm-send" class="btn-pill btn-pill-info px-8"><i class="fa-solid fa-paper-plane mr-2"></i> Kirim Email</button>
                    <button type="button" id="btn-send-wa" class="btn-pill btn-pill-success px-8"><i class="fa-brands fa-whatsapp mr-2"></i> Kirim WA</button>
                    <button type="button" class="btn-pill btn-pill-secondary px-8" data-modal-toggle="email-preview-modal">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const suratData = @json($surat->data ?? []);
            const formFields = @json($surat->jenis?->form_fields ?? []);
            const dosens = @json($dosens ?? []);
            const mahasiswas = @json($mahasiswas ?? []);
            const currentPemohonType = @json($surat->pemohon_type ?? '');
            const currentPemohonId = @json($surat->pemohon_type === 'mahasiswa' ? $surat->pemohon_mahasiswa_id : $surat->pemohon_dosen_id);
            const containerId = 'surat-show-container';

            function escapeHtml(str) {
                return String(str ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            // Global function for removing table rows
            window.removeTableRow = function(btn) {
                const row = btn.closest('tr');
                const tbody = row.closest('tbody');
                if (tbody.querySelectorAll('tr').length > 1) {
                    row.remove();
                    // Reindex all table rows in the document
                    document.querySelectorAll('[id$="_body"]').forEach(tb => {
                        const tableId = tb.id.replace('_body', '');
                        const reindexFunc = window['reindexTableRows_' + tableId.replace('table_', '')];
                        if (reindexFunc) reindexFunc();
                    });
                } else {
                    alert('Minimal harus ada 1 baris data.');
                }
            };

            function renderField(field) {
                const key = field.key;
                const label = escapeHtml(field.label);
                const required = field.required ? 'required' : '';
                let value = suratData[key] || '';
                
                if (value === '') {
                    if (key === 'tujuan') value = @json($surat->tujuan);
                    if (key === 'perihal') value = @json($surat->perihal);
                    if (key === 'isi') value = @json($surat->isi);
                    if (key === 'penerima_email') value = @json($surat->penerima_email);
                }

                if (['no_surat', 'tanggal_surat', 'status'].includes(key)) return '';

                if (field.type === 'pemohon') {
                    const sources = Array.isArray(field.pemohon_sources) && field.pemohon_sources.length ? field.pemohon_sources : ['mahasiswa','dosen'];
                    const dosenOptions = (dosens || []).map(d => `<option value="dosen:${d.id}" ${currentPemohonType === 'dosen' && currentPemohonId == d.id ? 'selected' : ''}>${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
                    const mhsOptions = (mahasiswas || []).map(m => `<option value="mahasiswa:${m.id}" ${currentPemohonType === 'mahasiswa' && currentPemohonId == m.id ? 'selected' : ''}>${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
                    
                    // Check if pemohon data exists in suratData (for custom type detection)
                    const pemohonData = suratData[key] || {};
                    const actualType = pemohonData.type || currentPemohonType;
                    const actualId = pemohonData.id || currentPemohonId;
                    const customNama = pemohonData.custom_nama || '';
                    const customNip = pemohonData.custom_nip || '';
                    const isCustom = actualType === 'custom';
                    
                    return `<div class="md:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">${label}</label>
                        <input type="hidden" class="pemohon-type" name="form_data[${escapeHtml(key)}][type]" value="${escapeHtml(actualType)}">
                        <input type="hidden" class="pemohon-id" name="form_data[${escapeHtml(key)}][id]" value="${escapeHtml(actualId)}">
                        <select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md bg-white mb-3">
                            <option value="">Pilih pemohon</option>
                            ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
                            ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
                            <optgroup label="Lainnya">
                                <option value="custom:0" ${isCustom ? 'selected' : ''}>Isi Sendiri</option>
                            </optgroup>
                        </select>
                        <div class="pemohon-custom-inputs ${isCustom ? '' : 'hidden'} space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Lengkap</label>
                                <input type="text" name="form_data[${escapeHtml(key)}][custom_nama]" value="${escapeHtml(customNama)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Masukkan nama lengkap" ${isCustom ? 'required' : ''}>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">NIP/NPM/Identitas</label>
                                <input type="text" name="form_data[${escapeHtml(key)}][custom_nip]" value="${escapeHtml(customNip)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="Masukkan NIP/NPM" ${isCustom ? 'required' : ''}>
                            </div>
                        </div>
                    </div>`;
                }

                if (field.type === 'textarea') {
                    return `<div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <textarea name="form_data[${escapeHtml(key)}]" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">${escapeHtml(value)}</textarea>
                    </div>`;
                }

                if (field.type === 'date') {
                    return `<div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <input type="date" name="form_data[${escapeHtml(key)}]" value="${escapeHtml(value)}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                    </div>`;
                }

                if (field.type === 'file') {
                    const statusClass = value ? 'emerald' : 'gray';
                    const statusText = value ? 'FILE ADA' : 'BELUM ADA';
                    const buttonText = value ? 'Ganti File' : 'Upload File';

                    return `
                        <div class="md:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-200 group hover:border-blue-200 transition-all">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 truncate">${label}</h3>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">OPSIONAL • Simpan FILE</p>
                                </div>
                                <span class="flex-shrink-0 bg-${statusClass}-100 text-${statusClass}-700 text-[10px] font-bold px-2 py-1 rounded-full">
                                    ${statusText}
                                </span>
                            </div>
                            
                            <div class="relative group/input">
                                <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                    ${buttonText}
                                </label>
                                <input type="file" name="form_files[${escapeHtml(key)}]" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all">
                            </div>

                            ${value ? `
                                <div class="mt-4 pt-3 border-t border-slate-200">
                                    <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">File Saat Ini:</p>
                                    <a href="/storage/${value}" target="_blank" class="flex items-center text-xs font-mono text-blue-600 break-all bg-white p-2 rounded-lg border border-slate-200 hover:bg-blue-50 transition-colors">
                                        <i class="fas fa-file-download mr-2 text-blue-500"></i>
                                        <span>Lihat File</span>
                                    </a>
                                </div>
                            ` : ''}
                        </div>
                    `;
                }


                if (field.type === 'select' || field.type === 'radio') {
                    const options = Array.isArray(field.options) ? field.options : [];
                    const optionsHtml = options.map(o => `<option value="${escapeHtml(o.value)}" ${value == o.value ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('');
                    return `<div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <select name="form_data[${escapeHtml(key)}]" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                            <option value="">Pilih</option>
                            ${optionsHtml}
                        </select>
                    </div>`;
                }

                if (field.type === 'table') {
                    const columns = Array.isArray(field.columns) ? field.columns : [];
                    if (!columns.length) {
                        return `<div class="md:col-span-2 bg-amber-50 border border-amber-200 rounded-xl p-4">
                            <div class="text-sm text-amber-800">Tabel "${label}" belum dikonfigurasi.</div>
                        </div>`;
                    }

                    const tableId = `table_${key}`;
                    const tableData = Array.isArray(value) ? value : [];

                    
                    // Helper function to render table cell
                    const renderTableCell = (col, rowIdx, cellValue = '') => {
                        const colKey = col.key;
                        const colLabel = col.label;
                        const colType = col.type || 'text';
                        
                        if (colType === 'pemohon') {
                            const sources = Array.isArray(col.pemohon_sources) && col.pemohon_sources.length
                                ? col.pemohon_sources
                                : ['mahasiswa','dosen'];
                            
                            // cellValue for pemohon is an object {type, id}
                            const pemohonType = (cellValue && cellValue.type) || '';
                            const pemohonId = (cellValue && cellValue.id) || '';
                            const selectedValue = pemohonType && pemohonId ? `${pemohonType}:${pemohonId}` : '';
                            
                            const dosenOptions = (dosens || []).map(d => `<option value="dosen:${d.id}" ${selectedValue === `dosen:${d.id}` ? 'selected' : ''}>${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
                            const mhsOptions = (mahasiswas || []).map(m => `<option value="mahasiswa:${m.id}" ${selectedValue === `mahasiswa:${m.id}` ? 'selected' : ''}>${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
                            const optionsHtml = `
                                ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
                                ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
                            `;
                            
                            return `<td class="px-4 py-2">
                                <input type="hidden" class="pemohon-type" name="form_data[${key}][${rowIdx}][${escapeHtml(colKey)}][type]" value="${escapeHtml(pemohonType)}">
                                <input type="hidden" class="pemohon-id" name="form_data[${key}][${rowIdx}][${escapeHtml(colKey)}][id]" value="${escapeHtml(pemohonId)}">
                                <select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <option value="">Pilih ${escapeHtml(colLabel)}</option>
                                    ${optionsHtml}
                                </select>
                            </td>`;
                        }
                        
                        // Default: text input
                        return `<td class="px-4 py-2">
                            <input type="text" name="form_data[${key}][${rowIdx}][${escapeHtml(colKey)}]" value="${escapeHtml(cellValue)}" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="${escapeHtml(colLabel)}">
                        </td>`;
                    };
                    
                    const rowsHtml = tableData.length > 0 ? tableData.map((row, idx) => {
                        const cellsHtml = columns.map(col => renderTableCell(col, idx, row[col.key] || '')).join('');
                        
                        return `<tr class="table-row">
                            <td class="px-4 py-2 text-center text-sm text-gray-500 row-number">${idx + 1}</td>
                            ${cellsHtml}
                            <td class="px-4 py-2 text-center">
                                <button type="button" onclick="removeTableRow(this)" class="text-red-600 hover:text-red-800" title="Hapus Baris">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                    }).join('') : `<tr class="table-row">
                        <td class="px-4 py-2 text-center text-sm text-gray-500 row-number">1</td>
                        ${columns.map(col => renderTableCell(col, 0, '')).join('')}
                        <td class="px-4 py-2 text-center">
                            <button type="button" onclick="removeTableRow(this)" class="text-red-600 hover:text-red-800" title="Hapus Baris">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;

                    // Register table functions
                    window[`addTableRow_${key}`] = (function(cols, fieldKey, tableBodyId) {
                        return function() {
                            const tbody = document.getElementById(tableBodyId);
                            const rowCount = tbody.querySelectorAll('tr').length;
                            const newRow = document.createElement('tr');
                            newRow.className = 'table-row';
                            
                            let cellsHtml = '<td class="px-4 py-2 text-center text-sm text-gray-500 row-number">' + (rowCount + 1) + '</td>';
                            cols.forEach(col => {
                                const colKey = col.key;
                                const colLabel = col.label;
                                const colType = col.type || 'text';
                                
                                if (colType === 'pemohon') {
                                    const sources = Array.isArray(col.pemohon_sources) && col.pemohon_sources.length
                                        ? col.pemohon_sources
                                        : ['mahasiswa','dosen'];
                                    
                                    const dosenOptions = dosens.map(d => '<option value="dosen:' + d.id + '">' + escapeHtml(d.nama) + ' (' + escapeHtml(d.nip) + ')</option>').join('');
                                    const mhsOptions = mahasiswas.map(m => '<option value="mahasiswa:' + m.id + '">' + escapeHtml(m.nama) + ' (' + escapeHtml(m.npm) + ')</option>').join('');
                                    
                                    let optionsHtml = '';
                                    if (sources.includes('mahasiswa')) {
                                        optionsHtml += '<optgroup label="Mahasiswa">' + mhsOptions + '</optgroup>';
                                    }
                                    if (sources.includes('dosen')) {
                                        optionsHtml += '<optgroup label="Dosen">' + dosenOptions + '</optgroup>';
                                    }
                                    
                                    cellsHtml += '<td class="px-4 py-2">' +
                                        '<input type="hidden" class="pemohon-type" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + '][type]" value="">' +
                                        '<input type="hidden" class="pemohon-id" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + '][id]" value="">' +
                                        '<select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md text-sm">' +
                                            '<option value="">Pilih ' + colLabel + '</option>' +
                                            optionsHtml +
                                        '</select>' +
                                    '</td>';
                                } else {
                                    cellsHtml += '<td class="px-4 py-2">' +
                                        '<input type="text" name="form_data[' + fieldKey + '][' + rowCount + '][' + colKey + ']" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="' + colLabel + '">' +
                                    '</td>';
                                }
                            });
                            
                            cellsHtml += '<td class="px-4 py-2 text-center">' +
                                '<button type="button" onclick="removeTableRow(this)" class="text-red-600 hover:text-red-800" title="Hapus Baris">' +
                                    '<i class="fas fa-trash"></i>' +
                                '</button>' +
                            '</td>';
                            
                            newRow.innerHTML = cellsHtml;
                            tbody.appendChild(newRow);
                            
                            // Wire pemohon selects in the new row
                            newRow.querySelectorAll('.pemohon-select').forEach((select) => {
                                const cell = select.closest('td');
                                const typeInput = cell?.querySelector('.pemohon-type');
                                const idInput = cell?.querySelector('.pemohon-id');
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
                            
                            const reindexFunc = window['reindexTableRows_' + fieldKey];
                            if (reindexFunc) reindexFunc();
                        };
                    })(columns, key, tableId + '_body');

                    window[`reindexTableRows_${key}`] = (function(tableBodyId) {
                        return function() {
                            const tbody = document.getElementById(tableBodyId);
                            if (!tbody) return;
                            const rows = tbody.querySelectorAll('tr');
                            rows.forEach((row, idx) => {
                                // Update row number
                                const numCell = row.querySelector('.row-number');
                                if (numCell) numCell.textContent = idx + 1;

                                row.querySelectorAll('input, select').forEach(input => {
                                    const name = input.getAttribute('name');
                                    if (name) {
                                        input.setAttribute('name', name.replace(/(\[[^\]]+\])\[\d+\]/, '$1[' + idx + ']'));
                                    }
                                });
                            });
                        };
                    })(tableId + '_body');

                    const headerCells = '<th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 bg-gray-50 w-12 text-center">No</th>' + 
                                       columns.map(col => `<th class="px-4 py-2 text-left text-xs font-semibold text-gray-700 bg-gray-50">${escapeHtml(col.label)}</th>`).join('');

                    return `<div class="md:col-span-2 bg-white border border-gray-200 rounded-xl p-4">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-700 font-bold">${label}</label>
                            <button type="button" onclick="addTableRow_${key}()" class="btn-pill btn-pill-secondary text-xs px-3 py-1">
                                <i class="fas fa-plus mr-1"></i> Tambah Baris
                            </button>
                        </div>
                        <div class="overflow-x-auto border border-gray-200 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200" id="${tableId}">
                                <thead>
                                    <tr>
                                        ${headerCells}
                                        <th class="px-4 py-2 w-16 bg-gray-50"></th>
                                    </tr>
                                </thead>
                                <tbody id="${tableId}_body" class="divide-y divide-gray-100">
                                    ${rowsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>`;
                }

                return `<div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <input type="${field.type === 'number' ? 'number' : 'text'}" name="form_data[${escapeHtml(key)}]" value="${escapeHtml(value)}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                </div>`;
            }

            window.Protekta.registerInit(() => {
                const container = document.getElementById('dynamic-fields-admin');
                if (!container || container.dataset.initialized === '1') return;
                container.dataset.initialized = '1';
                container.innerHTML = (formFields || []).map(renderField).join('');

                // Also initialize the click listener for the main container here if not already done via delegation
                // In this architecture, we rely on the document level delegation but bounded by the scope check in handleSuratClicks

                // Wire pemohon selects
                container.querySelectorAll('.pemohon-select').forEach(select => {
                    select.addEventListener('change', () => {
                        const [type, id] = select.value.split(':');
                        const parent = select.closest('div') || select.closest('td');
                        if (parent.querySelector('.pemohon-type')) parent.querySelector('.pemohon-type').value = type || '';
                        if (parent.querySelector('.pemohon-id')) parent.querySelector('.pemohon-id').value = id || '';
                        
                        // Toggle custom inputs visibility
                        const customInputs = parent.querySelector('.pemohon-custom-inputs');
                        if (customInputs) {
                            if (type === 'custom') {
                                customInputs.classList.remove('hidden');
                                customInputs.querySelectorAll('input').forEach(inp => inp.setAttribute('required', 'required'));
                            } else {
                                customInputs.classList.add('hidden');
                                customInputs.querySelectorAll('input').forEach(inp => {
                                    inp.removeAttribute('required');
                                    inp.value = '';
                                });
                            }
                        }
                    });
                });

                // No Surat Logic
                const input = document.getElementById('no_surat');
                if (input && (input.value || '').trim() === '' && input.dataset.userEdited !== '1') {
                    input.addEventListener('input', () => { input.dataset.userEdited = '1'; });
                    const jenisId = @json($surat->surat_jenis_id);
                    if (jenisId) {
                        fetch(`{{ route('admin.surat.next-no-surat') }}?surat_jenis_id=${encodeURIComponent(jenisId)}`, {
                            headers: { 'Accept': 'application/json' },
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data && (input.value || '').trim() === '' && input.dataset.userEdited !== '1') {
                                input.value = String(data.next_no_surat);
                            }
                        });
                    }
                }
            });

            // Email Preview Delegated Listener (Specific for this view)
            const handleSuratClicks = async function(e) {
                // Check if we are still on the right page for this listener instance
                const scopeCheck = document.getElementById('surat-show-container');
                if (!scopeCheck || scopeCheck.dataset.id !== '{{ $surat->id }}') {
                    document.removeEventListener('click', handleSuratClicks);
                    return;
                }

                const btn = e.target.closest('#btn-show-email-preview');
                if (btn) {
                    e.preventDefault();
                    const toInput = document.getElementById('input-recipient');
                    const applicantEmail = '{{ $surat->pemohon_type == "mahasiswa" ? ($surat->pemohonMahasiswa->email ?? "") : ($surat->pemohonDosen->email ?? "") }}';
                    const to = (toInput ? toInput.value.trim() : '') || applicantEmail;
                    if (!to || !to.includes('@')) { alert('Harap masukkan alamat email yang valid.'); return; }

                    if (window.Protekta && window.Protekta.modal) {
                        window.Protekta.modal.show('email-preview-modal');
                    } else {
                        const m = document.getElementById('email-preview-modal');
                        if (m) m.classList.remove('hidden');
                    }
                    
                    const loader = document.getElementById('modal-loading-state');
                    const editor = document.getElementById('modal-editor-state');
                    if (loader) loader.classList.remove('hidden');
                    if (editor) editor.classList.add('hidden');

                    try {
                        const url = `{{ route('admin.surattemplate.preview-email', [$surat->jenis, $surat->jenis?->template ?? 0, $surat]) }}`;
                        const res = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            body: JSON.stringify({ recipient: to })
                        });
                        const data = await res.json();
                        
                        const pRec = document.getElementById('preview-recipient');
                        const pSub = document.getElementById('preview-subject');
                        const pBod = document.getElementById('preview-body');
                        
                        if (pRec) pRec.textContent = to;
                        if (pSub) pSub.value = data.subject || '';
                        if (pBod) pBod.value = data.body || '';
                        
                        if (loader) loader.classList.add('hidden');
                        if (editor) editor.classList.remove('hidden');
                    } catch (err) { alert('Gagal mengambil pratinjau.'); }
                    return;
                }

                if (e.target.closest('#btn-confirm-send')) {
                    const sub = document.getElementById('preview-subject');
                    const bod = document.getElementById('preview-body');
                    const hSub = document.getElementById('hidden-email-subject');
                    const hBod = document.getElementById('hidden-email-body');
                    
                    if (hSub && sub) hSub.value = sub.value;
                    if (hBod && bod) hBod.value = bod.value;
                    
                    const b = e.target.closest('#btn-confirm-send');
                    b.disabled = true;
                    b.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Mengirim...';
                    const form = document.getElementById('email-send-form');
                    if (form) form.submit();
                    return;
                }

                if (e.target.closest('#btn-send-wa')) {
                    const bod = document.getElementById('preview-body');
                    const recSet = document.getElementById('wa-recipient-select-surat');
                    if (!bod || !recSet) return;
                    
                    const num = window.Protekta.helpers.formatWA(recSet.value || '');
                    window.open(`https://wa.me/${num}?text=${encodeURIComponent(bod.value)}`, '_blank');
                    setTimeout(() => { 
                        document.getElementById('btn-download-docx-surat')?.click();
                        alert('WhatsApp dibuka. File sedang didownload.');
                    }, 1000);
                }
            };

            // Register global listener but it checks scope inside
            document.addEventListener('click', handleSuratClicks);
        })();
    </script>
</div>
@endsection
