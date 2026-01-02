@extends('layouts.app')

@section('title', 'Edit Seminar')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Edit Pendaftaran Seminar</h1>
        
        <form action="{{ route('mahasiswa.seminar.update', $seminar) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar</label>
                    <select name="seminar_jenis_id" id="seminar_jenis_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror" required>
                        <option value="">Pilih Jenis Seminar</option>
                        @foreach($seminarJenis as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('seminar_jenis_id', $seminar->seminar_jenis_id) == $jenis->id ? 'selected' : '' }}>
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
                    <textarea name="judul" id="judul" class="hidden" required>{{ old('judul', $seminar->judul) }}</textarea>
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
                            value="{{ old('tanggal', $seminar->tanggal) }}"
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
                            value="{{ old('waktu', $seminar->waktu_mulai) }}"
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
                        value="{{ old('lokasi', $seminar->lokasi) }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('lokasi') border-red-500 @enderror" 
                        required
                    >
                    @error('lokasi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="p1_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 1 (P1)</label>
                        <select name="p1_dosen_id" id="p1_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p1_dosen_id') border-red-500 @enderror">
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p1_dosen_id', $seminar->p1_dosen_id) == $dosen->id ? 'selected' : '' }}>
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
                        <select name="p2_dosen_id" id="p2_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p2_dosen_id') border-red-500 @enderror">
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p2_dosen_id', $seminar->p2_dosen_id) == $dosen->id ? 'selected' : '' }}>
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
                        <select name="pembahas_dosen_id" id="pembahas_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('pembahas_dosen_id') border-red-500 @enderror">
                            <option value="">Pilih Dosen</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id) == $dosen->id ? 'selected' : '' }}>
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
                    @php
                        $jenis = $seminarJenis->firstWhere('id', $seminar->seminar_jenis_id);
                        $items = $jenis && is_array($jenis->berkas_syarat_items) ? $jenis->berkas_syarat_items : [];

                        $existingBerkas = is_array($seminar->berkas_syarat) ? $seminar->berkas_syarat : [];
                    @endphp

                    @if(is_array($items) && count($items))
                        @foreach($items as $item)
                            @php
                                if (!is_array($item)) continue;
                                $key = $item['key'] ?? null;
                                $label = $item['label'] ?? null;
                                $required = array_key_exists('required', $item) ? (bool) $item['required'] : true;
                                if (!$key || !$label) continue;
                                $existingPath = is_array($existingBerkas) ? ($existingBerkas[$key] ?? null) : null;

                                $itemExts = (isset($item['extensions']) && is_array($item['extensions']) && count($item['extensions']))
                                    ? $item['extensions']
                                    : ['pdf'];
                                $itemAccept = implode(',', array_map(fn($e) => '.' . ltrim((string) $e, '.'), $itemExts));
                                $itemMaxKb = (int) (($item['max_size_kb'] ?? null) ?: 5120);
                                if ($itemMaxKb < 1) {
                                    $itemMaxKb = 5120;
                                }
                                $itemMaxMb = round(($itemMaxKb / 1024), 1);
                            @endphp
                            
                            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group mb-4">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-bold text-gray-800 truncate">{{ $label }}</h3>
                                        <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                            {{ $required ? 'WAJIB' : 'OPSIONAL' }} • {{ strtoupper(implode(', ', $itemExts)) }}
                                        </p>
                                    </div>
                                    <span class="flex-shrink-0 bg-{{ $existingPath ? 'emerald' : 'gray' }}-100 text-{{ $existingPath ? 'emerald' : 'gray' }}-700 text-[10px] font-bold px-2 py-1 rounded-full">
                                        {{ $existingPath ? 'FILE ADA' : 'BELUM ADA' }}
                                    </span>
                                </div>
                                
                                <div class="relative group/input">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                        {{ $existingPath ? 'Ganti Berkas' : 'Unggah Berkas' }}
                                    </label>
                                    <input
                                        type="file"
                                        name="berkas_syarat_items[{{ $key }}]"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                        accept="{{ $itemAccept }}"
                                    >
                                    <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maks: {{ $itemMaxMb }}MB. (Kosongkan jika tidak ubah)</p>
                                </div>

                                @if($existingPath)
                                    <div class="mt-4 pt-3 border-t border-gray-50">
                                        <p class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-1">File Saat Ini:</p>
                                        <div class="flex items-center text-xs font-mono text-blue-600 break-all bg-blue-50 p-2 rounded-lg border border-blue-100">
                                            <i class="fas fa-file-pdf me-2 text-blue-500"></i>
                                            <span>{{ basename($existingPath) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @endif
                    
                    
                </div>
                
                <div class="flex flex-col sm:flex-row items-center gap-3 pt-6 border-t border-gray-100">
                    <button type="submit" class="btn-pill btn-pill-primary w-full sm:w-auto">
                        <i class="fas fa-save me-2"></i>Update Seminar
                    </button>
                    
                    <button type="button" class="btn-pill btn-pill-danger w-full sm:w-auto" onclick="if(confirm('Apakah Anda yakin ingin membatalkan seminar ini?')) document.getElementById('cancel-form').submit();">
                        <i class="fas fa-times-circle me-2"></i>Batal Seminar
                    </button>
                </div>
            </div>
        </form>

        <form id="cancel-form" action="{{ route('mahasiswa.seminar.cancel', $seminar) }}" method="POST" class="hidden">
            @csrf
            @method('PUT')
        </form>
    </div>
</div>

<script type="application/json" id="seminar-jenis-evaluator-rules">
    {!! json_encode(collect($seminarJenis)->mapWithKeys(fn($j) => [$j->id => [
        'p1_required' => (bool) ($j->p1_required ?? true),
        'p2_required' => (bool) ($j->p2_required ?? true),
        'pembahas_required' => (bool) ($j->pembahas_required ?? true),
    ]])->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!}
</script>

<script>
    (function () {
        function initSeminarEdit() {
            const select = document.getElementById('seminar_jenis_id');
            const rulesEl = document.getElementById('seminar-jenis-evaluator-rules');

            const p1 = document.getElementById('p1_dosen_id');
            const p2 = document.getElementById('p2_dosen_id');
            const pembahas = document.getElementById('pembahas_dosen_id');

            if (!select || !rulesEl || !p1 || !p2 || !pembahas) return;
            if (select.dataset.initialized === 'true') return;

            let rules = {};
            try {
                rules = JSON.parse(rulesEl.textContent || '{}') || {};
            } catch (e) {
                rules = {};
            }

            const setReq = (el, required) => {
                if (required) {
                    el.setAttribute('required', 'required');
                } else {
                    el.removeAttribute('required');
                }
            };

            const apply = () => {
                const id = select.value;
                const r = id && rules[id] ? rules[id] : null;

                setReq(p1, r ? !!r.p1_required : true);
                setReq(p2, r ? !!r.p2_required : true);
                setReq(pembahas, r ? !!r.pembahas_required : true);
            };

            select.addEventListener('change', apply);
            apply();
            
            select.dataset.initialized = 'true';
        }

        window.addEventListener('app:init', initSeminarEdit);
        window.addEventListener('page-loaded', initSeminarEdit);
        if (document.readyState !== 'loading') initSeminarEdit();
        else document.addEventListener('DOMContentLoaded', initSeminarEdit);
    })();
</script>
@vite('resources/js/signature-pad.js')
@endsection