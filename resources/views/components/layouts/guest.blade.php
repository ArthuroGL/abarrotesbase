<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ABARROTESBASE' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen w-full items-center justify-center bg-slate-100 p-4 font-sans text-slate-90 antialiased selection:bg-emerald-500 selection:text-white">

    <!-- Envoltorio estricto: forzado con inline-style por si Tailwind v4 omite el max-w -->
    <main style="max-width: 410px; width: 100%;" class="mx-auto">
        {{ $slot }}
    </main>

    <div id="app-toast-region" class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4" aria-live="polite"></div>
</body>
</html>
