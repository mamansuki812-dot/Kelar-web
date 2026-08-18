<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KELAR POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{font-family:'Inter',sans-serif}h1,h2,h3,.font-display{font-family:'Plus Jakarta Sans','Inter',sans-serif}</style>
</head>
<body class="bg-body-bg flex items-center justify-center min-h-screen">

    <div class="bg-surface p-8 rounded-2xl shadow-lg w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold font-display text-primary mb-2">KELAR<span class="text-primary font-extrabold">.</span></h1>
            <p class="text-muted">Sistem Point of Sales & Inventori</p>
        </div>

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-300 text-rose-800 px-4 py-3 rounded-lg mb-4 text-sm flex items-center space-x-2">
                <svg class="h-5 w-5 text-rose-700 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="username" class="block text-sm font-semibold text-neutral-dark mb-1.5">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                       class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                       placeholder="Masukkan username" required autofocus>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-semibold text-neutral-dark mb-1.5">Password</label>
                <input type="password" id="password" name="password"
                       class="w-full px-4 py-2.5 border border-border-soft rounded-xl focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none transition"
                       placeholder="Masukkan password" required>
            </div>

            <x-button type="submit" variant="primary" size="lg" class="w-full">
                Masuk
            </x-button>
        </form>

        <div class="text-center mt-6 text-sm text-muted">
            &copy; {{ date('Y') }} KELAR POS by Rikni Winur Alam
        </div>
    </div>

</body>
</html>
