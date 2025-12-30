@extends('layouts.app')

@section('title', 'Preview & Send Document')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Preview & Send Document</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Template: <strong>{{ $template->nama }}</strong> | 
                    Seminar: <strong>{{ $seminar->mahasiswa->nama }}</strong>
                </p>
            </div>
            <a href="{{ route('admin.seminar.show', $seminar->id) }}" class="btn-pill btn-pill-secondary">
                Kembali
            </a>
        </div>



        @php
            $prefilledSubject = old('subject', $emailDefaults['subject'] ?? ('Dokumen ' . $template->nama));
            $prefilledMessage = old('message', $emailDefaults['body'] ?? '');
            $selectedAttachmentMode = old('attachment_mode', 'auto');
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Informasi Dokumen</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Template:</dt>
                        <dd class="font-medium text-gray-900">{{ $template->nama }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Kode:</dt>
                        <dd class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $template->kode }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Mahasiswa:</dt>
                        <dd class="font-medium text-gray-900">{{ $seminar->mahasiswa->nama }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">NPM:</dt>
                        <dd class="text-gray-900">{{ $seminar->mahasiswa->npm }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Jenis Seminar:</dt>
                        <dd class="text-gray-900">{{ $seminar->seminarJenis->nama }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-600">Tanggal:</dt>
                        <dd class="text-gray-900">{{ $seminar->tanggal ? $seminar->tanggal->format('d F Y') : '-' }}</dd>
                    </div>
                </dl>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Download DOCX</h2>
                    <p class="text-sm text-gray-600 mb-4">Unduh hasil template untuk diperiksa atau dibagikan manual.</p>
                </div>
                <div>
                    <a href="{{ route('admin.document.download-docx', [$template->id, $seminar->id]) }}" 
                       id="btn_download_docx"
                       download
                       data-no-ajax
                       target="_blank"
                       class="btn-pill btn-pill-primary w-full flex items-center justify-center gap-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11v8m0-8l-3 3m3-3l3 3M5 8h14l-1.405-4.215A1 1 0 0016.638 3H7.362a1 1 0 00-.957.785L5 8z"></path>
                        </svg>
                        Download DOCX
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">DOCX Perubahan</h2>
                    <p class="text-sm text-gray-600">Edit tag di bawah, lalu generate. Hasilnya dapat diunduh sebagai DOCX.</p>
                </div>
                <button type="button" onclick="generateDocxPreview()" id="preview_button" class="btn-pill btn-pill-info">
                    Generate DOCX Perubahan
                </button>
            </div>

            <details class="mt-4">
                <summary class="cursor-pointer text-sm font-semibold text-gray-700">Edit isi (tag) lalu regenerate</summary>
                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
                    <div class="md:col-span-2 xl:col-span-3 text-xs text-gray-500">
                        Tip: gunakan kolom HTML untuk tag bertipe <span class="font-mono">html</span>. Tag gambar tidak diedit di sini.
                    </div>
                    @foreach(($availableTags ?? []) as $tag)
                        @php
                            $type = ($tagTypes ?? [])[$tag] ?? 'standard';
                            $value = ($finalData ?? [])[$tag] ?? '';
                            $isImage = $type === 'image';
                            $isHtml = $type === 'html';
                        @endphp
                        <div class="border border-gray-200 rounded-md p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <div class="text-xs text-gray-500">Tag</div>
                                    <div class="font-mono text-sm text-gray-900">{{ $tag }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Type: {{ $type }}</div>
                                </div>
                                <div class="flex-1">
                                    @if($isImage)
                                        <input type="text" class="w-full px-3 py-2 border border-gray-200 rounded-md text-sm bg-gray-50" value="{{ is_scalar($value) ? $value : '' }}" readonly>
                                        <p class="text-xs text-gray-500 mt-1">Tag gambar tidak diedit di sini (gunakan sumber data/gambar).</p>
                                    @else
                                        <textarea
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm font-mono"
                                            rows="{{ $isHtml ? 6 : 2 }}"
                                            data-preview-override
                                            data-tag="{{ $tag }}"
                                        >{{ is_scalar($value) ? $value : '' }}</textarea>
                                        <p class="text-xs text-gray-500 mt-1">{{ $isHtml ? 'Isi sebagai HTML.' : 'Isi sebagai teks biasa.' }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </details>

            <p id="preview_status" class="text-xs text-gray-500 mt-3"></p>
            <div id="preview_link_wrapper" class="mt-4 hidden">
                <a id="docx_preview_link" href="" target="_blank" rel="noopener" class="text-indigo-600 hover:text-indigo-800 underline text-sm" data-no-ajax>
                    Download DOCX Perubahan
                </a>
            </div>
        </div>

        <div class="mt-8">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm w-full p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-1">Kirim Dokumen via Email</h2>
                <p class="text-sm text-gray-600 mb-5">Pilih penerima, atur pesan email, lalu kirim dokumen.</p>

                <form action="{{ route('admin.document.send', [$template->id, $seminar->id]) }}" method="POST" enctype="multipart/form-data" class="space-y-6" data-no-ajax>
                    @csrf
                    <input type="hidden" name="preview_token" id="preview_token_input" value="{{ old('preview_token') }}">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Penerima Email</label>
                        <div class="border border-gray-200 rounded-md divide-y divide-gray-100">
                            @php
                                $recipientOptions = [
                                    ['label' => 'Mahasiswa', 'value' => optional($seminar->mahasiswa)->email, 'wa' => optional($seminar->mahasiswa)->wa, 'nama' => optional($seminar->mahasiswa)->nama],
                                    ['label' => 'Pembimbing 1', 'value' => optional($seminar->p1Dosen)->email, 'wa' => optional($seminar->p1Dosen)->wa, 'nama' => optional($seminar->p1Dosen)->nama],
                                    ['label' => 'Pembimbing 2', 'value' => optional($seminar->p2Dosen)->email, 'wa' => optional($seminar->p2Dosen)->wa, 'nama' => optional($seminar->p2Dosen)->nama],
                                    ['label' => 'Pembahas', 'value' => optional($seminar->pembahasDosen)->email, 'wa' => optional($seminar->pembahasDosen)->wa, 'nama' => optional($seminar->pembahasDosen)->nama],
                                ];
                                
                                // Collect WhatsApp numbers for the selector
                                $waOptions = [];
                                foreach ($recipientOptions as $opt) {
                                    if (!empty($opt['wa'])) {
                                        $waOptions[] = $opt;
                                    }
                                }
                            @endphp
                            @foreach($recipientOptions as $option)
                                @if($option['value'])
                                    <label class="flex items-center justify-between px-3 py-2 text-sm text-gray-700">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="recipients[]" value="{{ $option['value'] }}" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                            <span class="ml-3 font-medium">{{ $option['label'] }}</span>
                                        </div>
                                        <span class="text-xs text-gray-500">{{ $option['value'] }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        @error('recipients')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email Tambahan (Optional)</label>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="email" id="custom_email" placeholder="email@example.com" class="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm">
                            <button type="button" onclick="addCustomEmail()" class="btn-pill btn-pill-secondary text-sm">+ Tambah Email</button>
                        </div>
                        <div id="custom_emails_list" class="mt-2 space-y-1"></div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Lampiran Dokumen</label>
                        <div class="flex items-center gap-6 mt-2 text-sm text-gray-700">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="attachment_mode" value="auto" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ $selectedAttachmentMode === 'auto' ? 'checked' : '' }}>
                                <span>Gunakan file DOCX hasil template</span>
                            </label>
                            
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" name="attachment_mode" value="custom" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500" {{ $selectedAttachmentMode === 'custom' ? 'checked' : '' }}>
                                <span>Unggah file sendiri</span>
                            </label>
                        </div>@error('attachment_mode')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                            <div id="custom_attachment_group" class="mt-2 {{ $selectedAttachmentMode === 'custom' ? '' : 'hidden' }}">
                                <input type="file" name="custom_attachment" id="custom_attachment" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all shadow-sm" {{ $selectedAttachmentMode === 'custom' ? 'required' : '' }}>
                                <p class="text-xs text-gray-500 mt-1">Maksimum 15MB. Format umum seperti .docx, .pdf, .zip diperbolehkan.</p>
                                @error('custom_attachment')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            Konten Email
                        </h3>

                        <div class="mb-5">
                            <label for="subject" class="block text-sm font-semibold text-gray-700 mb-1">Subject Email</label>
                            <input type="text" name="subject" id="subject" value="{{ $prefilledSubject }}" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('subject') border-red-500 @enderror" required>
                            @error('subject')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pesan Email</label>
                            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold text-gray-600 mb-2">
                                <span>Mode Editor:</span>
                                <button type="button" data-email-editor-mode="visual" onclick="setEmailEditorMode('visual')" class="px-2 py-1 rounded border border-transparent bg-gray-200">Visual</button>
                                <button type="button" data-email-editor-mode="html" onclick="setEmailEditorMode('html')" class="px-2 py-1 rounded border border-transparent">HTML</button>
                                <button type="button" onclick="triggerEmailImageUpload()" class="px-2 py-1 rounded border border-blue-200 bg-blue-50 text-blue-700">Insert Gambar</button>
                            </div>
                            <div id="email_message_editor_container" class="border border-gray-300 rounded-md bg-white">
                                <div id="email_message_editor" class="min-h-[220px]"></div>
                            </div>
                            <textarea id="email_message_html" class="hidden w-full mt-2 px-3 py-2 border border-gray-300 rounded-md text-sm font-mono" rows="8"></textarea>
                            <textarea name="message" id="message" class="hidden">@php echo old('message', $prefilledMessage); @endphp</textarea>
                            <input type="file" id="email_image_uploader" accept="image/*" class="hidden">
                        </div>
                    </div>

                    <div class="flex flex-col gap-4">
                        @if(count($waOptions) > 0)
                            <div class="flex flex-col sm:flex-row items-end sm:items-center justify-end gap-3 pb-2 border-b border-gray-100 mt-3">
                                <label for="wa_recipient_select" class="text-[10px] font-bold text-blue-500 uppercase tracking-widest">
                                    <i class="fab fa-whatsapp mr-1"></i> Pilih Penerima WhatsApp
                                </label>
                                <select id="wa_recipient_select" class="block w-full sm:w-64 bg-blue-50 border-blue-200 text-blue-700 text-xs font-semibold rounded-full px-4 py-1.5 focus:ring-blue-500 focus:border-blue-500 cursor-pointer">
                                    @foreach($waOptions as $opt)
                                        <option value="{{ $opt['wa'] }}">{{ $opt['label'] }}: {{ $opt['nama'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="flex flex-col sm:flex-row justify-end items-center gap-3">
                            <button type="button" 
                                    onclick="sendViaWhatsapp()" 
                                    class="w-full sm:w-auto btn-pill btn-pill-success min-w-[180px] {{ count($waOptions) === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ count($waOptions) === 0 ? 'disabled' : '' }}>
                                <i class="fab fa-whatsapp mr-2"></i> Kirim via WA
                            </button>

                            <button type="submit" 
                                    onclick="this.closest('form').submit()" 
                                    class="w-full sm:w-auto btn-pill btn-pill-info min-w-[180px]">
                                <i class="fas fa-paper-plane mr-2"></i> Kirim via Email
                            </button>
                        </div>

                        @if(count($waOptions) === 0)
                            <p class="text-right text-[10px] text-amber-600 font-semibold italic">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Tidak ada nomor WA yang terdaftar untuk seminar ini.
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .ql-editor {
        color: #374151 !important;
        min-height: 220px;
        font-size: 0.875rem;
        line-height: 1.5;
    }
</style>

<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
const previewConfig = {
    generateUrl: "{{ route('admin.document.preview-docx', [$template->id, $seminar->id]) }}",
    cleanupUrl: "{{ route('admin.document.preview.cleanup') }}",
    csrfToken: "{{ csrf_token() }}",
};

let currentPreviewToken = "{{ old('preview_token') }}";
if (!currentPreviewToken) {
    currentPreviewToken = null;
}
let previewLoading = false;

function sendViaWhatsapp() {
    // 1. Get content from editor
    let bodyText = '';
    if (emailEditorMode === 'visual' && emailEditorQuill) {
        // Use innerText to preserve visual line breaks better for WhatsApp
        bodyText = emailEditorQuill.root.innerText || emailEditorQuill.getText();
    } else {
        const html = document.getElementById('email_message_html').value;
        const temp = document.createElement('div');
        temp.innerHTML = html;
        bodyText = temp.textContent || temp.innerText || '';
    }

    // 2. Get Recipient Number
    const select = document.getElementById('wa_recipient_select');
    let phoneNumber = '';
    
    if (select && select.value) {
        // Format phone number: remove non-digits, replace leading 0 with 62
        let raw = select.value.replace(/\D/g, '');
        if (raw.startsWith('0')) {
            raw = '62' + raw.substring(1);
        }
        phoneNumber = raw;
    }

    // 3. Open WhatsApp FIRST (to ensure popup isn't blocked)
    const encodedText = encodeURIComponent(bodyText);
    const waUrl = phoneNumber 
        ? `https://wa.me/${phoneNumber}?text=${encodedText}` 
        : `https://wa.me/?text=${encodedText}`;

    window.open(waUrl, '_blank');

    // 4. Trigger download & Alert (Delayed to allow tab switch/backgrounding)
    setTimeout(() => {
        const downloadBtn = document.getElementById('btn_download_docx');
        if (downloadBtn) {
            downloadBtn.click();
            // Optional: alert user to attach file
            alert('WhatsApp telah dibuka. File dokumen sedang didownload.\n\nMohon lampirkan file yang terdownload secara manual di chat WhatsApp.');
        }
    }, 1000);
}

async function generateDocxPreview() {
    if (previewLoading) {
        return;
    }

    previewLoading = true;
    setPreviewStatus('Sedang membuat preview...');

    try {
        if (currentPreviewToken) {
            await cleanupPreview(currentPreviewToken);
        }

        const overrides = {};
        document.querySelectorAll('[data-preview-override][data-tag]').forEach(el => {
            const tag = el.getAttribute('data-tag');
            overrides[tag] = el.value;
        });

        const response = await fetch(previewConfig.generateUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': previewConfig.csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ overrides }),
        });

        if (!response.ok) {
            throw new Error('Server mengembalikan status ' + response.status);
        }

        const payload = await response.json();
        const link = document.getElementById('docx_preview_link');
        link.href = payload.file_url;
        document.getElementById('preview_link_wrapper').classList.remove('hidden');
        document.getElementById('preview_token_input').value = payload.token;
        currentPreviewToken = payload.token;
        setPreviewStatus('Preview berhasil dibuat.');
    } catch (error) {
        setPreviewStatus(error.message || 'Gagal membuat preview.');
    } finally {
        previewLoading = false;
    }
}

function setPreviewStatus(message) {
    const el = document.getElementById('preview_status');
    if (el) {
        el.textContent = message;
    }
}

async function cleanupPreview(token, options = {}) {
    if (!token) {
        return;
    }

    const formData = new FormData();
    formData.append('_token', previewConfig.csrfToken);
    formData.append('token', token);

    try {
        await fetch(previewConfig.cleanupUrl, {
            method: 'POST',
            body: formData,
            keepalive: options.keepalive ?? false,
        });
    } catch (_) {
        // Abaikan error cleanup
    } finally {
        if (!options.keepExistingValue && currentPreviewToken === token) {
            currentPreviewToken = null;
            const hiddenInput = document.getElementById('preview_token_input');
            if (hiddenInput && hiddenInput.value === token) {
                hiddenInput.value = '';
            }
        }
    }
}

function cleanupPreviewBeforeUnload() {
    if (!currentPreviewToken) {
        return;
    }

    const formData = new FormData();
    formData.append('_token', previewConfig.csrfToken);
    formData.append('token', currentPreviewToken);

    if (navigator.sendBeacon) {
        navigator.sendBeacon(previewConfig.cleanupUrl, formData);
    } else {
        fetch(previewConfig.cleanupUrl, {
            method: 'POST',
            body: formData,
            keepalive: true,
        });
    }
}

window.addEventListener('beforeunload', cleanupPreviewBeforeUnload);
window.addEventListener('pagehide', cleanupPreviewBeforeUnload);

let emailEditorQuill = null;
let emailEditorMode = 'visual';

// Simple, robust initialization
// Ultimate Robust Initialization Pattern (matching signature-pad.js)
function safeInitQuill() {
    // 1. Check if container exists (if not, we might be on wrong page or too early)
    if (!document.getElementById('email_message_editor')) {
        return;
    }

    // 2. Check if Quill is loaded
    if (typeof Quill === 'undefined') {
        // Poll for it, as it might be loading async/deferred
        let checks = 0;
        const waiter = setInterval(() => {
            checks++;
            if (typeof Quill !== 'undefined') {
                clearInterval(waiter);
                initEmailMessageEditor();
            } else if (checks > 50) { // 5 seconds max
                clearInterval(waiter);
                console.error("Quill failed to load.");
                fallbackToTextarea();
            }
        }, 100);
        return;
    }

    // 3. Ready to go
    initEmailMessageEditor();
}

// Attach to standard events
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', safeInitQuill);
} else {
    safeInitQuill();
}

// Attach to framework-specific custom events (like signature-pad.js does)
window.addEventListener('page-loaded', safeInitQuill);
window.addEventListener('app:init', safeInitQuill);
window.addEventListener('turbolinks:load', safeInitQuill); // Just in case
window.addEventListener('turbo:load', safeInitQuill);     // Just in case

function fallbackToTextarea() {
    const containerWrapper = document.getElementById('email_message_editor_container');
    const htmlTextarea = document.getElementById('email_message_html');
    const hiddenInput = document.getElementById('message');
    
    if (containerWrapper && htmlTextarea && hiddenInput) {
        containerWrapper.classList.add('hidden');
        htmlTextarea.classList.remove('hidden');
        htmlTextarea.value = hiddenInput.value;
        emailEditorMode = 'html';
    }
}

function initEmailMessageEditor() {
    const container = document.getElementById('email_message_editor');
    const containerWrapper = document.getElementById('email_message_editor_container');
    const hiddenInput = document.getElementById('message');
    const htmlTextarea = document.getElementById('email_message_html');

    if (!container || !hiddenInput || !htmlTextarea) {
        return;
    }

    if (typeof Quill === 'undefined') {
        console.error('Quill is undefined. Falling back to HTML textarea.');
        // Fallback: Show HTML area, hide container
        containerWrapper.classList.add('hidden');
        htmlTextarea.classList.remove('hidden');
        htmlTextarea.value = hiddenInput.value;
        emailEditorMode = 'html';
        return;
    }

    // Prevent double initialization
    if (containerWrapper.querySelector('.ql-toolbar')) {
        return; 
    }

    try {
        // Clear potential partial renders
        container.innerHTML = '';
        
        emailEditorQuill = new Quill(container, {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ list: 'ordered' }, { list: 'bullet' }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
    } catch (e) {
        console.error('Quill Init Error:', e);
        containerWrapper.classList.add('hidden');
        htmlTextarea.classList.remove('hidden');
        return;
    }

    const initialValue = hiddenInput.value || '';
    
    // Direct assignment - simplest and often most robust for initialization
    if (initialValue) {
        emailEditorQuill.root.innerHTML = initialValue;
    }
    htmlTextarea.value = initialValue;

    emailEditorQuill.on('text-change', () => {
        if (emailEditorMode === 'visual') {
            const html = emailEditorQuill.root.innerHTML;
            hiddenInput.value = html;
            htmlTextarea.value = html;
        }
    });

    htmlTextarea.addEventListener('input', () => {
        if (emailEditorMode === 'html') {
            hiddenInput.value = htmlTextarea.value;
        }
    });

    const form = container.closest('form');
    // Ensure form exists and hasn't already been wired
    if (form && !form.dataset.quillSubmitWired) {
        form.dataset.quillSubmitWired = 'true'; // Prevent duplicate listeners
        form.addEventListener('submit', () => {
             // Sync Quill content to hidden input right before submit
            if (emailEditorMode === 'visual' && emailEditorQuill) {
                hiddenInput.value = emailEditorQuill.root.innerHTML;
            } else if (htmlTextarea) {
                hiddenInput.value = htmlTextarea.value;
            }
        });
    }

    setEmailEditorMode('visual');
}

function setEmailEditorMode(mode) {
    if (!emailEditorQuill) {
        return;
    }

    const containerWrapper = document.getElementById('email_message_editor_container');
    const htmlTextarea = document.getElementById('email_message_html');

    if (mode === 'visual') {
        htmlTextarea.classList.add('hidden');
        containerWrapper.classList.remove('hidden');
        if (emailEditorMode === 'html') {
            emailEditorQuill.root.innerHTML = htmlTextarea.value;
        }
    } else {
        htmlTextarea.value = emailEditorQuill.root.innerHTML;
        containerWrapper.classList.add('hidden');
        htmlTextarea.classList.remove('hidden');
        htmlTextarea.focus();
    }

    emailEditorMode = mode;
    document.querySelectorAll('[data-email-editor-mode]').forEach(button => {
        if (button.getAttribute('data-email-editor-mode') === mode) {
            button.classList.add('bg-gray-200', 'border-gray-300');
        } else {
            button.classList.remove('bg-gray-200', 'border-gray-300');
        }
    });
}

function triggerEmailImageUpload() {
    const input = document.getElementById('email_image_uploader');
    if (input) {
        input.click();
    }
}

document.getElementById('email_image_uploader')?.addEventListener('change', function(event) {
    const file = event.target.files?.[0];
    if (!file) {
        return;
    }

    const reader = new FileReader();
    reader.onload = function(e) {
        const dataUrl = e.target?.result;
        if (!dataUrl) {
            return;
        }

        if (emailEditorMode === 'html') {
            const htmlTextarea = document.getElementById('email_message_html');
            htmlTextarea.value += `\n<img src="${dataUrl}">\n`;
            htmlTextarea.dispatchEvent(new Event('input'));
        } else if (emailEditorQuill) {
            const range = emailEditorQuill.getSelection(true) || { index: emailEditorQuill.getLength(), length: 0 };
            emailEditorQuill.insertEmbed(range.index, 'image', dataUrl, 'user');
            emailEditorQuill.setSelection(range.index + 1, 0);
        }
    };
    reader.readAsDataURL(file);
    event.target.value = '';
});

function addCustomEmail() {
    const input = document.getElementById('custom_email');
    const email = input.value.trim();
    
    if (email && validateEmail(email)) {
        const list = document.getElementById('custom_emails_list');
        const div = document.createElement('div');
        div.className = 'flex items-center justify-between bg-white px-3 py-2 rounded border text-sm';
        div.innerHTML = `
            <span>${email}</span>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-800">
                ×
            </button>
            <input type="hidden" name="recipients[]" value="${email}">
        `;
        list.appendChild(div);
        input.value = '';
    } else {
        alert('Email tidak valid!');
    }
}

function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

document.getElementById('custom_email')?.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        addCustomEmail();
    }
});

// Attachment Toggle Logic
function initAttachmentToggle() {
    const attachmentRadios = document.querySelectorAll('input[name="attachment_mode"]');
    const customAttachmentGroup = document.getElementById('custom_attachment_group');
    const customAttachmentInput = document.getElementById('custom_attachment');

    if (!customAttachmentGroup || !customAttachmentInput) return;

    // Prevent duplicate initialization
    if (customAttachmentGroup.dataset.toggleInitialized === 'true') return;
    customAttachmentGroup.dataset.toggleInitialized = 'true';

    function updateVisibility() {
        const selected = document.querySelector('input[name="attachment_mode"]:checked');
        if (!selected) return;

        const isCustom = selected.value === 'custom';
        if (isCustom) {
            customAttachmentGroup.classList.remove('hidden');
        } else {
            customAttachmentGroup.classList.add('hidden');
        }
        customAttachmentInput.required = isCustom;
        if (!isCustom) customAttachmentInput.value = '';
    }

    // Attach listener
    attachmentRadios.forEach(radio => {
        radio.addEventListener('change', updateVisibility);
    });

    // Run once on init to set correct state
    updateVisibility();
}

// Attach to standard events
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAttachmentToggle);
} else {
    initAttachmentToggle();
}

// Attach to framework-specific custom events
window.addEventListener('page-loaded', initAttachmentToggle);
window.addEventListener('app:init', initAttachmentToggle);
window.addEventListener('turbolinks:load', initAttachmentToggle);
window.addEventListener('turbo:load', initAttachmentToggle);
</script>
@endsection
