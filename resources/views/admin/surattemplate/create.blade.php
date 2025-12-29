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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File DOCX</label>
                    <input type="file" name="file" class="w-full px-3 py-2 border border-gray-300 rounded-md" accept=".docx" required>
                    <p class="text-xs text-gray-500 mt-1">Gunakan placeholder tag seperti <span class="font-mono">${surat_no}</span>, <span class="font-mono">${surat_tujuan}</span>, <span class="font-mono">${mahasiswa_nama}</span>, dll.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('keterangan') }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex items-center justify-between">
                <a href="{{ route('admin.suratjenis.edit', $suratJenis) }}" class="btn-pill btn-pill-secondary">Batal</a>
                <button class="btn-pill btn-pill-primary" type="submit">Upload</button>
            </div>
        </form>
    </div>
</div>
@endsection
