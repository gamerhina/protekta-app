@extends('layouts.app')

@section('title', 'Ubah Seminar')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white overflow-hidden shadow-lg sm:rounded-2xl p-8 space-y-8 border border-gray-100">
        <div class="flex items-center justify-between">
            <h1 class="text-3xl font-semibold text-gray-800">Ubah Seminar</h1>
            <span class="text-sm text-gray-500">Perbarui informasi seminar secara lengkap</span>
        </div>

        @php
            $berkasSyarat = null;
            if($seminar->berkas_syarat) {
                if(is_array($seminar->berkas_syarat)) {
                    $berkasSyarat = $seminar->berkas_syarat;
                } else {
                    $berkasSyarat = json_decode($seminar->berkas_syarat, true);
                }
            }
        @endphp

        <form method="POST" action="{{ route('admin.seminar.update', $seminar->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mahasiswa</label>
                    <input 
                        type="text" 
                        value="{{ $seminar->mahasiswa->nama ?? 'N/A' }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600"
                        readonly
                    />
                </div>

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
                    <label for="no_surat" class="block text-sm font-medium text-gray-700 mb-1">No. Surat</label>
                    <input 
                        type="text" 
                        name="no_surat" 
                        id="no_surat" 
                        value="{{ old('no_surat', $seminar->no_surat) }}" 
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
                    <textarea name="judul" id="judul" class="hidden" required>{{ old('judul', $seminar->judul) }}</textarea>
                    @error('judul')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            name="tanggal" 
                            id="tanggal" 
                            value="{{ old('tanggal', $seminar->tanggal->format('Y-m-d')) }}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('tanggal') border-red-500 @enderror"
                            required
                        />
                    </div>
                    @error('tanggal')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="waktu_mulai" class="block text-sm font-medium text-gray-700 mb-1">Waktu Mulai</label>
                    <div class="relative">
                        <input
                            type="time"
                            name="waktu_mulai"
                            id="waktu_mulai"
                            value="{{ old('waktu_mulai', $seminar->waktu_mulai ? \Carbon\Carbon::parse($seminar->waktu_mulai)->format('H:i') : '') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('waktu_mulai') border-red-500 @enderror"
                            required
                        />
                    </div>
                    @error('waktu_mulai')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
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
                    />
                    @error('lokasi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="p1_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 1</label>
                    <div class="space-y-2">
                        <select name="p1_dosen_id" id="p1_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p1_dosen_id') border-red-500 @enderror dosen-select-toggle" required>
                            <option value="">Pilih Pembimbing 1</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p1_dosen_id', $seminar->p1_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('p1_dosen_id', $seminar->p1_dosen_id === null && $seminar->p1_nama ? 'manual' : '') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="p1_manual_fields" class="{{ (old('p1_dosen_id', $seminar->p1_dosen_id ? '' : ($seminar->p1_nama ? 'manual' : '')) == 'manual') ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="p1_nama" value="{{ old('p1_nama', $seminar->p1_nama) }}" placeholder="Nama Pembimbing 1" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="p1_nip" value="{{ old('p1_nip', $seminar->p1_nip) }}" placeholder="NIP Pembimbing 1" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('p1_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="p2_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembimbing 2</label>
                    <div class="space-y-2">
                        <select name="p2_dosen_id" id="p2_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('p2_dosen_id') border-red-500 @enderror dosen-select-toggle">
                            <option value="">(Opsional) Pilih Pembimbing 2</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('p2_dosen_id', $seminar->p2_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('p2_dosen_id', $seminar->p2_dosen_id === null && $seminar->p2_nama ? 'manual' : '') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="p2_manual_fields" class="{{ (old('p2_dosen_id', $seminar->p2_dosen_id ? '' : ($seminar->p2_nama ? 'manual' : '')) == 'manual') ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="p2_nama" value="{{ old('p2_nama', $seminar->p2_nama) }}" placeholder="Nama Pembimbing 2" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="p2_nip" value="{{ old('p2_nip', $seminar->p2_nip) }}" placeholder="NIP Pembimbing 2" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('p2_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="pembahas_dosen_id" class="block text-sm font-medium text-gray-700 mb-1">Pembahas</label>
                    <div class="space-y-2">
                        <select name="pembahas_dosen_id" id="pembahas_dosen_id" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('pembahas_dosen_id') border-red-500 @enderror dosen-select-toggle">
                            <option value="">(Opsional) Pilih Pembahas</option>
                            @foreach($dosens as $dosen)
                                <option value="{{ $dosen->id }}" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id) == $dosen->id ? 'selected' : '' }}>
                                    {{ $dosen->nama }}
                                </option>
                            @endforeach
                            <option value="manual" {{ old('pembahas_dosen_id', $seminar->pembahas_dosen_id === null && $seminar->pembahas_nama ? 'manual' : '') == 'manual' ? 'selected' : '' }}>Lainnya (Ketik Manual)</option>
                        </select>
                        <div id="pembahas_manual_fields" class="{{ (old('pembahas_dosen_id', $seminar->pembahas_dosen_id ? '' : ($seminar->pembahas_nama ? 'manual' : '')) == 'manual') ? '' : 'hidden' }} space-y-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                            <input type="text" name="pembahas_nama" value="{{ old('pembahas_nama', $seminar->pembahas_nama) }}" placeholder="Nama Pembahas" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                            <input type="text" name="pembahas_nip" value="{{ old('pembahas_nip', $seminar->pembahas_nip) }}" placeholder="NIP Pembahas" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md">
                        </div>
                    </div>
                    @error('pembahas_dosen_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md @error('status') border-red-500 @enderror" required>
                        <option value="diajukan" {{ old('status', $seminar->status) == 'diajukan' ? 'selected' : '' }}>Diajukan</option>
                        <option value="disetujui" {{ old('status', $seminar->status) == 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ old('status', $seminar->status) == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="belum_lengkap" {{ old('status', $seminar->status) == 'belum_lengkap' ? 'selected' : '' }}>Belum Lengkap</option>
                        <option value="selesai" {{ old('status', $seminar->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="md:col-span-2 mt-8 pt-8 border-t border-gray-100" id="syarat-upload-container">
                    <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-file-upload text-blue-500"></i>
                        Berkas Persyaratan
                    </h2>

                    @php
                        // Get berkas_syarat_items for currently selected seminar jenis
                        $items = is_array($seminar->seminarJenis?->berkas_syarat_items) ? $seminar->seminarJenis->berkas_syarat_items : [];
                        $existing = is_array($berkasSyarat) ? $berkasSyarat : [];
                    @endphp

                    @if(is_array($items) && count($items))
                        <div class="grid grid-cols-1 gap-6">
                            @foreach($items as $item)
                                @php
                                    if (!is_array($item)) continue;
                                    $key = $item['key'] ?? null;
                                    $label = $item['label'] ?? null;
                                    if (!$key || !$label) continue;
                                    $required = array_key_exists('required', $item) ? (bool) $item['required'] : true;
                                    $itemExts = (isset($item['extensions']) && is_array($item['extensions']) && count($item['extensions'])) ? $item['extensions'] : ['pdf'];
                                    $itemAccept = implode(',', array_map(fn($e) => '.' . ltrim((string) $e, '.'), $itemExts));
                                    $itemMaxKb = (int) (($item['max_size_kb'] ?? null) ?: 5120);
                                    if ($itemMaxKb < 1) $itemMaxKb = 5120;
                                    $itemMaxMb = round(($itemMaxKb / 1024), 1);
                                    $existingPath = is_array($existing) ? ($existing[$key] ?? null) : null;
                                @endphp

                                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-sm font-bold text-gray-800 truncate">{{ $label }}</h3>
                                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                                {{ $required ? 'Wajib' : 'Opsional' }} • {{ strtoupper(implode(', ', $itemExts)) }}
                                            </p>
                                        </div>
                                        @if(is_string($existingPath) && $existingPath !== '')
                                            <span class="flex-shrink-0 bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-1 rounded-full">TERUNGGAH</span>
                                        @else
                                            <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">BELUM ADA</span>
                                        @endif
                                    </div>
                                    
                                    <div class="space-y-4">
                                        @if(is_string($existingPath) && $existingPath !== '')
                                            <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="flex items-center gap-3 min-w-0">
                                                        <div class="bg-blue-600 text-white p-2 rounded-lg shadow-sm">
                                                            <i class="fas fa-file-pdf"></i>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <p class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Nama File</p>
                                                            <p class="text-sm text-gray-700 truncate font-mono font-medium" title="{{ basename($existingPath) }}">{{ basename($existingPath) }}</p>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('admin.seminar.files.show', ['path' => $existingPath]) }}" target="_blank" class="flex-shrink-0 bg-white text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-100 p-2 rounded-lg transition-all shadow-sm" title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <button type="button" class="w-full text-center px-4 py-2 text-xs font-bold text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 hover:border-red-200 transition-all flex items-center justify-center gap-2" onclick="deleteBerkas('{{ $key }}')">
                                                <i class="fas fa-trash-alt"></i> Hapus Berkas Saat Ini
                                            </button>
                                        @endif

                                        <div class="relative group/input">
                                            <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                                {{ is_string($existingPath) && $existingPath !== '' ? 'Ganti Berkas' : 'Unggah Berkas' }}
                                            </label>
                                            <input
                                                type="file"
                                                name="berkas_syarat_items[{{ $key }}]"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                                accept="{{ $itemAccept }}"
                                                {{ $required && !$existingPath ? 'required' : '' }}
                                            />
                                            <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maksimum ukuran file: <span class="font-bold text-gray-600">{{ $itemMaxMb }}MB</span></p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center">
                            <i class="fas fa-folder-open text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500 font-medium">Tidak ada berkas persyaratan yang diperlukan untuk jenis seminar ini.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Nilai Seminar Section - Assessment Aspects -->
            <div class="mt-10 pt-10 border-t border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Nilai Seminar</h2>
                
                @php
                    // Get assessment aspects for this seminar type
                    $assessmentAspects = $seminar->seminarJenis->assessmentAspects ?? collect();
                    $p1Aspects = $assessmentAspects->where('evaluator_type', 'p1');
                    $p2Aspects = $assessmentAspects->where('evaluator_type', 'p2');
                    $pembahasAspects = $assessmentAspects->where('evaluator_type', 'pembahas');
                    
                    // Get existing nilai records, memastikan sesuai dosen yang saat ini terdaftar di seminar
                    $p1Nilai = $seminar->nilai->first(function ($n) use ($seminar) {
                        return $n->jenis_penilai === 'p1' && (int) $n->dosen_id === (int) $seminar->p1_dosen_id;
                    });

                    $p2Nilai = $seminar->nilai->first(function ($n) use ($seminar) {
                        return $n->jenis_penilai === 'p2' && (int) $n->dosen_id === (int) $seminar->p2_dosen_id;
                    });

                    $pembahasNilai = $seminar->nilai->first(function ($n) use ($seminar) {
                        return $n->jenis_penilai === 'pembahas' && (int) $n->dosen_id === (int) $seminar->pembahas_dosen_id;
                    });
                    
                    // Get assessment scores if using new system
                    $p1Scores = $p1Nilai ? $p1Nilai->assessmentScores->keyBy('assessment_aspect_id') : collect();
                    $p2Scores = $p2Nilai ? $p2Nilai->assessmentScores->keyBy('assessment_aspect_id') : collect();
                    $pembahasScores = $pembahasNilai ? $pembahasNilai->assessmentScores->keyBy('assessment_aspect_id') : collect();
                @endphp

                @if($assessmentAspects->isEmpty())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <svg class="w-12 h-12 text-yellow-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-yellow-800 font-medium">Belum Ada Aspek Penilaian</p>
                        <p class="text-yellow-600 text-sm mt-1">Silakan konfigurasi aspek penilaian untuk jenis seminar "{{ $seminar->seminarJenis->nama }}" terlebih dahulu.</p>
                        <a href="{{ route('admin.seminarjenis.edit', $seminar->seminarJenis) }}" class="inline-block mt-4 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                            Kelola Aspek Penilaian
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Pembimbing 1 -->
                        <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-blue-50 to-white shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm mr-2">P1</span>
                                {{ $seminar->p1Dosen->nama ?? ($seminar->p1_nama ?? 'N/A') }}
                            </h3>

                            @php
                                $p1Signature = $seminar->signatures->first(function ($signature) use ($seminar) {
                                    return $signature->jenis_penilai === 'p1' && (int) $signature->dosen_id === (int) $seminar->p1_dosen_id;
                                });
                            @endphp
                            
                            @if($p1Aspects->isEmpty())
                                <p class="text-gray-500 text-sm italic">Belum ada aspek untuk Pembimbing 1</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($p1Aspects->sortBy('urutan') as $aspect)
                                        @php
                                            $score = $p1Scores->get($aspect->id);
                                            $nilaiValue = $score ? $score->nilai : null;
                                        @endphp
                                        <div class="bg-white rounded-lg p-3 border border-blue-100">
                                            <div class="mb-1">
                                                <span class="text-sm font-medium text-gray-700">{{ $aspect->nama_aspek }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-xs text-gray-500 mr-2">Nilai:</span>
                                                <input 
                                                    type="number" 
                                                    name="aspect_scores[p1][{{ $aspect->id }}]" 
                                                    min="0" 
                                                    max="100" 
                                                    step="0.01"
                                                    value="{{ $nilaiValue }}"
                                                    class="w-20 px-2 py-1 text-sm border border-blue-200 rounded focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
                                                    placeholder="0-100"
                                                />
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Notes Section P1 -->
                                    <div class="mt-4 pt-4 border-t border-blue-200">
                                        <label class="text-xs text-gray-600 font-semibold mb-2 block">Catatan</label>
                                        <textarea 
                                            name="nilai_catatan[p1]" 
                                            rows="3" 
                                            class="w-full px-3 py-2 text-sm border border-blue-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-200" 
                                            placeholder="Tambahkan catatan untuk Pembimbing 1...">{{ old('nilai_catatan.p1', $p1Nilai->catatan ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <!-- E-Signature Section P1 -->
                            <div class="mt-4 pt-4 border-t border-blue-200">
                                <label class="text-xs text-gray-600 font-semibold mb-2 block">Tanda Tangan Digital</label>
                                
                                @if($p1Signature && $p1Signature->tanda_tangan)
                                    <div class="mb-2 text-center bg-white rounded-lg p-3 border border-blue-200">
                                        <img src="{{ route('admin.seminar.files.show', ['path' => $p1Signature->tanda_tangan]) }}?t={{ time() }}" alt="Signature P1" class="h-16 mx-auto">
                                        <p class="text-xs text-gray-500 mt-1">{{ $p1Signature->tanggal_ttd ? $p1Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '' }}</p>
                                    </div>
                                @endif
                                
                                <div class="signature-pad-wrapper-p1">
                                    <input type="hidden" name="signatures[p1][data]" class="signature-input-p1">
                                    <input type="hidden" name="signatures[p1][dosen_id]" value="{{ $seminar->p1_dosen_id }}">
                                    <input type="hidden" name="signatures[p1][jenis_penilai]" value="p1">
                                    
                                    <button type="button" class="toggle-signature-btn-p1 text-xs text-blue-600 hover:text-blue-800 mb-2 w-full py-1 border border-blue-300 rounded">
                                        {{ $p1Signature ? 'Ubah Tanda Tangan' : 'Buat Tanda Tangan' }}
                                    </button>
                                    
                                    <div class="signature-pad-container-p1 hidden">
                                        <canvas width="360" height="120" class="signature-canvas-p1 border border-blue-200 rounded bg-white cursor-crosshair w-full"></canvas>
                                        <button type="button" class="clear-signature-btn-p1 text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full">Bersihkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pembimbing 2 -->
                        <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-green-50 to-white shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm mr-2">P2</span>
                                {{ $seminar->p2Dosen->nama ?? ($seminar->p2_nama ?? 'N/A') }}
                            </h3>

                            @php
                                $p2Signature = $seminar->signatures->first(function ($signature) use ($seminar) {
                                    return $signature->jenis_penilai === 'p2' && (int) $signature->dosen_id === (int) $seminar->p2_dosen_id;
                                });
                            @endphp
                            
                            @if($p2Aspects->isEmpty())
                                <p class="text-gray-500 text-sm italic">Belum ada aspek untuk Pembimbing 2</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($p2Aspects->sortBy('urutan') as $aspect)
                                        @php
                                            $score = $p2Scores->get($aspect->id);
                                            $nilaiValue = $score ? $score->nilai : null;
                                        @endphp
                                        <div class="bg-white rounded-lg p-3 border border-green-100">
                                            <div class="mb-1">
                                                <span class="text-sm font-medium text-gray-700">{{ $aspect->nama_aspek }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-xs text-gray-500 mr-2">Nilai:</span>
                                                <input 
                                                    type="number" 
                                                    name="aspect_scores[p2][{{ $aspect->id }}]" 
                                                    min="0" 
                                                    max="100" 
                                                    step="0.01"
                                                    value="{{ $nilaiValue }}"
                                                    class="w-20 px-2 py-1 text-sm border border-green-200 rounded focus:border-green-500 focus:ring-1 focus:ring-green-500"
                                                    placeholder="0-100"
                                                />
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Notes Section P2 -->
                                    <div class="mt-4 pt-4 border-t border-green-200">
                                        <label class="text-xs text-gray-600 font-semibold mb-2 block">Catatan</label>
                                        <textarea 
                                            name="nilai_catatan[p2]" 
                                            rows="3" 
                                            class="w-full px-3 py-2 text-sm border border-green-200 rounded-lg focus:border-green-500 focus:ring-1 focus:ring-green-200" 
                                            placeholder="Tambahkan catatan untuk Pembimbing 2...">{{ old('nilai_catatan.p2', $p2Nilai->catatan ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <!-- E-Signature Section P2 -->
                            <div class="mt-4 pt-4 border-t border-green-200">
                                <label class="text-xs text-gray-600 font-semibold mb-2 block">Tanda Tangan Digital</label>
                                
                                @if($p2Signature && $p2Signature->tanda_tangan)
                                    <div class="mb-2 text-center bg-white rounded-lg p-3 border border-green-200">
                                        <img src="{{ route('admin.seminar.files.show', ['path' => $p2Signature->tanda_tangan]) }}?t={{ time() }}" alt="Signature P2" class="h-16 mx-auto">
                                        <p class="text-xs text-gray-500 mt-1">{{ $p2Signature->tanggal_ttd ? $p2Signature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '' }}</p>
                                    </div>
                                @endif
                                
                                <div class="signature-pad-wrapper-p2">
                                    <input type="hidden" name="signatures[p2][data]" class="signature-input-p2">
                                    <input type="hidden" name="signatures[p2][dosen_id]" value="{{ $seminar->p2_dosen_id }}">
                                    <input type="hidden" name="signatures[p2][jenis_penilai]" value="p2">
                                    
                                    <button type="button" class="toggle-signature-btn-p2 text-xs text-green-600 hover:text-green-800 mb-2 w-full py-1 border border-green-300 rounded">
                                        {{ $p2Signature ? 'Ubah Tanda Tangan' : 'Buat Tanda Tangan' }}
                                    </button>
                                    
                                    <div class="signature-pad-container-p2 hidden">
                                        <canvas width="360" height="120" class="signature-canvas-p2 border border-green-200 rounded bg-white cursor-crosshair w-full"></canvas>
                                        <button type="button" class="clear-signature-btn-p2 text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full">Bersihkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pembahas -->
                        <div class="border border-gray-200 rounded-xl p-5 bg-gradient-to-br from-purple-50 to-white shadow-sm">
                            <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                <span class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center text-sm mr-2">PMB</span>
                                {{ $seminar->pembahasDosen->nama ?? ($seminar->pembahas_nama ?? 'N/A') }}
                            </h3>

                            @php
                                $pembahasSignature = $seminar->signatures->first(function ($signature) use ($seminar) {
                                    return $signature->jenis_penilai === 'pembahas' && (int) $signature->dosen_id === (int) $seminar->pembahas_dosen_id;
                                });
                            @endphp
                            
                            @if($pembahasAspects->isEmpty())
                                <p class="text-gray-500 text-sm italic">Belum ada aspek untuk Pembahas</p>
                            @else
                                <div class="space-y-3">
                                    @foreach($pembahasAspects->sortBy('urutan') as $aspect)
                                        @php
                                            $score = $pembahasScores->get($aspect->id);
                                            $nilaiValue = $score ? $score->nilai : null;
                                        @endphp
                                        <div class="bg-white rounded-lg p-3 border border-purple-100">
                                            <div class="mb-1">
                                                <span class="text-sm font-medium text-gray-700">{{ $aspect->nama_aspek }}</span>
                                            </div>
                                            <div class="flex items-center">
                                                <span class="text-xs text-gray-500 mr-2">Nilai:</span>
                                                <input 
                                                    type="number" 
                                                    name="aspect_scores[pembahas][{{ $aspect->id }}]" 
                                                    min="0" 
                                                    max="100" 
                                                    step="0.01"
                                                    value="{{ $nilaiValue }}"
                                                    class="w-20 px-2 py-1 text-sm border border-purple-200 rounded focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                                                    placeholder="0-100"
                                                />
                                            </div>
                                        </div>
                                    @endforeach

                                    <!-- Notes Section Pembahas -->
                                    <div class="mt-4 pt-4 border-t border-purple-200">
                                        <label class="text-xs text-gray-600 font-semibold mb-2 block">Catatan</label>
                                        <textarea 
                                            name="nilai_catatan[pembahas]" 
                                            rows="3" 
                                            class="w-full px-3 py-2 text-sm border border-purple-200 rounded-lg focus:border-purple-500 focus:ring-1 focus:ring-purple-200" 
                                            placeholder="Tambahkan catatan untuk Pembahas...">{{ old('nilai_catatan.pembahas', $pembahasNilai->catatan ?? '') }}</textarea>
                                    </div>
                                </div>
                            @endif

                            <!-- E-Signature Section Pembahas -->
                            <div class="mt-4 pt-4 border-t border-purple-200">
                                <label class="text-xs text-gray-600 font-semibold mb-2 block">Tanda Tangan Digital</label>
                                
                                @if($pembahasSignature && $pembahasSignature->tanda_tangan)
                                    <div class="mb-2 text-center bg-white rounded-lg p-3 border border-purple-200">
                                        <img src="{{ route('admin.seminar.files.show', ['path' => $pembahasSignature->tanda_tangan]) }}?t={{ time() }}" alt="Signature Pembahas" class="h-16 mx-auto">
                                        <p class="text-xs text-gray-500 mt-1">{{ $pembahasSignature->tanggal_ttd ? $pembahasSignature->tanggal_ttd->timezone('Asia/Jakarta')->translatedFormat('d/m/Y H:i') : '' }}</p>
                                    </div>
                                @endif
                                
                                <div class="signature-pad-wrapper-pembahas">
                                    <input type="hidden" name="signatures[pembahas][data]" class="signature-input-pembahas">
                                    <input type="hidden" name="signatures[pembahas][dosen_id]" value="{{ $seminar->pembahas_dosen_id }}">
                                    <input type="hidden" name="signatures[pembahas][jenis_penilai]" value="pembahas">
                                    
                                    <button type="button" class="toggle-signature-btn-pembahas text-xs text-purple-600 hover:text-purple-800 mb-2 w-full py-1 border border-purple-300 rounded">
                                        {{ $pembahasSignature ? 'Ubah Tanda Tangan' : 'Buat Tanda Tangan' }}
                                    </button>
                                    
                                    <div class="signature-pad-container-pembahas hidden">
                                        <canvas width="360" height="120" class="signature-canvas-pembahas border border-purple-200 rounded bg-white cursor-crosshair w-full"></canvas>
                                        <button type="button" class="clear-signature-btn-pembahas text-xs px-3 py-1 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 mt-2 w-full">Bersihkan</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Overall Score Calculation with Weights -->
                    @if(($p1Nilai && $p1Scores->isNotEmpty()) || ($p2Nilai && $p2Scores->isNotEmpty()) || ($pembahasNilai && $pembahasScores->isNotEmpty()))
                        <div class="mt-8 bg-gradient-to-r from-indigo-50 to-blue-50 border-2 border-indigo-200 rounded-xl p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Perhitungan Nilai Akhir</h3>
                            
                            @php
                                // Get weight percentages from seminar type
                                $p1Weight = $seminar->seminarJenis->p1_weight ?? 35;
                                $p2Weight = $seminar->seminarJenis->p2_weight ?? 35;
                                $pembahasWeight = $seminar->seminarJenis->pembahas_weight ?? 30;
                                
                                // Calculate weighted scores
                                $p1FinalScore = 0;
                                $p2FinalScore = 0;
                                $pembahasFinalScore = 0;
                                
                                if ($p1Nilai && $p1Scores->isNotEmpty()) {
                                    $p1FinalScore = ($p1Nilai->nilai_angka * $p1Weight) / 100;
                                }
                                
                                if ($p2Nilai && $p2Scores->isNotEmpty()) {
                                    $p2FinalScore = ($p2Nilai->nilai_angka * $p2Weight) / 100;
                                }
                                
                                if ($pembahasNilai && $pembahasScores->isNotEmpty()) {
                                    $pembahasFinalScore = ($pembahasNilai->nilai_angka * $pembahasWeight) / 100;
                                }
                                
                                $totalFinalScore = $p1FinalScore + $p2FinalScore + $pembahasFinalScore;
                            @endphp
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div class="bg-white rounded-lg p-4 border border-blue-200">
                                    <div class="text-sm text-gray-600 mb-1">Pembimbing 1 ({{ $p1Weight }}%)</div>
                                    <div class="text-2xl font-bold text-blue-600">{{ number_format($p1FinalScore, 2) }}</div>
                                    @if($p1Nilai && $p1Scores->isNotEmpty())
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($p1Nilai->nilai_angka, 2) }} × {{ $p1Weight }}%</div>
                                    @endif
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border border-green-200">
                                    <div class="text-sm text-gray-600 mb-1">Pembimbing 2 ({{ $p2Weight }}%)</div>
                                    <div class="text-2xl font-bold text-green-600">{{ number_format($p2FinalScore, 2) }}</div>
                                    @if($p2Nilai && $p2Scores->isNotEmpty())
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($p2Nilai->nilai_angka, 2) }} × {{ $p2Weight }}%</div>
                                    @endif
                                </div>
                                
                                <div class="bg-white rounded-lg p-4 border border-purple-200">
                                    <div class="text-sm text-gray-600 mb-1">Pembahas ({{ $pembahasWeight }}%)</div>
                                    <div class="text-2xl font-bold text-purple-600">{{ number_format($pembahasFinalScore, 2) }}</div>
                                    @if($pembahasNilai && $pembahasScores->isNotEmpty())
                                        <div class="text-xs text-gray-500 mt-1">{{ number_format($pembahasNilai->nilai_angka, 2) }} × {{ $pembahasWeight }}%</div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="bg-indigo-600 text-white rounded-lg p-5 flex justify-between items-center">
                                <div>
                                    <div class="text-sm opacity-90 mb-1">Nilai Akhir Seminar</div>
                                    <div class="text-3xl font-bold">{{ number_format($totalFinalScore, 2) }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs opacity-75">Total Bobot: {{ $p1Weight + $p2Weight + $pembahasWeight }}%</div>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                @endif
            </div>

            <div class="mt-10 flex items-center justify-between">
                <a href="{{ route('admin.seminar.index') }}" class="btn-pill btn-pill-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-pill btn-pill-primary">
                    Simpan Seminar
                </button>
            </div>
        </form>

        <!-- Old format deletion handled via special route -->
    </div>
</div>
@endsection

@section('scripts')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <script>
        // Global function to handle deletion via form submit
        window.deleteBerkas = function(key) {
            if (!confirm('Apakah Anda yakin ingin menghapus berkas ini?')) return;
            
            const deleteRoute = "{{ route('admin.seminar.delete-berkas', [$seminar->id, 'KEY_PLACEHOLDER']) }}";
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = deleteRoute.replace('KEY_PLACEHOLDER', key);
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = "{{ csrf_token() }}";
            form.appendChild(csrf);
            
            const method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            method.value = 'DELETE';
            form.appendChild(method);
            
            document.body.appendChild(form);
            form.submit();
        };

        (function() {
            function initSeminarEdit() {
                const editor = document.getElementById('judul-editor');
                const textarea = document.getElementById('judul');
                const select = document.getElementById('seminar_jenis_id');
                const container = document.getElementById('syarat-upload-container');
                
                // 3. Manual Dosen Toggle
                document.querySelectorAll('.dosen-select-toggle').forEach(select => {
                    select.addEventListener('change', function() {
                        const role = this.id.replace('_dosen_id', ''); // p1, p2, or pembahas
                        const targetId = role + '_manual_fields';
                        const target = document.getElementById(targetId);
                        
                        // Toggle manual fields visibility
                        if (target) {
                            if (this.value === 'manual') {
                                target.classList.remove('hidden');
                            } else {
                                target.classList.add('hidden');
                            }
                        }

                        // Sync signature hidden dosen_id
                        const sigDosenInput = document.querySelector(`input[name="signatures[${role}][dosen_id]"]`);
                        if (sigDosenInput) {
                            sigDosenInput.value = this.value;
                        }
                    });
                });

                if (!editor || !textarea || !select || !container) return;
                
                // Prevent double-init
                if (editor.dataset.initialized === 'true') return;

                // 1. Quill Initialization
                if (typeof Quill !== 'undefined' && editor.getAttribute('data-quill-initialized') !== 'true') {
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

                // 2. Dynamic Berkas Logic
                const jenisData = {!! json_encode($seminarJenisData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
                const currentBerkas = {!! json_encode($currentBerkasSyarat, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
                const isOldFormat = @json($isOldFormat);

                const deleteRoute = "{{ route('admin.seminar.delete-berkas', [$seminar->id, 'KEY_PLACEHOLDER']) }}";

                function renderBerkasFields() {
                    container.innerHTML = '';
                    const selectedId = select.value;
                    const jenis = jenisData[selectedId];
                    if (!jenis || !jenis.berkas_syarat_items || !Array.isArray(jenis.berkas_syarat_items)) {
                        container.innerHTML = `
                            <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-10 text-center">
                                <i class="fas fa-folder-open text-gray-300 text-5xl mb-4"></i>
                                <p class="text-gray-500 font-medium">Tidak ada berkas persyaratan yang diperlukan untuk jenis seminar ini.</p>
                            </div>
                        `;
                        return;
                    }

                    // Create Grid Wrapper
                    container.innerHTML = `
                        <h2 class="text-xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
                            <i class="fas fa-file-upload text-blue-500"></i>
                            Berkas Persyaratan
                        </h2>
                        <div class="grid grid-cols-1 gap-6" id="berkas-grid-container"></div>
                    `;
                    const grid = document.getElementById('berkas-grid-container');

                    // Support old format file (Legacy)
                    if (isOldFormat && currentBerkas['__old_format_file__']) {
                        const oldItem = document.createElement('div');
                        oldItem.className = 'bg-white p-5 rounded-2xl border border-amber-200 shadow-sm transition-all bg-amber-50/30';
                        oldItem.innerHTML = `
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-sm font-bold text-amber-800 truncate">Berkas Format Lama</h3>
                                    <p class="text-[10px] text-amber-600 uppercase tracking-wider font-semibold mt-0.5">Legacy File Detected</p>
                                </div>
                                <span class="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-1 rounded-full text-center">PERLU DIHAPUS</span>
                            </div>
                            <div class="bg-white p-3 rounded-xl border border-amber-100 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="bg-amber-500 text-white p-2 rounded-lg"><i class="fas fa-exclamation-triangle"></i></div>
                                    <div class="min-w-0">
                                        <p class="text-[10px] font-bold text-amber-400 uppercase leading-none mb-1">Nama File</p>
                                        <p class="text-sm text-gray-700 truncate font-mono">${currentBerkas['__old_format_file__'].split('/').pop()}</p>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="w-full text-center px-4 py-2 text-xs font-bold text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 shadow-sm transition-all" 
                                    onclick="deleteBerkas('old_format_file')">
                                <i class="fas fa-trash-alt mr-1"></i> Hapus Berkas Lama
                            </button>
                        `;
                        grid.appendChild(oldItem);
                    }

                    jenis.berkas_syarat_items.forEach(item => {
                        if (!item || !item.key || !item.label) return;
                        const existingPath = isOldFormat ? '' : (currentBerkas[item.key] || '');
                        const isRequired = item.required !== false && !existingPath;
                        const itemExts = Array.isArray(item.extensions) && item.extensions.length ? item.extensions : ['pdf'];
                        const extensions = itemExts.map(e => '.' + e.replace(/^\./, '')).join(',');
                        const maxSize = item.max_size_kb ? Math.round(item.max_size_kb / 1024 * 10) / 10 : 5;
                        
                        const card = document.createElement('div');
                        card.className = 'bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group';
                        
                        let html = `
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-sm font-bold text-gray-800 truncate">${item.label}</h3>
                                    <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                        ${item.required === false ? 'Opsional' : 'Wajib'} • ${itemExts.join(', ').toUpperCase()}
                                    </p>
                                </div>
                                <span class="flex-shrink-0 ${existingPath ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500'} text-[10px] font-bold px-2 py-1 rounded-full">
                                    ${existingPath ? 'TERUNGGAH' : 'BELUM ADA'}
                                </span>
                            </div>
                            <div class="space-y-4">
                        `;

                        if (existingPath) {
                            const fileName = existingPath.split('/').pop();
                            html += `
                                <div class="bg-gray-50 p-3 rounded-xl border border-gray-100">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="bg-blue-600 text-white p-2 rounded-lg shadow-sm">
                                                <i class="fas fa-file-pdf"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[10px] font-bold text-gray-400 uppercase leading-none mb-1">Nama File</p>
                                                <p class="text-sm text-gray-700 truncate font-mono font-medium" title="${fileName}">${fileName}</p>
                                            </div>
                                        </div>
                                        <a href="/admin/seminars/files/${encodeURIComponent(existingPath)}" target="_blank" class="flex-shrink-0 bg-white text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-100 p-2 rounded-lg transition-all shadow-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                                <button type="button" class="w-full text-center px-4 py-2 text-xs font-bold text-red-600 bg-red-50 border border-red-100 rounded-xl hover:bg-red-100 transition-all flex items-center justify-center gap-2" 
                                        onclick="deleteBerkas('${item.key}')">
                                    <i class="fas fa-trash-alt"></i> Hapus Berkas Saat Ini
                                </button>
                            `;
                        }

                        html += `
                                <div class="relative">
                                    <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">
                                        ${existingPath ? 'Ganti Berkas' : 'Unggah Berkas'}
                                    </label>
                                    <input
                                        type="file"
                                        name="berkas_syarat_items[${item.key}]"
                                        class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all"
                                        accept="${extensions}"
                                        ${isRequired ? 'required' : ''}
                                    />
                                    <p class="text-[10px] text-gray-400 mt-2 italic px-1">Maksimum ukuran file: <span class="font-bold text-gray-600">${maxSize}MB</span></p>
                                </div>
                            </div>
                        `;
                        
                        card.innerHTML = html;
                        grid.appendChild(card);
                    });
                }

                select.addEventListener('change', renderBerkasFields);
                renderBerkasFields();
                
                editor.dataset.initialized = 'true';
            }

            // Standardized Init Pattern
            if (document.readyState !== 'loading') {
                initSeminarEdit();
            } else {
                document.addEventListener('DOMContentLoaded', initSeminarEdit);
            }
            window.addEventListener('page-loaded', initSeminarEdit);
            
            // For general PDF opening
            window.openPdfUrl = function(url) {
                window.open(url, '_blank');
            };
        })();
    </script>
    @vite('resources/js/signature-pad.js')
@endsection
