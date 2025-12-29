@extends('layouts.app')

@section('title', 'Create Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Create New Seminar</h1>

        <form method="POST" action="{{ route('admin.seminar.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="mahasiswa_id" class="block text-sm font-medium text-gray-700 mb-1">Mahasiswa</label>
                    <select name="mahasiswa_id" id="mahasiswa_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('mahasiswa_id') border-red-500 @enderror" required>
                        <option value="">Select Mahasiswa</option>
                        @foreach($mahasiswas as $mahasiswa)
                            <option value="{{ $mahasiswa->id }}" {{ old('mahasiswa_id') == $mahasiswa->id ? 'selected' : '' }}>
                                {{ $mahasiswa->nama }} ({{ $mahasiswa->npm }})
                            </option>
                        @endforeach
                    </select>
                    @error('mahasiswa_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar</label>
                    <select name="seminar_jenis_id" id="seminar_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror" required>
                        <option value="">Select Seminar Type</option>
                        @foreach($seminarJenis as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('seminar_jenis_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('seminar_jenis_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="no_surat" class="block text-sm font-medium text-gray-700 mb-1">No. Surat</label>
                    <input 
                        type="text" 
                        name="no_surat" 
                        id="no_surat" 
                        value="{{ old('no_surat', $defaultNoSurat ?? '') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('no_surat') border-red-500 @enderror"
                        required
                    />
                    @error('no_surat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                    <div id="judul-editor" 
                         style="height: 200px; min-height: 200px; border: 1px solid #ccc; border-radius: 5px; background: white; spellcheck: false; -webkit-user-modify: read-write-plaintext-only;" 
                         class="@error('judul') border-red-500 @enderror ql-editor">
                    </div>
                    
                    <!-- Additional CSS for spellcheck removal -->
                    <style>
                        #judul-editor {
                            spellcheck: false !important;
                            -webkit-user-modify: read-write-plaintext-only;
                        }
                        #judul-editor::selection {
                            background: #b3d4fc;
                        }
                        #judul-editor:focus {
                            outline: none;
                            border-color: #4a90e2;
                        }
                    </style>
                    <textarea name="judul" id="judul" class="hidden" required>{{ old('judul') }}</textarea>
                    @error('judul')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div id="admin-berkas-syarat-dynamic" class="mt-6"></div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="btn-pill btn-pill-primary">
                    Create Seminar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ensure Quill CSS is loaded -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

<!-- Data for JavaScript -->
<div id="seminar-jenis-berkas-items" class="hidden">
    {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => $j->berkas_syarat_items ?: []])->all()) !!}
</div>
@endsection

@section('scripts')
<script>
(function() {
    function initSeminarCreate() {
        const seminarJenisSelect = document.getElementById('seminar_jenis_id');
        const noSuratInput = document.getElementById('no_surat');
        const nextNoSuratUrl = "{{ route('admin.seminar.next-no-surat') }}";
        const editor = document.getElementById('judul-editor');
        const textarea = document.getElementById('judul');

        if (!seminarJenisSelect) return;
        
        // Prevent double-init
        if (seminarJenisSelect.dataset.initialized === 'true') return;

        console.log('[SeminarCreate] Initializing...');

        // 1. Quill Initialization
        if (editor && textarea && typeof Quill !== 'undefined') {
            if (editor.getAttribute('data-quill-initialized') !== 'true') {
                const quill = new Quill(editor, {
                    theme: 'snow',
                    modules: {
                        toolbar: [['bold', 'italic']]
                    }
                });

                if (textarea.value) {
                    quill.root.innerHTML = textarea.value;
                }

                quill.on('text-change', () => {
                    textarea.value = quill.root.innerHTML;
                });

                const form = textarea.closest('form');
                if (form) {
                    form.addEventListener('submit', () => {
                        textarea.value = quill.root.innerHTML;
                    });
                }
                editor.setAttribute('data-quill-initialized', 'true');
            }
        }

        // 2. Serial Number & Dynamic Berkas Logic
        let manualOverride = false;
        noSuratInput?.addEventListener('input', () => { manualOverride = true; });

        async function updateNoSurat(jenisId) {
            if (!jenisId || !noSuratInput || !nextNoSuratUrl) return;
            manualOverride = false;
            noSuratInput.classList.add('opacity-70');
            try {
                const response = await fetch(`${nextNoSuratUrl}?seminar_jenis_id=${encodeURIComponent(jenisId)}`, {
                    headers: { 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (!manualOverride && data.next_no_surat) {
                    noSuratInput.value = data.next_no_surat;
                }
            } catch (error) {
                console.error('Failed to fetch nomor surat', error);
            } finally {
                noSuratInput.classList.remove('opacity-70');
            }
        }

        const berkasContainer = document.getElementById('admin-berkas-syarat-dynamic');
        const itemsEl = document.getElementById('seminar-jenis-berkas-items');
        let itemsMap = {};
        try {
            itemsMap = JSON.parse(itemsEl?.textContent || '{}') || {};
        } catch (e) { itemsMap = {}; }

        const renderUploads = () => {
            const id = seminarJenisSelect.value;
            const items = (id && Array.isArray(itemsMap[id])) ? itemsMap[id] : [];
            if (berkasContainer) {
                if (items.length === 0) {
                    berkasContainer.innerHTML = '';
                    return;
                }

                berkasContainer.innerHTML = `
                    <div class="mt-8 pt-8 border-t border-gray-100">
                        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-file-upload text-blue-500"></i>
                            Berkas Persyaratan
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="berkas-grid-container"></div>
                    </div>
                `;
                const grid = document.getElementById('berkas-grid-container');

                items.forEach((it) => {
                    if (!it || !it.key || !it.label) return;
                    const exts = Array.isArray(it.extensions) && it.extensions.length ? it.extensions : ['pdf'];
                    const maxKb = it.max_size_kb ? Number(it.max_size_kb) : 5120;
                    const accept = exts.map((e) => '.' + String(e).replace(/^\./, '')).join(',');
                    const mb = Math.round((maxKb / 1024) * 10) / 10;
                    
                    const card = document.createElement('div');
                    card.className = 'bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group';
                    
                    card.innerHTML = `
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-bold text-gray-800 truncate">${it.label}</h3>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                    ${it.required === false ? 'Opsional' : 'Wajib'} • ${exts.join(', ').toUpperCase()}
                                </p>
                            </div>
                            <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">BELUM ADA</span>
                        </div>
                        <div class="relative group/input">
                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Berkas</label>
                            <input
                                type="file"
                                name="berkas_syarat_items[${it.key}]"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                accept="${accept}"
                                ${it.required === false ? '' : 'required'}
                            />
                            <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maksimum ukuran file: <span class="font-bold text-gray-600">${mb}MB</span></p>
                        </div>
                    `;
                    grid.appendChild(card);
                });
            }
        };

        seminarJenisSelect.addEventListener('change', function () {
            manualOverride = false;
            updateNoSurat(this.value);
            renderUploads();
        });

        // Initial trigger
        if (seminarJenisSelect.value) {
            if (!noSuratInput?.value) updateNoSurat(seminarJenisSelect.value);
            renderUploads();
        }

        seminarJenisSelect.dataset.initialized = 'true';
    }

    // Standardized Init Pattern
    window.addEventListener('app:init', initSeminarCreate);
    window.addEventListener('page-loaded', initSeminarCreate);
    
    if (document.readyState !== 'loading') {
        initSeminarCreate();
    } else {
        document.addEventListener('DOMContentLoaded', initSeminarCreate);
    }
})();
</script>
@endsection