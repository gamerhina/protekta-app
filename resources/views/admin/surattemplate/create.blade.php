@extends('layouts.app')

@section('title', 'Upload Template Surat')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-2">Upload Template Surat</h1>
        <p class="text-sm text-gray-500 mb-6">Jenis surat: <strong>{{ $suratJenis->nama }}</strong></p>

        <form method="POST" action="{{ route('admin.surattemplate.store', $suratJenis) }}" enctype="multipart/form-data">
            @csrf

            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Template</label>
                    <input name="nama" value="{{ old('nama', $suratJenis->nama) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:border-blue-200 transition-all group">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1 min-w-0">
                            <h3 class="text-sm font-bold text-gray-800 truncate">File Template</h3>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold mt-0.5">
                                WAJIB • DOCX
                            </p>
                        </div>
                        <span class="flex-shrink-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 rounded-full">BELUM ADA</span>
                    </div>
                    <div class="relative group/input">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-1">Unggah Berkas</label>
                        <input 
                            type="file" 
                            name="file" 
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-200 rounded-xl bg-white focus:outline-none focus:border-blue-300 transition-all" 
                            accept=".docx" 
                            required
                        >
                        <p class="text-[10px] text-gray-400 mt-2 italic px-1">Gunakan placeholder tag seperti <span class="font-mono text-gray-600">${surat_no}</span>, <span class="font-mono text-gray-600">${mahasiswa_nama}</span></p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.suratjenis.edit', $suratJenis) }}" class="btn-pill btn-pill-secondary">Batal</a>
                <button class="btn-pill btn-pill-primary" type="submit">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
