@extends('layouts.app')

@section('title', 'Daftar Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Pendaftaran Seminar</h1>

        <form action="{{ route('mahasiswa.seminar.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar</label>
                    <select
                        name="seminar_jenis_id"
                        id="seminar_jenis_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror"
                        required
                    >
                        <option value="">Pilih Jenis Seminar</option>
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
                    <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">Judul Seminar</label>
                    <div id="judul-editor"
                         style="height: 200px; min-height: 200px; border: 1px solid #ccc; border-radius: 6px; background: white;"
                         class="@error('judul') border-red-500 @enderror">
                    </div>
                    <textarea name="judul" id="judul" class="hidden" required>{{ old('judul') }}</textarea>
                    @error('judul')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input
                            type="date"
                            name="tanggal"
                            id="tanggal"
                            value="{{ old('tanggal') }}"
                            min="{{ date('Y-m-d') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('tanggal') border-red-500 @enderror"
                            required
                        >
                        <p class="text-sm text-gray-500 mt-1">Tanggal harus hari ini atau setelahnya</p>
                        @error('tanggal')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="waktu" class="block text-sm font-medium text-gray-700 mb-1">Waktu</label>
                        <input
                            type="time"
                            name="waktu"
                            id="waktu"
                            value="{{ old('waktu', '09:00') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('waktu') border-red-500 @enderror"
                            required
                        >
                        @error('waktu')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="lokasi" class="block text-sm font-medium text-gray-700 mb-1">Lokasi</label>
                    <input
                        type="text"
                        name="lokasi"
                        id="lokasi"
                        value="{{ old('lokasi') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('lokasi') border-red-500 @enderror"
                        placeholder="Masukkan Lokasi Seminar"
                        required
                    >
                    @error('lokasi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="p1_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 1 (P1)</label>
                        <select
                            name="p1_dosen_id"
                            id="p1_dosen_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p1_dosen_id') border-red-500 @enderror"
                        >
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p1_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }} ({{ $dosen->nip }})
                                </option>
                            @endforeach
                        </select>
                        @error('p1_dosen_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="p2_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 2 (P2)</label>
                        <select
                            name="p2_dosen_id"
                            id="p2_dosen_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p2_dosen_id') border-red-500 @enderror"
                        >
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p2_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }} ({{ $dosen->nip }})
                                </option>
                            @endforeach
                        </select>
                        @error('p2_dosen_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="pembahas_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembahas</label>
                        <select
                            name="pembahas_dosen_id"
                            id="pembahas_dosen_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('pembahas_dosen_id') border-red-500 @enderror"
                        >
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('pembahas_dosen_id') == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }} ({{ $dosen->nip }})
                                </option>
                            @endforeach
                        </select>
                        @error('pembahas_dosen_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <div id="syarat-seminar-box" class="hidden rounded-xl border border-blue-200 bg-blue-50 p-4 mb-3">
                        <p class="text-sm font-semibold text-blue-800">Syarat Seminar</p>
                        <div id="syarat-seminar-text" class="mt-2 text-sm text-blue-900 whitespace-pre-line"></div>
                    </div>

                    <script type="application/json" id="seminar-jenis-syarat-data">
                        {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => $j->syarat_seminar])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                    </script>

                    <script type="application/json" id="seminar-jenis-berkas-rules">
                        {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => [
                            'items' => $j->berkas_syarat_items ?: [],
                        ]])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                    </script>

                    <script type="application/json" id="seminar-jenis-evaluator-rules">
                        {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => [
                            'p1_required' => (bool) ($j->p1_required ?? true),
                            'p2_required' => (bool) ($j->p2_required ?? true),
                            'pembahas_required' => (bool) ($j->pembahas_required ?? true),
                        ]])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
                    </script>

                    <div id="berkas-syarat-dynamic"></div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Daftar Seminar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function() {
    function initSeminarRegister() {
        const select = document.getElementById('seminar_jenis_id');
        if (!select || select.dataset.initialized === 'true') return;

        // 1. Quill Editor
        const editorEl = document.getElementById('judul-editor');
        const textarea = document.getElementById('judul');
        if (editorEl && textarea && typeof Quill !== 'undefined') {
            const quill = new Quill(editorEl, {
                theme: 'snow',
                modules: { toolbar: [['bold', 'italic']] }
            });
            if (textarea.value) quill.root.innerHTML = textarea.value;
            quill.on('text-change', () => { textarea.value = quill.root.innerHTML; });
            const form = textarea.closest('form');
            if (form) form.addEventListener('submit', () => { textarea.value = quill.root.innerHTML; });
        }

        // 2. Syarat Seminar Text
        const box = document.getElementById('syarat-seminar-box');
        const text = document.getElementById('syarat-seminar-text');
        const dataEl = document.getElementById('seminar-jenis-syarat-data');
        let requirements = {};
        if (dataEl) {
            try { requirements = JSON.parse(dataEl.textContent || '{}'); } catch(e) {}
        }

        // 3. Berkas Dynamic Fields
        const rulesEl = document.getElementById('seminar-jenis-berkas-rules');
        const berkasContainer = document.getElementById('berkas-syarat-dynamic');
        let berkasRules = {};
        if (rulesEl) {
            try { berkasRules = JSON.parse(rulesEl.textContent || '{}'); } catch(e) {}
        }

        // 4. Evaluator Rules
        const evalRulesEl = document.getElementById('seminar-jenis-evaluator-rules');
        const p1Select = document.getElementById('p1_dosen_id');
        const p2Select = document.getElementById('p2_dosen_id');
        const pembahasSelect = document.getElementById('pembahas_dosen_id');
        let evalRules = {};
        if (evalRulesEl) {
            try { evalRules = JSON.parse(evalRulesEl.textContent || '{}'); } catch(e) {}
        }

        const render = () => {
            const id = select.value;
            
            // Render Syarat Text
            if (box && text) {
                const val = (id && requirements[id]) ? String(requirements[id]).trim() : '';
                if (val) {
                    text.textContent = val;
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');
                }
            }

            // Render Berkas
            if (berkasContainer) {
                const r = (id && berkasRules[id]) ? berkasRules[id] : null;
                const items = (r && Array.isArray(r.items)) ? r.items : [];
                berkasContainer.innerHTML = '';
                items.forEach(it => {
                    if (!it.key || !it.label) return;
                    const exts = (Array.isArray(it.extensions) && it.extensions.length) ? it.extensions : ['pdf'];
                    const maxKb = it.max_size_kb ? Number(it.max_size_kb) : 5120;
                    const accept = exts.map(e => '.' + String(e).replace(/^\./, '')).join(',');
                    const mb = Math.round(maxKb / 102.4) / 10;
                    const div = document.createElement('div');
                    div.className = 'mb-3';
                    div.innerHTML = `
                        <label class="block text-sm font-medium text-gray-700 mb-1">${it.label}${it.required === false ? ' (Opsional)' : ''}</label>
                        <input type="file" name="berkas_syarat_items[${it.key}]" class="w-full px-3 py-2 border border-gray-300 rounded-md file-input-mobile" accept="${accept}" ${it.required === false ? '' : 'required'}>
                        <p class="text-sm text-gray-500 mt-1">Format: ${exts.join(', ').toUpperCase()}. Maks: ${mb}MB.</p>
                    `;
                    berkasContainer.appendChild(div);
                });
            }

            // Apply Evaluator Rules
            if (p1Select && p2Select && pembahasSelect) {
                const r = (id && evalRules[id]) ? evalRules[id] : null;
                const setReq = (el, req) => req ? el.setAttribute('required', 'required') : el.removeAttribute('required');
                setReq(p1Select, r ? !!r.p1_required : true);
                setReq(p2Select, r ? !!r.p2_required : true);
                setReq(pembahasSelect, r ? !!r.pembahas_required : true);
            }
        };

        select.addEventListener('change', render);
        render();

        select.dataset.initialized = 'true';
    }

    // Standardized Init Pattern
    window.addEventListener('app:init', initSeminarRegister);
    window.addEventListener('page-loaded', initSeminarRegister);
    
    // Fallback/Direct
    if (document.readyState !== 'loading') initSeminarRegister();
    else document.addEventListener('DOMContentLoaded', initSeminarRegister);
})();
</script>
@vite('resources/js/signature-pad.js')
@endsection
