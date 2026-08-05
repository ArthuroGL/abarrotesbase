<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'ABARROTESBASE' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full font-sans text-slate-900 antialiased">
    <div class="min-h-screen lg:flex">
        <x-layout.sidebar />

        <div class="min-w-0 flex-1">
            <x-layout.topbar />

            <main class="mx-auto w-full max-w-screen-2xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="app-toast-region" class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4" aria-live="polite"></div>
</body>
</html>
