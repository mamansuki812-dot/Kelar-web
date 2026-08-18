<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan | KELAR POS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{font-family:'Inter',sans-serif}h1,h2,h3,.font-display{font-family:'Plus Jakarta Sans','Inter',sans-serif}</style>
</head>
<body class="bg-body-bg min-h-screen flex items-center justify-center">
    <div class="text-center px-6">
        <div class="h-24 w-24 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-6">
            <svg class="h-12 w-12 text-amber-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
            </svg>
        </div>
        <h1 class="text-6xl font-bold font-display text-neutral-dark mb-2">404</h1>
        <h2 class="text-2xl font-semibold font-display text-neutral-dark mb-3">Halaman Tidak Ditemukan</h2>
        <p class="text-muted mb-8 max-w-md mx-auto">Halaman yang Anda cari tidak ada atau telah dipindahkan. Periksa kembali alamatnya, atau gunakan menu di aplikasi.</p>
        <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white font-semibold rounded-xl hover:bg-primary-dark transition-colors">
            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
