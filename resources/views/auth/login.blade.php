<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Protekta Apps</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @php
        $logo = optional($branding)->logo_url;
        $bgImage = optional($branding)->login_background_url;
        $primary = optional($branding)->primary_color ?? '#1d4ed8';
        $loginBackground = $bgImage
            ? "linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.75)), url('$bgImage')"
            : 'linear-gradient(135deg, rgba(15, 23, 42, 0.85), rgba(15, 23, 42, 0.75))';
    @endphp
    <style>
        body {
            min-height: 100vh;
            background-image: {!! $loginBackground !!};
            background-size: cover;
            background-position: center;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .btn-primary {
            background-color: {{ $primary }};
        }
    </style>
</head>
<body class="flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-4xl rounded-3xl bg-white/90 shadow-2xl backdrop-blur">
        <div class="grid gap-0 md:grid-cols-2">
            <div class="flex flex-col items-center justify-center gap-4 px-8 py-10 text-center text-slate-700">
                @if($logo)
                    <img src="{{ $logo }}" alt="Logo" class="h-16 w-16 rounded-2xl object-cover">
                @else
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-900 text-2xl font-semibold text-white">PA</div>
                @endif
                <h1 class="text-2xl font-semibold text-slate-900">Selamat Datang di Protekta Apps</h1>
                <p class="text-sm text-slate-500">Silahkan masuk menggunakan kredensial anda untuk mengelola seminar dan data akademik.</p>
            </div>
            <div class="rounded-3xl bg-white p-8">
                <h2 class="text-xl font-semibold text-slate-900">Login</h2>
                <form method="POST" action="{{ route('login.post') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="login" class="block text-sm font-semibold text-slate-600">Username</label>
                        <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-slate-400 focus:outline-none">
                        @error('login')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-600">Password</label>
                        <input type="password" id="password" name="password" required class="mt-2 w-full rounded-2xl border border-slate-200 px-4 py-3 text-slate-700 focus:border-slate-400 focus:outline-none">
                        @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn-primary w-full rounded-2xl px-4 py-3 text-sm font-semibold text-white shadow-lg transition hover:opacity-90">Masuk</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginError = document.querySelector('p.text-red-500');
            if (loginError && loginError.textContent.includes('detik')) {
                let message = loginError.textContent;
                let match = message.match(/(\d+)/);
                
                if (match) {
                    let seconds = parseInt(match[1]);
                    const baseMessage = message.replace(seconds, '{seconds}');
                    
                    const interval = setInterval(() => {
                        seconds--;
                        if (seconds <= 0) {
                            clearInterval(interval);
                            loginError.textContent = "Silakan coba login kembali.";
                            loginError.classList.remove('text-red-500');
                            loginError.classList.add('text-green-600', 'font-bold');
                        } else {
                            loginError.textContent = baseMessage.replace('{seconds}', seconds);
                        }
                    }, 1000);
                }
            }
        });
    </script>
</body>
</html>
