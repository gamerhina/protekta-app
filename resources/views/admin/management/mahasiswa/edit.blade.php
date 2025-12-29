@extends('layouts.app')

@section('title', 'Edit Mahasiswa')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Edit Mahasiswa</h1>

        <form action="{{ route('admin.mahasiswa.update', $mahasiswa->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama', $mahasiswa->nama) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror"
                        required
                    >
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="npm" class="block text-sm font-medium text-gray-700 mb-1">NPM</label>
                    <input
                        type="text"
                        name="npm"
                        id="npm"
                        value="{{ old('npm', $mahasiswa->npm) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('npm') border-red-500 @enderror"
                        required
                    >
                    @error('npm')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $mahasiswa->email) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('email') border-red-500 @enderror"
                        required
                    >
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="wa" class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                    <input
                        type="text"
                        name="wa"
                        id="wa"
                        value="{{ old('wa', $mahasiswa->wa) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('wa') border-red-500 @enderror"
                    >
                    @error('wa')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="hp" class="block text-sm font-medium text-gray-700 mb-1">HP</label>
                    <input
                        type="text"
                        name="hp"
                        id="hp"
                        value="{{ old('hp', $mahasiswa->hp) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('hp') border-red-500 @enderror"
                    >
                    @error('hp')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('password') border-red-500 @enderror"
                        placeholder="Kosongkan jika tidak ingin mengganti password"
                    >
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">Foto Profil</label>
                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('foto') border-red-500 @enderror"
                        accept="image/*"
                    >
                    @error('foto')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, maksimal 2MB</p>
                    
                    @if($mahasiswa->foto)
                        <div class="mt-2">
                            <p class="text-sm text-gray-600">Foto saat ini:</p>
                            <img src="{{ asset('uploads/' . $mahasiswa->foto) }}" alt="Current Photo" class="mt-1 h-16 w-16 object-cover rounded">
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end space-x-4 pt-6">
                    <a href="{{ route('admin.mahasiswa.index') }}" class="btn-pill btn-pill-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn-pill btn-pill-primary">
                        Update Mahasiswa
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection