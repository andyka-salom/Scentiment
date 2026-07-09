<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Scentiment') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-charcoal antialiased bg-ivory selection:bg-gold selection:text-white">
        <div class="min-h-screen flex">
            <!-- Left Side (Branding) -->
            <div class="hidden lg:flex lg:w-1/2 bg-charcoal text-white p-12 flex-col justify-between relative overflow-hidden">
                <!-- Decorative Elements -->
                <div class="absolute -top-32 -left-32 w-96 h-96 bg-gold rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-gold rounded-full mix-blend-multiply filter blur-3xl opacity-20"></div>
                
                <div class="relative z-10">
                    <a href="/" class="inline-flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gold flex items-center justify-center shrink-0 shadow-lg shadow-gold/20">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-charcoal" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <span class="font-outfit font-bold text-2xl tracking-tight text-white">Scentiment</span>
                    </a>
                </div>

                <div class="relative z-10 max-w-md">
                    <h1 class="font-serif text-5xl leading-tight mb-6 text-white font-medium">Build beautiful forms with ease.</h1>
                    <p class="text-white/70 text-lg leading-relaxed font-light">Create, share, and analyze dynamic forms and assessments without writing a single line of code. Designed for modern teams.</p>
                </div>

                <div class="relative z-10">
                    <p class="text-white/40 text-sm">&copy; {{ date('Y') }} Heaven Scent. All rights reserved.</p>
                </div>
            </div>

            <!-- Right Side (Form) -->
            <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative bg-ivory">
                <!-- Mobile Logo -->
                <div class="absolute top-8 left-8 lg:hidden">
                    <a href="/" class="inline-flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-charcoal flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                        </div>
                        <span class="font-outfit font-bold text-xl text-charcoal">Scentiment</span>
                    </a>
                </div>

                <div class="w-full max-w-md bg-white p-8 sm:p-10 rounded-3xl shadow-xl shadow-charcoal/5 border border-white/50 relative z-10">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
