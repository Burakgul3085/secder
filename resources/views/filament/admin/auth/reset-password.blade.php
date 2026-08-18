<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yeni Şifre Belirle</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100">
    <div class="mx-auto flex min-h-screen w-full max-w-xl items-center px-4 py-10">
        <div class="w-full rounded-3xl border border-cyan-100 bg-white p-6 shadow-xl md:p-8">
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Yeni Şifre Belirle</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                En az 8 karakterlik yeni şifrenizi girin.
            </p>

            @if ($errors->any())
                <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.password.update') }}" method="post" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700">Yeni Şifre</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        minlength="8"
                        class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                    >
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700">Yeni Şifre (Tekrar)</label>
                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        minlength="8"
                        class="mt-1 w-full rounded-2xl border border-slate-300 px-4 py-3 text-slate-900 outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-100"
                    >
                </div>
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-500 px-4 py-3 text-sm font-semibold text-white transition hover:bg-cyan-600"
                >
                    Şifreyi Kaydet
                </button>
            </form>

            <a
                href="{{ route('filament.admin.auth.login') }}"
                class="mt-4 inline-flex w-full items-center justify-center rounded-2xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
            >
                Giriş Sayfasına Dön
            </a>
        </div>
    </div>
</body>
</html>
