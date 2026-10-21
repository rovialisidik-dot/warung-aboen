<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ app_name() }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-850 to-emerald-950 flex items-center justify-center p-4 sm:p-6 font-sans">

    <div class="w-full max-w-md">
        <!-- Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-400 text-white shadow-xl shadow-emerald-500/25 mb-4 transform hover:scale-105 transition-transform">
                <i class="fa-solid fa-store text-3xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">{{ app_name() }}</h1>
            <p class="text-emerald-400 font-medium text-sm mt-1">{{ app_setting('store_tagline', 'Aplikasi Pembukuan & Kasir Warung Digital') }}</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 p-8 sm:p-10">
            <h2 class="text-xl font-bold text-slate-800 text-center mb-2">Masuk ke Sistem</h2>
            <p class="text-slate-500 text-xs text-center mb-6">Silakan masukkan username dan kata sandi Anda</p>

            @if(session('error'))
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                <span>{{ session('error') }}</span>
            </div>
            @endif

            @if($errors->any())
            <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center space-x-2">
                <i class="fa-solid fa-circle-exclamation text-rose-500 text-sm"></i>
                <span>{{ $errors->first() }}</span>
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" id="loginForm" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="username">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-user"></i>
                        </div>
                        <input type="text" name="username" id="username" value="{{ old('username', 'admin') }}" required autofocus
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition-all text-slate-800 placeholder-slate-400 bg-slate-50/50"
                               placeholder="Masukkan username Anda">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="password">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <input type="password" name="password" id="password" value="admin123" required
                               class="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition-all text-slate-800 placeholder-slate-400 bg-slate-50/50"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600">
                            <i class="fa-regular fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center space-x-2 text-slate-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 border-slate-300">
                        <span>Ingat saya</span>
                    </label>
                    <span class="text-slate-400">Hak Akses: Pemilik / Kasir</span>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold text-sm shadow-lg shadow-emerald-600/30 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center space-x-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk ke Dashboard</span>
                </button>
            </form>

            <!-- Quick Demo Accounts -->
            <div class="mt-8 pt-6 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider text-center mb-3">Akses Cepat (Klik untuk Coba)</p>
                <div class="grid grid-cols-2 gap-2.5">
                    <button type="button" onclick="fillAccount('admin', 'admin123')" class="flex items-center justify-center space-x-2 p-2.5 rounded-xl border border-emerald-200 bg-emerald-50/80 hover:bg-emerald-100/80 text-emerald-800 text-xs font-semibold transition-all">
                        <i class="fa-solid fa-crown text-amber-500"></i>
                        <span>Pemilik (Admin)</span>
                    </button>
                    <button type="button" onclick="fillAccount('kasir', 'kasir123')" class="flex items-center justify-center space-x-2 p-2.5 rounded-xl border border-sky-200 bg-sky-50/80 hover:bg-sky-100/80 text-sky-800 text-xs font-semibold transition-all">
                        <i class="fa-solid fa-user-tag text-sky-500"></i>
                        <span>Kasir (Siti)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Notice -->
        <p class="text-center text-slate-500 text-xs mt-6">
            {{ app_name() }} &copy; {{ date('Y') }} &bull; Sistem Pembukuan & Kasir Digital Warung
        </p>
    </div>

    <script>
        function togglePassword() {
            const passInput = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (passInput.type === 'password') {
                passInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function fillAccount(username, password) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = password;
            document.getElementById('loginForm').submit();
        }
    </script>
</body>
</html>
