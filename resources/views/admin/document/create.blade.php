@extends('layouts.app')

@section('title', 'Tambah Template Dokumen')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Tambah Template Dokumen Baru</h1>
        @php
            $defaultEmailSubjectTemplate = 'Dokumen {{template_nama}} - {{mahasiswa_nama}}';
            $defaultEmailBodyTemplate = "Yth. {{mahasiswa_nama}},\n\nBerikut kami kirimkan dokumen {{template_nama}}.\n\nTerima kasih.";
        @endphp
        
        <form action="{{ route('admin.document.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Template</label>
                    <input 
                        type="text" 
                        name="nama" 
                        id="nama" 
                        value="{{ old('nama') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror" 
                        required
                    >
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="kode" class="block text-sm font-medium text-gray-700 mb-1">Kode Template</label>
                    <input 
                        type="text" 
                        name="kode" 
                        id="kode" 
                        value="{{ old('kode') }}" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('kode') border-red-500 @enderror" 
                        placeholder="Contoh: BRNG_NILAI, UND_SEMINAR"
                        required
                    >
                    @error('kode')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar (Optional)</label>
                    <select 
                        name="seminar_jenis_id" 
                        id="seminar_jenis_id" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror"
                    >
                        <option value="">-- Semua Jenis Seminar --</option>
                        @foreach($seminarJenis as $jenis)
                            <option value="{{ $jenis->id }}" {{ old('seminar_jenis_id') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->nama }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-sm text-gray-500 mt-1">Pilih jenis seminar jika template ini spesifik untuk jenis tertentu</p>
                    @error('seminar_jenis_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea 
                        name="keterangan" 
                        id="keterangan" 
                        rows="3" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('keterangan') border-red-500 @enderror"
                    >{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">Template Email Default</h2>
                    <p class="text-sm text-gray-600 mb-4">Placeholder email sama dengan tag dokumen. Gunakan format <code class="bg-gray-200 px-1 rounded text-xs">${TAG}</code>.</p>
                    <div class="space-y-4">
                        <div>
                            <label for="email_subject_template" class="block text-sm font-medium text-gray-700 mb-1">Subject Default</label>
                            <input 
                                type="text"
                                name="email_subject_template"
                                id="email_subject_template"
                                value="{{ old('email_subject_template', $defaultEmailSubjectTemplate) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md @error('email_subject_template') border-red-500 @enderror"
                                maxlength="255"
                            >
                            @error('email_subject_template')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label for="email_body_template" class="block text-sm font-medium text-gray-700 mb-1">Isi Email Default</label>
                            <textarea 
                                name="email_body_template"
                                id="email_body_template"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm @error('email_body_template') border-red-500 @enderror"
                            >{{ old('email_body_template', $defaultEmailBodyTemplate) }}</textarea>
                            @error('email_body_template')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">File Template (.docx)</label>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded p-3 mb-3 text-sm">
                        <p class="text-blue-900 mb-2">
                            💡 <strong>Tip:</strong> Untuk template baru, buat file HTML/Blade di <code class="bg-blue-100 px-1 rounded text-xs">resources/views/documents/</code>
                        </p>
                        <p class="text-xs text-blue-700">
                            Format tag: <code class="bg-blue-100 px-1 rounded">@{{ $mahasiswa_nama }}</code> | 
                            <a href="{{ asset('TEMPLATE_TAGS_DOCUMENTATION.md') }}" class="underline">Lihat semua tag →</a>
                        </p>
                    </div>
                    
                    <input 
                        type="file" 
                        name="file" 
                        id="file" 
                        accept=".docx" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('file') border-red-500 @enderror" 
                        required
                    >
                    <p class="text-sm text-gray-500 mt-1">Upload file template dalam format .docx (maks. 10MB)</p>
                    @error('file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.document.templates') }}" class="btn-pill btn-pill-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Simpan Template
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection