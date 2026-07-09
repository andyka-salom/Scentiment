<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terima Kasih — Heaven Scent</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        charcoal: '#2B2B2B',
                        gold: '#C6A961',
                        ivory: '#FAF7F0',
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #FAF7F0;
            color: #2B2B2B;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen flex items-center justify-center py-12 px-4">

    <div class="max-w-md w-full bg-white rounded-3xl p-8 sm:p-10 shadow-sm border border-slate-100/50 text-center">
        <!-- Success Check Icon -->
        <div class="inline-flex p-4 bg-emerald-50 text-emerald-600 rounded-full mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
        </div>

        <h1 class="font-serif text-2xl font-bold text-charcoal mb-4">Pengisian Berhasil</h1>
        
        <p class="text-slate-500 text-sm mb-8 leading-relaxed">
            {{ $form->settings['success_message'] ?? 'Terima kasih! Jawaban Anda telah kami terima.' }}
        </p>

        <!-- Assessment Mode Score Display (only if show_score settings is enabled) -->
        @if($form->is_assessment && ($form->settings['show_score'] ?? false) && $response->score !== null)
            <div class="bg-slate-50 border border-slate-100 rounded-2xl p-6 mb-8">
                <span class="text-xs font-semibold text-slate-400 block uppercase">Skor Hasil Penilaian</span>
                <span class="text-4xl font-extrabold text-charcoal mt-1 block">{{ $response->score }}</span>
                @if($response->grade)
                    <span class="text-xs font-bold text-emerald-700 mt-2 block px-2.5 py-1 bg-emerald-50 border border-emerald-100 rounded-full inline-block">
                        Grade: {{ $response->grade }}
                    </span>
                @endif
            </div>
        @endif

        <!-- Optional redirect trigger -->
        @if($form->settings['redirect_url'] ?? false)
            <div class="space-y-4">
                <span class="text-xs text-slate-400">Mengarahkan Anda secara otomatis...</span>
                <a href="{{ $form->settings['redirect_url'] }}" class="block w-full py-2.5 bg-charcoal text-white rounded-xl text-xs font-semibold hover:bg-black transition">
                    Lanjutkan ke Halaman Berikutnya
                </a>
                <script>
                    setTimeout(function() {
                        window.location.href = "{{ $form->settings['redirect_url'] }}";
                    }, 3000);
                </script>
            </div>
        @endif
    </div>

</body>
</html>
