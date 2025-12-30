@extends('layouts.app')

@section('title', 'Edit Template')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Edit Template</h1>
                <p class="text-sm text-gray-600 mt-1">Template: <strong>{{ $template->nama }}</strong></p>
            </div>
            <a href="{{ route('admin.document.templates') }}" class="btn-pill btn-pill-secondary">
                Kembali
            </a>
        </div>



        @php
            $fileExists = file_exists(storage_path('app/private/' . $template->file_path));
            $defaultEmailSubjectTemplate = 'Dokumen {{template_nama}} - {{mahasiswa_nama}}';
            $defaultEmailBodyTemplate = "Yth. {{mahasiswa_nama}},\n\nBerikut kami kirimkan dokumen {{template_nama}}.\n\nTerima kasih.";
        @endphp


        
        <form action="{{ route('admin.document.update', $template->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Template</label>
                        <input 
                            type="text" 
                            name="nama" 
                            id="nama" 
                            value="{{ old('nama', $template->nama) }}" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror" 
                            required
                        >
                        @error('nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="seminar_jenis_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Seminar</label>
                        <select 
                            name="seminar_jenis_id" 
                            id="seminar_jenis_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md @error('seminar_jenis_id') border-red-500 @enderror"
                        >
                            <option value="">-- Semua Jenis Seminar --</option>
                            @foreach($seminarJenis as $jenis)
                                <option value="{{ $jenis->id }}" {{ old('seminar_jenis_id', $template->seminar_jenis_id) == $jenis->id ? 'selected' : '' }}>
                                    {{ $jenis->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('seminar_jenis_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea 
                        name="keterangan" 
                        id="keterangan" 
                        rows="2" 
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('keterangan') border-red-500 @enderror"
                    >{{ old('keterangan', $template->keterangan) }}</textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Template Email Default</h3>
                    <p class="text-sm text-gray-600 mb-4">Placeholder email sama dengan tag dokumen. Gunakan format <code class="bg-gray-200 px-1 rounded text-xs">${TAG}</code>.</p>
                    @if($template->available_tags && count($template->available_tags) > 0)
                        <div class="mb-4 space-y-2">
                            <label for="email_tag_picker" class="text-sm font-medium text-gray-700">Tag Tersedia</label>
                            <div class="flex flex-wrap gap-2">
                                <select id="email_tag_picker" class="flex-1 min-w-[200px] px-3 py-2 border border-gray-300 rounded-md text-sm">
                                    <option value="">-- Pilih Tag --</option>
                                    @foreach($template->available_tags as $tag)
                                        <option value="{{ $tag }}">{{ $tag }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="email-tag-btn px-3 py-2 bg-blue-100 text-blue-800 rounded-md text-xs font-semibold hover:bg-blue-200" data-target="subject">Ke Subject ${TAG}</button>
                                <button type="button" class="email-tag-btn px-3 py-2 bg-purple-100 text-purple-800 rounded-md text-xs font-semibold hover:bg-purple-200" data-target="body">Ke Isi ${TAG}</button>
                            </div>
                            <p class="text-xs text-gray-500">Gunakan tombol di atas untuk menyisipkan placeholder secara otomatis.</p>
                        </div>
                    @endif
                    <div class="space-y-4">
                        <div>
                            <label for="email_subject_template" class="block text-sm font-medium text-gray-700 mb-1">Subject Default</label>
                            <input 
                                type="text"
                                name="email_subject_template"
                                id="email_subject_template"
                                value="{{ old('email_subject_template', $template->email_subject_template ?? $defaultEmailSubjectTemplate) }}"
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
                            >{{ old('email_body_template', $template->email_body_template ?? $defaultEmailBodyTemplate) }}</textarea>
                            @error('email_body_template')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="hidden" name="aktif" value="0">
                    <input 
                        type="checkbox" 
                        name="aktif" 
                        id="aktif" 
                        value="1"
                        {{ old('aktif', $template->aktif) ? 'checked' : '' }}
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    >
                    <label for="aktif" class="ml-2 block text-sm text-gray-700">
                        Template Aktif
                    </label>
                </div>

                @if(!$fileExists)
                    <div class="bg-yellow-50 border border-yellow-300 rounded-lg p-4">
                        <h3 class="font-semibold text-yellow-900 mb-2">📎 Upload Ulang File Template</h3>
                        <input 
                            type="file" 
                            name="new_file" 
                            id="new_file" 
                            accept=".docx" 
                            class="w-full px-3 py-2 border border-yellow-300 rounded-md @error('new_file') border-red-500 @enderror"
                        >
                        <p class="text-xs text-yellow-800 mt-1">Gunakan format tag Word <code>${NAMA_TAG}</code> (huruf kapital dan underscore).</p>
                        @error('new_file')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                @else
                    <div class="bg-green-50 border border-green-300 rounded-lg p-4">
                        <h3 class="font-semibold text-green-900 mb-2">✅ File Template Tersedia</h3>
                        <p class="text-sm text-green-800">File: <code class="bg-green-100 px-2 py-1 rounded">{{ basename($template->file_path) }}</code></p>
                        <div class="mt-3">
                            <label class="block text-sm font-medium text-green-900 mb-1">Upload File Baru (Optional)</label>
                            <input 
                                type="file" 
                                name="new_file" 
                                id="new_file" 
                                accept=".docx" 
                                class="w-full px-3 py-2 border border-green-300 rounded-md text-sm"
                            >
                            <p class="text-xs text-green-800 mt-1">Upload file baru jika ingin mengganti template. Tags akan di-extract otomatis.</p>
                        </div>
                    </div>
                @endif

                <hr class="my-6">

                <div>
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Tag Management & Mapping</h2>
                            <p class="text-sm text-gray-600 mt-1">
                                Kelola tags dari template Word dan mapping ke field data
                            </p>
                        </div>
                        <form action="{{ route('admin.document.re-extract', $template->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="btn-pill btn-pill-primary text-sm">
                                🔄 Re-Extract Tags
                            </button>
                        </form>
                    </div>

                    <!-- Manual Add Tags Section -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <h3 class="font-semibold text-blue-900 mb-2">➕ Manual Add Tags</h3>
                        <p class="text-xs text-blue-800 mb-3">Tambah tags manual jika auto-extract belum mendeteksi semua tags dari dokumen Word</p>
                        <div class="flex gap-2">
                            <input type="text" 
                                   id="manual_tag_input" 
                                   placeholder="Nama tag (contoh: NAMA_MAHASISWA)" 
                                   class="flex-1 px-3 py-2 border border-blue-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                            <button type="button" 
                                    id="add_tag_btn" 
                                    class="btn-pill btn-pill-primary text-sm">
                                Tambah Tag
                            </button>
                        </div>
                    </div>

                    <!-- Current Tags Display -->
                    <div class="mb-4">
                        <h3 class="font-semibold text-gray-700 mb-2">📋 Tags yang Terdeteksi:</h3>
                        <p class="text-xs text-gray-500 mb-2">
                            Format Tag Word: <code class="bg-gray-100 px-1 rounded">${NAMA_TAG}</code>
                        </p>
                        <div id="tags_display" class="flex flex-wrap gap-2 p-3 bg-gray-50 rounded-lg min-h-[60px]">
                            @if($template->available_tags && count($template->available_tags) > 0)
                                @foreach($template->available_tags as $index => $tag)
                                    <span class="inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                        <code>{{ '${' . $tag . '}' }}</code>
                                        <button type="button" 
                                                class="remove-tag-btn ml-2 text-blue-600 hover:text-blue-900 font-bold" 
                                                data-tag="{{ $tag }}">×</button>
                                        <input type="hidden" name="available_tags[]" value="{{ $tag }}">
                                    </span>
                                @endforeach
                            @else
                                <p class="text-gray-400 text-sm italic">Tidak ada tags terdeteksi. Gunakan tombol "Re-Extract Tags" atau tambah manual.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Tag Mappings -->
                    <div id="tag_mappings_container">
                        <h3 class="font-semibold text-gray-700 mb-3">🔗 Mapping Tags ke Data Fields:</h3>
                        <p class="text-xs text-gray-600 mb-3">
                            Mapping tag dari template Word ke field data yang tersedia. Tags yang tidak dimapping akan tetap kosong di dokumen hasil.
                        </p>
                        
                        @if($template->available_tags && count($template->available_tags) > 0)
                            <div class="bg-gray-50 p-4 rounded-lg overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-1/4">
                                                Tag
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-1/3">
                                                Maps ke Field Data
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-5/12">
                                                Tipe Tag
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($template->available_tags as $tag)
                                            @php
                                                $currentType = isset($template->tag_types[$tag]) ? $template->tag_types[$tag] : 'standard';
                                                $currentProps = isset($template->tag_properties[$tag]) ? $template->tag_properties[$tag] : [];
                                            @endphp
                                            <tr class="tag-mapping-item" data-tag="{{ $tag }}">
                                                <td class="px-4 py-3 whitespace-nowrap">
                                                    <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ '${' . $tag . '}' }}</code>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <select 
                                                        name="tag_mappings[{{ $tag }}]" 
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm"
                                                    >
                                                        <option value="">-- Tidak dimapping --</option>
                                                        @foreach($availableFields as $category => $fields)
                                                            <optgroup label="{{ ucfirst($category) }}">
                                                                @foreach($fields as $fieldKey => $fieldLabel)
                                                                    <option value="{{ $fieldKey }}" 
                                                                        {{ isset($template->tag_mappings[$tag]) && $template->tag_mappings[$tag] == $fieldKey ? 'selected' : '' }}>
                                                                        {{ $fieldLabel }}
                                                                    </option>
                                                                @endforeach
                                                            </optgroup>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <select 
                                                        name="tag_types[{{ $tag }}]" 
                                                        class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm tag-type-select"
                                                        data-tag="{{ $tag }}"
                                                    >
                                                        <option value="standard" {{ $currentType == 'standard' ? 'selected' : '' }}>Standard (Text)</option>
                                                        <option value="hyperlink" {{ $currentType == 'hyperlink' ? 'selected' : '' }}>Hyperlink</option>
                                                        <option value="image" {{ $currentType == 'image' ? 'selected' : '' }}>Image</option>
                                                    </select>
                                                    
                                                    <!-- Hyperlink Properties -->
                                                    <div class="mt-2 tag-properties hyperlink-props" style="display: {{ $currentType == 'hyperlink' ? 'block' : 'none' }}">
                                                        <input 
                                                            type="text" 
                                                            name="tag_properties[{{ $tag }}][hyperlink_url]" 
                                                            placeholder="URL (e.g., https://example.com)"
                                                            value="{{ $currentProps['hyperlink_url'] ?? '' }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs"
                                                        >
                                                    </div>
                                                    
                                                    <!-- Image Properties -->
                                                    <div class="mt-2 space-y-2 tag-properties image-props" style="display: {{ $currentType == 'image' ? 'block' : 'none' }}">
                                                        <input 
                                                            type="text" 
                                                            name="tag_properties[{{ $tag }}][image_url]" 
                                                            placeholder="Image URL (optional - kosongkan untuk ambil dari database)"
                                                            value="{{ $currentProps['image_url'] ?? '' }}"
                                                            class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs"
                                                        >
                                                        <p class="text-xs text-gray-500 italic">💡 Kosongkan jika ingin menggunakan image dari database berdasarkan mapping field</p>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <input 
                                                                type="number" 
                                                                name="tag_properties[{{ $tag }}][image_width]" 
                                                                placeholder="Width (px)"
                                                                value="{{ $currentProps['image_width'] ?? '' }}"
                                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs"
                                                            >
                                                            <input 
                                                                type="number" 
                                                                name="tag_properties[{{ $tag }}][image_height]" 
                                                                placeholder="Height (px)"
                                                                value="{{ $currentProps['image_height'] ?? '' }}"
                                                                class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs"
                                                            >
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                                <p class="font-medium">⚠️ Tidak ada tags untuk dimapping</p>
                                <p class="text-sm mt-1">Tambahkan tags terlebih dahulu menggunakan "Re-Extract Tags" atau manual add.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <h3 class="font-semibold text-blue-900 mb-2">💡 Available Fields Reference</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                        @foreach($availableFields as $category => $fields)
                            <div>
                                <h4 class="font-semibold text-gray-700 mb-1">{{ ucfirst($category) }}</h4>
                                <ul class="text-xs text-gray-600 space-y-0.5">
                                    @foreach($fields as $fieldKey => $fieldLabel)
                                        <li>• {{ $fieldLabel }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.document.templates') }}" class="btn-pill btn-pill-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@section('scripts')
{{-- Scripts moved to bottom of content to ensure AJAX execution --}}
@endsection

<script>
(function() {
    // Global Error Handler for this specific page component
    try {
        console.log("Document Edit Script Loading...");
        // State management
        const availableFields = @json($availableFields) || {};
        // initialized check removed to allow fresh runs

        // Helper Functions
        function handleTagTypeChange(select) {
            const tagName = select.getAttribute('data-tag');
            if (!tagName) return;
            
            const selectedType = select.value;
            const row = select.closest('tr');
            
            const allPropsInRow = row.querySelectorAll('.tag-properties');
            allPropsInRow.forEach(prop => prop.style.display = 'none');
            
            if (selectedType === 'hyperlink') {
                const hyperlinkProps = row.querySelector('.hyperlink-props');
                if (hyperlinkProps) hyperlinkProps.style.display = 'block';
            } else if (selectedType === 'image') {
                const imageProps = row.querySelector('.image-props');
                if (imageProps) imageProps.style.display = 'block';
            }
        }

        function addManualTag() {
            const input = document.getElementById('manual_tag_input');
            if (!input) return;

            const tagName = input.value.trim();
            
            if (!tagName) {
                alert('Masukkan nama tag!');
                return;
            }
            
            const existingTags = Array.from(document.querySelectorAll('input[name="available_tags[]"]')).map(el => el.value);
            if (existingTags.includes(tagName)) {
                alert('Tag sudah ada!');
                return;
            }
            
            const tagsDisplay = document.getElementById('tags_display');
            const emptyMsg = tagsDisplay.querySelector('p.text-gray-400');
            if (emptyMsg) emptyMsg.remove();
            
            const span = document.createElement('span');
            span.className = 'inline-flex items-center bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm';
            span.innerHTML = `
                <code>${'${'}${tagName}${'}'}</code>
                <button type="button" class="remove-tag-btn ml-2 text-blue-600 hover:text-blue-900 font-bold" data-tag="${tagName}">×</button>
                <input type="hidden" name="available_tags[]" value="${tagName}">
            `;
            tagsDisplay.appendChild(span);

            // Wire up the new button immediately
            const newBtn = span.querySelector('.remove-tag-btn');
            if (newBtn) {
                newBtn.addEventListener('click', function() {
                    removeTag(this.dataset.tag);
                });
            }
            
            addMappingSection(tagName);
            input.value = '';
        }

        function removeTag(tagName) {
            if (!confirm(`Hapus tag ${'${'}${tagName}${'}'} ?`)) return;
            
            const tagsDisplay = document.getElementById('tags_display');
            const tagSpans = tagsDisplay.querySelectorAll('span');
            tagSpans.forEach(span => {
                const hiddenInput = span.querySelector('input[type="hidden"]');
                if (hiddenInput && hiddenInput.value === tagName) span.remove();
            });
            
            const mappingItem = document.querySelector(`.tag-mapping-item[data-tag="${tagName}"]`);
            if (mappingItem) mappingItem.remove();
            
            const remainingTags = document.querySelectorAll('input[name="available_tags[]"]');
            if (remainingTags.length === 0) {
                tagsDisplay.innerHTML = '<p class="text-gray-400 text-sm italic">Tidak ada tags terdeteksi. Gunakan tombol "Re-Extract Tags" atau tambah manual.</p>';
                
                const mappingsContainer = document.getElementById('tag_mappings_container');
                const tableContainer = mappingsContainer.querySelector('.bg-gray-50');
                if (tableContainer) {
                    tableContainer.innerHTML = `
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                            <p class="font-medium">Tidak ada tags untuk dimapping</p>
                            <p class="text-sm mt-1">Tambahkan tags terlebih dahulu menggunakan "Re-Extract Tags" atau manual add.</p>
                        </div>
                    `;
                }
            }
        }

        function addMappingSection(tagName) {
            const mappingsContainer = document.getElementById('tag_mappings_container');
            let tbody = mappingsContainer.querySelector('tbody');
            
            if (!tbody) {
                const newContainer = document.createElement('div');
                newContainer.className = 'bg-gray-50 p-4 rounded-lg overflow-x-auto';
                newContainer.innerHTML = `
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-1/4">Tag</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-1/3">Maps ke Field Data</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider w-5/12">Tipe Tag</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200"></tbody>
                    </table>
                `;
                mappingsContainer.appendChild(newContainer);
                tbody = newContainer.querySelector('tbody');
            }
            
            let optionsHtml = '<option value="">-- Tidak dimapping --</option>';
            for (const [category, fields] of Object.entries(availableFields)) {
                optionsHtml += `<optgroup label="${category.charAt(0).toUpperCase() + category.slice(1)}">`;
                for (const [fieldKey, fieldLabel] of Object.entries(fields)) {
                    optionsHtml += `<option value="${fieldKey}">${fieldLabel}</option>`;
                }
                optionsHtml += '</optgroup>';
            }
            
            const tr = document.createElement('tr');
            tr.className = 'tag-mapping-item';
            tr.setAttribute('data-tag', tagName);
            tr.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap">
                    <code class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">${'${'}${tagName}${'}'}</code>
                </td>
                <td class="px-4 py-3">
                    <select name="tag_mappings[${tagName}]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                        ${optionsHtml}
                    </select>
                </td>
                <td class="px-4 py-3">
                    <select name="tag_types[${tagName}]" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm tag-type-select" data-tag="${tagName}">
                        <option value="standard">Standard (Text)</option>
                        <option value="hyperlink">Hyperlink</option>
                        <option value="image">Image</option>
                    </select>
                    <div class="mt-2 tag-properties hyperlink-props" style="display: none">
                        <input type="text" name="tag_properties[${tagName}][hyperlink_url]" placeholder="URL (e.g., https://example.com)" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs">
                    </div>
                    <div class="mt-2 space-y-2 tag-properties image-props" style="display: none">
                        <input type="text" name="tag_properties[${tagName}][image_url]" placeholder="Image URL" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="number" name="tag_properties[${tagName}][image_width]" placeholder="Width" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs">
                            <input type="number" name="tag_properties[${tagName}][image_height]" placeholder="Height" class="w-full px-3 py-2 border border-gray-300 rounded-md text-xs">
                        </div>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);

            // Wire up new select
            const newSelect = tr.querySelector('.tag-type-select');
            if (newSelect) {
                newSelect.addEventListener('change', function() {
                    handleTagTypeChange(this);
                });
            }
        }

        function insertEmailTag(target) {
            const select = document.getElementById('email_tag_picker');
            if (!select) return;
            const tag = select.value;
            if (!tag) return;
            const placeholder = '${' + tag + '}';
            const fieldId = target === 'subject' ? 'email_subject_template' : 'email_body_template';
            const field = document.getElementById(fieldId);
            if (!field) return;
            insertAtCursor(field, placeholder);
            field.focus();
        }

        function insertAtCursor(element, text) {
            const start = element.selectionStart ?? element.value.length;
            const end = element.selectionEnd ?? element.value.length;
            const value = element.value;
            element.value = value.slice(0, start) + text + value.slice(end);
            const caret = start + text.length;
            element.selectionStart = caret;
            element.selectionEnd = caret;
        }

        function initDocumentEdit() {
            // Check manual input primarily
            const manualInput = document.getElementById('manual_tag_input');
            if (manualInput && manualInput.dataset.initialized === 'true') {
                 console.log("Document Edit already initialized on this node. Skipping.");
                 return;
            }
            
            
            console.log("Initializing Document Edit...");

            const addTagBtn = document.getElementById('add_tag_btn');
            
            // 1. Manual Tag
            if (addTagBtn) {
                // Remove existing listeners (clone node trick or assume this function runs only once per DOM load)
                // With page replacement, nodes are new, so just add.
                addTagBtn.addEventListener('click', addManualTag);
            }

            if (manualInput) {
                manualInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        addManualTag();
                    }
                });
            }

            // 2. Tag Types
            document.querySelectorAll('.tag-type-select').forEach(select => {
                select.addEventListener('change', function() {
                    handleTagTypeChange(this);
                });
                
                // Trigger initial state
                const tagName = select.getAttribute('data-tag');
                if (tagName) handleTagTypeChange(select);
            });

            // 3. Email Tag Buttons
            document.querySelectorAll('.email-tag-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    insertEmailTag(this.dataset.target);
                });
            });

            // 4. Remove Tag Buttons
            document.querySelectorAll('.remove-tag-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    removeTag(this.dataset.tag);
                });
            });

            if (manualInput) manualInput.dataset.initialized = 'true';
            console.log("Document Edit Initialized Successfully.");
        }

        // Initialization Logic
        if (document.readyState !== 'loading') {
            initDocumentEdit();
        } else {
            document.addEventListener('DOMContentLoaded', initDocumentEdit);
        }
        
        // Removed window.addEventListener('page-loaded') to prevent leaks.
        // AJAX injection runs this script immediately after content update, so initDocumentEdit() above suffices.

    } catch (e) {
        console.error('CRITICAL ERROR in Document Edit Script:', e);
    }
})();
</script>
@endsection
