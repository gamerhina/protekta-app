@extends('layouts.app')

@section('title', 'Import Data Mahasiswa')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Import Data Mahasiswa</h1>

        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h3 class="font-medium text-blue-800 mb-2">Petunjuk Import:</h3>
            <ul class="list-disc pl-5 space-y-1 text-blue-700">
                <li>Format file yang didukung: Excel (.xlsx, .xls) atau CSV</li>
                <li>Kolom yang wajib diisi: <strong>Nama</strong>, <strong>NPM</strong>, dan <strong>Email</strong></li>
                <li>Kolom opsional: <strong>HP</strong>, <strong>WA</strong>, dan <strong>Password</strong></li>
                <li>Gunakan format kolom sesuai contoh di bawah</li>
                <li>Data mahasiswa dengan NPM yang sudah ada akan diperbarui</li>
                <li>Jika kolom Password kosong, maka password default akan menjadi NPM</li>
            </ul>
        </div>

        <div class="mb-6">
            <a href="{{ route('admin.mahasiswa.sample.download') }}" class="btn-gradient inline-flex items-center gap-2" data-no-ajax>
                <i class="fas fa-download"></i> Download Contoh File
            </a>
        </div>

        <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-6">
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-1">Upload File Excel</label>
                    <input
                        type="file"
                        name="file"
                        id="file"
                        accept=".xlsx,.xls,.csv"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('file') border-red-500 @enderror"
                        required
                    >
                    @error('file')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn-pill btn-pill-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Import Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection