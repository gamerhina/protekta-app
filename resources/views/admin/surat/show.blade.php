@extends('layouts.app')

@section('title', 'Detail Surat')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
                        <button id="btn-show-email-preview" class="btn-pill btn-pill-primary w-full md:w-auto px-6 text-sm" type="button">
                            <i class="fab fa-whatsapp mr-1 text-green-300"></i>
                            <i class="fas fa-paper-plane mr-2"></i> 
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
</div>
@endsection

@section('scripts')
<script>
    (function() {
        const suratData = @json($surat->data ?? []);
        const formFields = @json($surat->jenis?->form_fields ?? []);
        const dosens = @json($dosens ?? []);
        const mahasiswas = @json($mahasiswas ?? []);
        const currentPemohonType = @json($surat->pemohon_type);
        const currentPemohonId = @json($surat->pemohon_type === 'mahasiswa' ? $surat->pemohon_mahasiswa_id : $surat->pemohon_dosen_id);

        function escapeHtml(str) {
            return String(str ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function renderField(field) {
            const key = field.key;
            const label = escapeHtml(field.label);
            const required = field.required ? 'required' : '';
            
            // Get value from suratData (JSON) or from model attributes passed via json
            let value = suratData[key] || '';
            
            // Mapping for standard columns if value not in JSON
            if (value === '') {
                if (key === 'tujuan') value = @json($surat->tujuan);
                if (key === 'perihal') value = @json($surat->perihal);
                if (key === 'isi') value = @json($surat->isi);
                if (key === 'penerima_email') value = @json($surat->penerima_email);
            }

            // Skip fields that are already in the "administrative" section 
            // EXCEPT if they are defined as content fields in form_fields (e.g. tujuan, perihal, isi)
            if (['no_surat', 'tanggal_surat', 'status'].includes(key)) {
                return '';
            }

            if (field.type === 'pemohon') {
                const sources = Array.isArray(field.pemohon_sources) && field.pemohon_sources.length ? field.pemohon_sources : ['mahasiswa','dosen'];
                const dosenOptions = dosens.map(d => `<option value="dosen:${d.id}" ${currentPemohonType === 'dosen' && currentPemohonId == d.id ? 'selected' : ''}>${escapeHtml(d.nama)} (${escapeHtml(d.nip)})</option>`).join('');
                const mhsOptions = mahasiswas.map(m => `<option value="mahasiswa:${m.id}" ${currentPemohonType === 'mahasiswa' && currentPemohonId == m.id ? 'selected' : ''}>${escapeHtml(m.nama)} (${escapeHtml(m.npm)})</option>`).join('');
                
                return `
                    <div class="md:col-span-2 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <label class="block text-sm font-semibold text-slate-700 mb-2">${label}</label>
                        <input type="hidden" class="pemohon-type" name="form_data[${escapeHtml(key)}][type]" value="${escapeHtml(currentPemohonType)}">
                        <input type="hidden" class="pemohon-id" name="form_data[${escapeHtml(key)}][id]" value="${escapeHtml(currentPemohonId)}">
                        <select class="pemohon-select w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                            <option value="">Pilih pemohon</option>
                            ${sources.includes('mahasiswa') ? `<optgroup label="Mahasiswa">${mhsOptions}</optgroup>` : ''}
                            ${sources.includes('dosen') ? `<optgroup label="Dosen">${dosenOptions}</optgroup>` : ''}
                        </select>
                    </div>
                `;
            }

            if (field.type === 'textarea') {
                return `
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <textarea name="form_data[${escapeHtml(key)}]" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">${escapeHtml(value)}</textarea>
                    </div>
                `;
            }

            if (field.type === 'date') {
                return `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <input type="date" name="form_data[${escapeHtml(key)}]" value="${escapeHtml(value)}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                    </div>
                `;
            }

            if (field.type === 'file') {
                return `
                    <div class="md:col-span-2 p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        ${value ? `<div class="mb-2 text-xs text-blue-600"><i class="fas fa-paperclip mr-1"></i> File: <a href="/storage/${value}" target="_blank" class="underline">Lihat File Saat Ini</a></div>` : '<div class="mb-2 text-xs text-gray-400 italic">Belum ada file.</div>'}
                        <input type="file" name="form_files[${escapeHtml(key)}]" class="text-sm">
                    </div>
                `;
            }

            if (field.type === 'select' || field.type === 'radio') {
                const options = Array.isArray(field.options) ? field.options : [];
                const optionsHtml = options.map(o => `<option value="${escapeHtml(o.value)}" ${value == o.value ? 'selected' : ''}>${escapeHtml(o.label)}</option>`).join('');
                return `
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                        <select name="form_data[${escapeHtml(key)}]" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                            <option value="">Pilih</option>
                            ${optionsHtml}
                        </select>
                    </div>
                `;
            }

            return `
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">${label}</label>
                    <input type="${field.type === 'number' ? 'number' : 'text'}" name="form_data[${escapeHtml(key)}]" value="${escapeHtml(value)}" class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white">
                </div>
            `;
        }

        function initAdminSuratShow() {
            const container = document.getElementById('dynamic-fields-admin');
            if (!container) return;
            if (container.dataset.initialized === '1') return;
            container.dataset.initialized = '1';

            container.innerHTML = formFields.map(renderField).join('');

            // Wire up pemohon selects
            container.querySelectorAll('.pemohon-select').forEach(select => {
                select.addEventListener('change', () => {
                    const [type, id] = select.value.split(':');
                    const parent = select.closest('div');
                    parent.querySelector('.pemohon-type').value = type || '';
                    parent.querySelector('.pemohon-id').value = id || '';
                });
            });

            // Original no_surat logic
            const input = document.getElementById('no_surat');
            if (input) {
                input.addEventListener('input', () => {
                    input.dataset.userEdited = '1';
                });
                if ((input.value || '').trim() === '' && input.dataset.userEdited !== '1') {
                    const jenisId = @json($surat->surat_jenis_id);
                    if (jenisId) {
                        fetch(`{{ route('admin.surat.next-no-surat') }}?surat_jenis_id=${encodeURIComponent(jenisId)}`, {
                            headers: { 'Accept': 'application/json' },
                        })
                        .then((res) => (res.ok ? res.json() : null))
                        .then((data) => {
                            if (data && (input.value || '').trim() === '' && input.dataset.userEdited !== '1') {
                                input.value = String(data.next_no_surat);
                            }
                        });
                    }
                }
            }
        }

        document.addEventListener('DOMContentLoaded', initAdminSuratShow);
        window.addEventListener('page-loaded', initAdminSuratShow);

        // Global toggle function
        window.toggleEmailPreviewModal = function(show) {
            const modal = document.getElementById('email-preview-modal');
            if (!modal) return;
            if (show) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        };

        // Event Delegation for all preview-related clicks
        // This is robust against AJAX content replacement
        document.addEventListener('click', async function(e) {
            // 1. Show Preview Click
            const triggerBtn = e.target.closest('#btn-show-email-preview');
            if (triggerBtn) {
                const to = document.getElementById('input-recipient').value;
                if (!to || !to.includes('@')) {
                    alert('Harap masukkan alamat email yang valid.');
                    return;
                }

                window.toggleEmailPreviewModal(true);
                const loadingState = document.getElementById('modal-loading-state');
                const editorState = document.getElementById('modal-editor-state');
                const confirmBtn = document.getElementById('btn-confirm-send');

                if (loadingState) loadingState.classList.remove('hidden');
                if (editorState) editorState.classList.add('hidden');
                if (confirmBtn) confirmBtn.disabled = true;

                try {
                    const response = await fetch(`{{ route('admin.surattemplate.preview-email', [$surat->jenis, $surat->jenis->template ?? 0, $surat]) }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) throw new Error('Gagal mengambil pratinjau email.');

                    const data = await response.json();
                    
                    const recipientEl = document.getElementById('preview-recipient');
                    if (recipientEl) {
                        recipientEl.textContent = to;
                    }
                    document.getElementById('preview-subject').value = data.subject;
                    document.getElementById('preview-body').value = data.body;

                    if (loadingState) loadingState.classList.add('hidden');
                    if (editorState) editorState.classList.remove('hidden');
                    if (confirmBtn) confirmBtn.disabled = false;
                } catch (error) {
                    alert(error.message);
                    window.toggleEmailPreviewModal(false);
                }
                return;
            }

            // 2. Confirm Send Click
            const confirmBtn = e.target.closest('#btn-confirm-send');
            if (confirmBtn) {
                const subject = document.getElementById('preview-subject').value;
                const body = document.getElementById('preview-body').value;

                document.getElementById('hidden-email-subject').value = subject;
                document.getElementById('hidden-email-body').value = body;

                document.getElementById('email-send-form').submit();
                return;
            }

            // 3. Send WA Click
            const waBtn = e.target.closest('#btn-send-wa');
            if (waBtn) {
                const body = document.getElementById('preview-body').value;
                const recipientSelect = document.getElementById('wa-recipient-select-surat');
                let phoneNumber = '';

                if (recipientSelect && recipientSelect.value) {
                    let raw = recipientSelect.value.replace(/\D/g, '');
                    if (raw.startsWith('0')) {
                        raw = '62' + raw.substring(1);
                    }
                    phoneNumber = raw;
                }
                
                // Open WhatsApp FIRST
                const encodedText = encodeURIComponent(body);
                const waUrl = phoneNumber 
                    ? `https://wa.me/${phoneNumber}?text=${encodedText}` 
                    : `https://wa.me/?text=${encodedText}`;

                window.open(waUrl, '_blank');

                // Trigger download & Alert after delay
                setTimeout(() => {
                    const downloadBtn = document.getElementById('btn-download-docx-surat');
                    if (downloadBtn) {
                        downloadBtn.click();
                        alert('WhatsApp telah dibuka. File dokumen sedang didownload.\n\nMohon lampirkan file yang terdownload secara manual di chat WhatsApp.');
                    }
                }, 1000);
                
                return;
            }

            // 4. Close/Batal buttons click
            if (e.target.closest('.btn-close-preview')) {
                window.toggleEmailPreviewModal(false);
                return;
            }
        });
    })();
</script>

<!-- Email Preview Modal -->
<div id="email-preview-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="toggleEmailPreviewModal(false)"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4 border-b pb-4">
                    <h3 class="text-lg font-bold text-gray-900" id="modal-title">Kirim WA / Email</h3>
                    <button type="button" class="btn-close-preview text-gray-400 hover:text-gray-500">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="space-y-4" id="modal-loading-state">
                    <div class="flex flex-col items-center justify-center py-10">
                        <i class="fas fa-spinner fa-spin text-4xl text-blue-500 mb-3"></i>
                        <p class="text-sm text-gray-500 italic">Menyiapkan pratinjau email...</p>
                    </div>
                </div>
                <div class="space-y-4 hidden" id="modal-editor-state">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Penerima</label>
                        <div id="preview-recipient" class="text-sm font-semibold text-gray-800 bg-gray-50 p-2 rounded-lg border border-gray-100"></div>
                    </div>
                    <div>
                        <label for="preview-subject" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Subjek</label>
                        <input type="text" id="preview-subject" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label for="preview-body" class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Isi Pesan</label>
                        <textarea id="preview-body" rows="8" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                    </div>
                    <div id="wa-recipient-container" class="pt-3 border-t border-gray-100">
                        <label class="block text-xs font-bold text-blue-500 uppercase tracking-widest mb-2">
                            <i class="fab fa-whatsapp mr-1"></i> Kirim WhatsApp Ke:
                        </label>
                        <select id="wa-recipient-select-surat" class="w-full px-3 py-2 border border-blue-200 rounded-lg text-sm bg-blue-50 text-blue-700 font-semibold focus:ring-2 focus:ring-blue-500 outline-none">
                            @if($surat->pemohon_type === 'mahasiswa' && $surat->pemohonMahasiswa && $surat->pemohonMahasiswa->wa)
                                <option value="{{ $surat->pemohonMahasiswa->wa }}">Pemohon: {{ $surat->pemohonMahasiswa->nama }} ({{ $surat->pemohonMahasiswa->wa }})</option>
                            @elseif($surat->pemohon_type === 'dosen' && $surat->pemohonDosen && $surat->pemohonDosen->wa)
                                <option value="{{ $surat->pemohonDosen->wa }}">Pemohon: {{ $surat->pemohonDosen->nama }} ({{ $surat->pemohonDosen->wa }})</option>
                            @endif
                            
                            @if($surat->mahasiswa && $surat->mahasiswa->wa && ($surat->pemohon_mahasiswa_id !== $surat->mahasiswa_id))
                                <option value="{{ $surat->mahasiswa->wa }}">Mahasiswa Terkait: {{ $surat->mahasiswa->nama }} ({{ $surat->mahasiswa->wa }})</option>
                            @endif
                            
                            <option value="">Pilih Manual di WhatsApp</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 flex flex-col md:flex-row-reverse gap-3 rounded-b-2xl">
                <button type="button" id="btn-confirm-send" class="btn-pill btn-pill-info px-8 min-w-[160px]">
                    <i class="fas fa-paper-plane mr-2"></i> Kirim via Email
                </button>
                <button type="button" id="btn-send-wa" class="btn-pill btn-pill-success px-8 min-w-[160px]">
                    <i class="fab fa-whatsapp mr-2"></i> Kirim via WA
                </button>
                <button type="button" class="btn-close-preview btn-pill btn-pill-secondary px-8">
                    <i class="fas fa-times mr-2"></i> Batal
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
