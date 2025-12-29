@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Edit Profile</h1>

        <form method="POST" enctype="multipart/form-data" action="{{
            (Auth::guard('admin')->check()) ? route('admin.profile.update') :
            ((Auth::guard('dosen')->check()) ? route('dosen.profile.update') :
            ((Auth::guard('mahasiswa')->check()) ? route('mahasiswa.profile.update') : '#'))
        }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input
                        type="text"
                        name="nama"
                        id="nama"
                        value="{{ old('nama', $user->nama) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('nama') border-red-500 @enderror"
                        required
                    />
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        value="{{ old('email', $user->email) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('email') border-red-500 @enderror"
                        required
                    />
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
                        value="{{ old('wa', $user->wa) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('wa') border-red-500 @enderror"
                    />
                    @error('wa')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="hp" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input
                        type="text"
                        name="hp"
                        id="hp"
                        value="{{ old('hp', $user->hp) }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('hp') border-red-500 @enderror"
                    />
                    @error('hp')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-6">
                <label for="foto" class="block text-sm font-medium text-gray-700 mb-1">Foto Profil (maks 500KB)</label>
                <div class="flex items-center gap-4 mt-1">
                    @if($user->foto ?? false)
                        <img src="{{ asset('uploads/' . $user->foto) }}" alt="Current Photo" class="w-14 h-14 rounded-xl object-cover border">
                    @else
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center bg-gray-100 text-gray-400 border">
                            <i class="fas fa-user text-lg"></i>
                        </div>
                    @endif
                    <input
                        type="file"
                        name="foto"
                        id="foto"
                        accept="image/*"
                        class="text-sm text-gray-700"
                    />
                </div>
                <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF, WEBP. Maksimal 500KB.</p>
                @error('foto')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <hr class="my-8 border-gray-200">

            <h2 class="text-xl font-semibold text-gray-800 mb-2">Ubah Password</h2>
            <p class="text-sm text-gray-500 mb-4">Kosongkan kolom di bawah jika tidak ingin mengubah password.</p>

            <div class="space-y-6">
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <input
                        type="password"
                        name="current_password"
                        id="current_password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('current_password') border-red-500 @enderror"
                    />
                    @error('current_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input
                        type="password"
                        name="new_password"
                        id="new_password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md @error('new_password') border-red-500 @enderror"
                    />
                    @error('new_password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        name="new_password_confirmation"
                        id="new_password_confirmation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md"
                    />
                </div>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <a href="{{
                    (Auth::guard('admin')->check()) ? route('admin.dashboard') :
                    ((Auth::guard('dosen')->check()) ? route('dosen.dashboard') :
                    ((Auth::guard('mahasiswa')->check()) ? route('mahasiswa.dashboard') : '/'))
                }}" class="btn-pill btn-pill-secondary text-center w-full sm:w-auto">
                    Cancel
                </a>
                <button type="submit" class="btn-pill btn-pill-primary w-full sm:w-auto">
                    Update Profil
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
