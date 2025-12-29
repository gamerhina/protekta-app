@extends('layouts.app')

@section('title', 'Kelola Template Surat')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Kelola Template Surat</h1>
                <p class="text-sm text-gray-500">Jenis surat: <strong>{{ $suratJenis->nama }}</strong></p>
            </div>
            <a href="{{ route('admin.suratjenis.edit', $suratJenis) }}" class="btn-pill btn-pill-secondary">Kembali</a>
        </div>

        <form method="POST" action="{{ route('admin.surattemplate.update', [$suratJenis, $template]) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Template</label>
                        <input name="nama" value="{{ old('nama', $template->nama) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ganti File DOCX (opsional)</label>
                        <input type="file" name="new_file" class="w-full px-3 py-2 border border-gray-300 rounded-md" accept=".docx">
                        <p class="text-xs text-gray-500 mt-1">Jika diganti, daftar tag akan diekstrak ulang.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email Subject Template (opsional)</label>
                        <input name="email_subject_template" value="{{ old('email_subject_template', $template->email_subject_template) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Surat @{{surat_jenis_nama}} - @{{mahasiswa_nama}}">
                    </div>

                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label class="block text-sm font-medium text-gray-700">Email Body Template (opsional)</label>
                            <button type="button" onclick="document.getElementById('tag-list-email').classList.toggle('hidden')" class="text-xs text-blue-600 hover:underline">
                                <i class="fas fa-info-circle"></i> Lihat Daftar Tag
                            </button>
                        </div>
                        <textarea name="email_body_template" rows="6" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Gunakan @{{surat_no}}, @{{surat_tujuan}}, @{{mahasiswa_nama}}, ...">{{ old('email_body_template', $template->email_body_template) }}</textarea>
                        
                        <div id="tag-list-email" class="hidden mt-3 p-4 bg-blue-50 border border-blue-100 rounded-xl shadow-inner">
                            <div class="mb-4">
                                <h4 class="text-[10px] font-bold text-blue-800 uppercase tracking-widest mb-2 border-b border-blue-200 pb-1">Tag Standar</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-y-2 gap-x-4">
                                    @foreach([
                                        'surat_no', 'surat_tanggal', 'surat_hari', 'surat_tahun', 'surat_tujuan', 'surat_perihal', 'surat_isi', 'surat_jenis_nama',
                                        'dosen_nama', 'dosen_nip', 'dosen_email', 'mahasiswa_nama', 'mahasiswa_npm', 'mahasiswa_prodi', 'mahasiswa_email'
                                    ] as $t)
                                        <div class="text-[10px] font-mono text-blue-700 bg-white/50 px-1 rounded inline-block">
                                            &lcub;&lcub; {{ $t }} &rcub;&rcub;
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            @php
                                $customFields = collect($suratJenis->form_fields ?? [])
                                    ->pluck('key')
                                    ->filter(fn($k) => !in_array($k, ['no_surat', 'tanggal_surat', 'status', 'pemohon']))
                                    ->values();
                            @endphp

                            @if($customFields->isNotEmpty())
                                <div>
                                    <h4 class="text-[10px] font-bold text-blue-800 uppercase tracking-widest mb-2 border-b border-blue-200 pb-1">Tag Kustom (Form)</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-2 gap-x-4">
                                        @foreach($customFields as $k)
                                            <div class="text-[10px] font-mono text-indigo-700 bg-white/50 px-1 rounded inline-block">
                                                &lcub;&lcub; {{ $k }} &rcub;&rcub;
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <p class="mt-4 text-[10px] text-blue-600 italic leading-relaxed border-t border-blue-200 pt-2 text-center">
                                * Klik "Lihat Daftar Tag" lagi untuk menutup panel ini.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="aktif" value="1" id="aktif" {{ old('aktif', $template->aktif) ? 'checked' : '' }}>
                        <label for="aktif" class="text-sm text-gray-700">Aktif</label>
                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Mapping Tag</h2>
                    <p class="text-sm text-gray-500 mb-4">Pilih sumber data untuk setiap tag <span class="font-mono">${...}</span> yang ditemukan.</p>

                    <div class="space-y-3">
                        @php
                            $tags = is_array($template->available_tags) ? $template->available_tags : [];
                            $mappings = is_array($template->tag_mappings) ? $template->tag_mappings : [];
                        @endphp

                        @forelse($tags as $tag)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 border border-slate-200 rounded-xl p-3 bg-slate-50">
                                <div class="md:col-span-1">
                                    <div class="text-xs text-slate-500">Tag</div>
                                    <div class="font-mono text-sm text-slate-800">${{ $tag }}</div>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="text-xs text-slate-500 mb-1">Sumber</div>
                                    <select name="tag_mappings[{{ $tag }}]" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="">(Kosong)</option>
                                        @foreach($availableFields as $group => $fields)
                                            <optgroup label="{{ $group }}">
                                                @foreach($fields as $value => $label)
                                                    <option value="{{ $value }}" {{ (($mappings[$tag] ?? '') === $value) ? 'selected' : '' }}>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-500">Tidak ada tag ditemukan pada template.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button class="btn-pill btn-pill-primary" type="submit">Simpan Template</button>
            </div>
        </form>

        <div class="mt-10 pt-8 border-t">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Preview / Kirim (berdasarkan data Surat)</h2>
            <p class="text-sm text-gray-500 mb-4">Pilih salah satu permohonan surat untuk download docx atau kirim email.</p>

            <div class="overflow-x-auto border border-gray-100 rounded-2xl shadow-sm">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Pemohon</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Mahasiswa</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold tracking-[0.2em] text-gray-500 uppercase bg-gray-50 w-56">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($surats as $s)
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $s->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $s->pemohonDosen->nama ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $s->mahasiswa->nama ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $s->status }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <a class="text-blue-600 hover:underline" href="{{ route('admin.surattemplate.download', [$suratJenis, $template, $s]) }}" download data-no-ajax target="_blank">Download DOCX</a>
                                        <form method="POST" action="{{ route('admin.surattemplate.send', [$suratJenis, $template, $s]) }}" class="flex items-center gap-2">
                                            @csrf
                                            <input name="to" value="{{ $s->penerima_email ?? ($s->mahasiswa->email ?? '') }}" class="px-3 py-2 border border-gray-300 rounded-md text-sm" placeholder="email@contoh.com" required>
                                            <button class="btn-pill btn-pill-secondary text-sm" type="submit">Kirim</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">Belum ada permohonan surat untuk jenis ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
