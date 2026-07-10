<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->title }}</title>

    <!-- Google Fonts: Geist & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Vanilla) -->
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
                        sans: ['Geist', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    <!-- Notyf -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>

    <style>
        body {
            background-color: #FAF7F0;
            background-image: url('{{ asset('BG.png') }}');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #2B2B2B;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen py-12 px-4 sm:px-6">

    <div class="max-w-[650px] mx-auto" x-data="publicFormApp()" x-init="initForm()">
        
        <!-- Form Submission Error Alert -->
        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 text-rose-800 border border-rose-100 rounded-[6px] text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form Content Card Container -->
        <div class="bg-white border-[#e8e8e8] border-[0.5px] border-solid flex flex-col gap-[18px] items-stretch overflow-hidden px-[18px] py-[12px] md:px-[36px] md:py-[20px] relative rounded-[14px] shadow-[0px_0px_0px_0.5px_rgba(64,37,0,0.15)] w-full">
            <!-- Inset shadow overlay -->
            <div class="absolute inset-0 pointer-events-none rounded-[inherit] shadow-[inset_0px_1px_3px_0px_rgba(179,179,179,0.2)]" aria-hidden="true"></div>
            
            <!-- Header / Logo -->
            <header class="text-center flex flex-col gap-[18px] items-center mb-4 relative z-10">
                <div class="h-[60px] relative flex justify-center">
                    <img src="{{ asset('Logo HS - black 1.png') }}" alt="Heaven Scent Logo" class="h-[60px] w-auto object-contain">
                </div>
                
                <div class="flex flex-col gap-[6px] items-center w-full">
                    @if($form->settings['show_title'] ?? true)
                        <h1 class="text-[20px] font-semibold text-black tracking-[-0.4px] leading-[1.4]">{{ $form->title }}</h1>
                    @endif
                    
                    @if(($form->settings['show_description'] ?? true) && $form->description)
                        <p class="text-[#666] text-[14px] leading-[1.4] text-center max-w-[613.5px] whitespace-pre-line">{{ $form->description }}</p>
                    @endif
                </div>
            </header>

            <form action="{{ route('public.submit', $form->slug) }}" method="POST" id="main-form" @submit="onSubmit($event)" class="relative z-10 flex flex-col gap-[18px]">
                @csrf
                <!-- Duration tracking & Honeypot -->
                <input type="hidden" name="_duration" x-model="duration">
                <input type="text" name="_hp" value="" tabindex="-1" autocomplete="off" class="opacity-0 absolute -left-[9999px] top-0 h-0 w-0" aria-hidden="true">

                <!-- Dynamic fields rendered page by page -->
                <div class="flex flex-col gap-[16.5px]">
                    @foreach($form->fields as $field)
                        @php
                            $hasLogic = !empty($field->logic) && is_array($field->logic);
                        @endphp
                        
                        <div @if($hasLogic) x-show="evaluateLogic({{ json_encode($field->logic) }})" x-transition x-cloak @endif>
                            @if($field->type === 'section')
                                <div class="border-t border-[#e8e8e8] pt-6 mt-4 first:border-0 first:pt-0 first:mt-0">
                                    <h3 class="text-[18px] font-medium text-black tracking-[-0.36px] leading-[1.4]">{{ $field->label }}</h3>
                                    @if($field->description)
                                        <p class="text-[#666] text-xs mt-1">{{ $field->description }}</p>
                                    @endif
                                </div>
                            @elseif($field->type === 'statement')
                                <div class="bg-[#fef8f6] border-[#e8e8e8] border-[0.75px] border-solid flex flex-col gap-[7.5px] items-start px-[18px] py-[15px] rounded-[6px] w-full">
                                    <div class="flex gap-[7.5px] items-center">
                                        <p class="font-sans font-normal leading-[1.4] text-[14px] text-black">
                                            {{ $field->label }}
                                        </p>
                                        <!-- Exclamation Triangle Icon SVG from Figma -->
                                        <svg class="w-[16.5px] h-[16.5px] shrink-0" viewBox="0 0 16.5 16.5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M15.2625 12.123L9.62607 2.33453C9.48523 2.09472 9.28415 1.89588 9.04278 1.75772C8.8014 1.61956 8.52812 1.54688 8.25 1.54688C7.97188 1.54687 7.69859 1.61956 7.45722 1.75772C7.21585 1.89588 7.01477 2.09472 6.87392 2.33453L1.2375 12.123C1.10198 12.355 1.03056 12.6188 1.03056 12.8874C1.03056 13.1561 1.10198 13.4199 1.2375 13.6519C1.37654 13.8931 1.57728 14.093 1.8191 14.2311C2.06092 14.3692 2.33513 14.4404 2.61357 14.4375H13.8864C14.1647 14.4402 14.4386 14.3689 14.6802 14.2308C14.9217 14.0927 15.1223 13.8929 15.2612 13.6519C15.3969 13.42 15.4686 13.1563 15.4688 12.8876C15.469 12.619 15.3978 12.3551 15.2625 12.123ZM7.73437 6.70317C7.73437 6.56642 7.7887 6.43526 7.8854 6.33857C7.9821 6.24187 8.11325 6.18754 8.25 6.18754C8.38675 6.18754 8.5179 6.24187 8.6146 6.33857C8.7113 6.43526 8.76562 6.56642 8.76562 6.70317V9.28129C8.76562 9.41804 8.7113 9.5492 8.6146 9.64589C8.5179 9.74259 8.38675 9.79692 8.25 9.79692C8.11325 9.79692 7.9821 9.74259 7.8854 9.64589C7.7887 9.5492 7.73437 9.41804 7.73437 9.28129V6.70317ZM8.25 12.375C8.09703 12.375 7.94749 12.3297 7.8203 12.2447C7.69311 12.1597 7.59398 12.0389 7.53544 11.8976C7.4769 11.7563 7.46158 11.6007 7.49142 11.4507C7.52127 11.3007 7.59493 11.1629 7.7031 11.0547C7.81126 10.9465 7.94908 10.8729 8.09911 10.843C8.24914 10.8132 8.40465 10.8285 8.54598 10.887C8.68731 10.9456 8.8081 11.0447 8.89309 11.1719C8.97807 11.2991 9.02344 11.4486 9.02344 11.6016C9.02344 11.8067 8.94195 12.0035 8.7969 12.1485C8.65185 12.2936 8.45513 12.375 8.25 12.375Z" fill="#F59E0B"/>
                                        </svg>
                                    </div>
                                    @if(isset($field->config['button_url']))
                                        <a href="{{ $field->config['button_url'] }}" target="_blank" class="border-[#e8e8e8] border-[0.75px] border-solid flex gap-[9px] items-center justify-center px-[13.5px] py-[10.5px] relative rounded-[6px] w-full bg-gradient-to-b from-white to-[#f7f7f7] hover:brightness-95 transition shadow-[inset_0px_1.5px_1.5px_0px_rgba(255,255,255,0.05),inset_0px_1.5px_1.5px_0px_rgba(255,255,255,0.08)]">
                                            <span class="font-sans font-medium leading-[15px] text-[14px] text-black text-center tracking-[-0.154px]">
                                                {{ $field->config['button_text'] ?? 'Click Here' }}
                                            </span>
                                            <!-- WhatsApp Icon -->
                                            <svg class="w-[16.5px] h-[16.5px] shrink-0" viewBox="0 0 16.5 16.5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M9.83426 9.36052L11.3167 10.1004C11.2464 10.4516 11.0565 10.7674 10.7793 10.9942C10.5022 11.2209 10.155 11.3444 9.79688 11.3437C8.56652 11.3424 7.38696 10.853 6.51697 9.98303C5.64697 9.11304 5.15761 7.93347 5.15625 6.70312C5.15615 6.34548 5.27998 5.99886 5.50666 5.72224C5.73334 5.44561 6.04887 5.25608 6.39955 5.1859L7.13947 6.66832L6.50977 7.60547C6.46271 7.67605 6.4338 7.75716 6.42559 7.84159C6.41738 7.92603 6.43013 8.01118 6.46271 8.08951C6.83163 8.96632 7.52917 9.66385 8.40598 10.0328C8.48454 10.0668 8.57034 10.0807 8.65563 10.0733C8.74093 10.0659 8.82303 10.0373 8.89453 9.99023L9.83426 9.36052ZM14.9531 8.25C14.9534 9.40727 14.654 10.5449 14.0841 11.5522C13.5143 12.5594 12.6934 13.402 11.7013 13.9978C10.7092 14.5937 9.57972 14.9225 8.42282 14.9524C7.26593 14.9822 6.12102 14.712 5.09953 14.1681L2.9049 14.8996C2.7232 14.9602 2.52821 14.969 2.34179 14.925C2.15537 14.881 1.98489 14.786 1.84945 14.6505C1.71401 14.5151 1.61897 14.3446 1.57497 14.1582C1.53098 13.9718 1.53978 13.7768 1.60037 13.5951L2.33191 11.4005C1.85377 10.5015 1.58671 9.5054 1.551 8.48781C1.51529 7.47021 1.71188 6.45786 2.12584 5.52758C2.5398 4.59731 3.16025 3.77356 3.9401 3.11888C4.71994 2.46419 5.63869 1.99577 6.6266 1.74917C7.61451 1.50258 8.64561 1.48428 9.64165 1.69567C10.6377 1.90707 11.5725 2.3426 12.375 2.9692C13.1776 3.59581 13.8269 4.39702 14.2736 5.31203C14.7203 6.22703 14.9527 7.23178 14.9531 8.25ZM12.375 9.79687C12.3751 9.70108 12.3485 9.60716 12.2981 9.52565C12.2478 9.44414 12.1758 9.37825 12.0901 9.33539L10.0276 8.30414C9.94643 8.26368 9.85601 8.24536 9.76548 8.25101C9.67495 8.25667 9.58751 8.28611 9.51199 8.33636L8.56518 8.968C8.13054 8.72907 7.77286 8.37139 7.53393 7.93675L8.16557 6.98994C8.21582 6.91442 8.24526 6.82698 8.25092 6.73645C8.25657 6.64592 8.23825 6.5555 8.19779 6.47431L7.16654 4.41181C7.1238 4.32549 7.05773 4.25287 6.97582 4.20218C6.89392 4.15148 6.79945 4.12475 6.70312 4.125C6.01936 4.125 5.36361 4.39662 4.88012 4.88011C4.39662 5.3636 4.125 6.01936 4.125 6.70312C4.12671 8.20687 4.72482 9.64854 5.78814 10.7119C6.85145 11.7752 8.29312 12.3733 9.79688 12.375C10.1354 12.375 10.4707 12.3083 10.7835 12.1787C11.0963 12.0492 11.3805 11.8593 11.6199 11.6199C11.8593 11.3805 12.0492 11.0963 12.1788 10.7835C12.3083 10.4707 12.375 10.1354 12.375 9.79687Z" fill="#66D173"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <!-- Input Questions Row -->
                                <div class="flex flex-col gap-3 py-2 border-b border-[#e8e8e8]/30 last:border-0 pb-4">
                                    <div class="flex flex-col gap-[7.5px] w-full">
                                        <div class="w-full">
                                            <label class="block text-[18px] font-medium text-black tracking-[-0.36px] leading-[1.4]">
                                                {{ $field->label }}
                                                <span class="text-rose-500 font-bold">*</span>
                                            </label>
                                            @if($field->description)
                                                <p class="text-xs text-[#666] mt-1">{{ $field->description }}</p>
                                            @endif
                                        </div>

                                        <div class="w-full">
                                            <!-- Render inputs dynamically based on field types -->
                                            @if($field->type === 'short_text')
                                                <input type="text" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-[13.5px] py-[9px] bg-white border border-[#e8e8e8] rounded-[6px] text-[14px] text-black placeholder-[#999] tracking-[-0.154px] focus:outline-none focus:ring-1 focus:ring-black/10 transition shadow-sm">
                                            @elseif($field->type === 'long_text')
                                                <textarea name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-[13.5px] py-[10.5px] pb-[75px] bg-white border border-[#e8e8e8] rounded-[6px] text-[14px] text-black placeholder-[#999] tracking-[-0.154px] focus:outline-none focus:ring-1 focus:ring-black/10 transition shadow-sm resize-none" placeholder="Tulis di sini..."></textarea>
                                            @elseif($field->type === 'number')
                                                <input type="number" name="{{ $field->field_key }}" x-model.number="answers.{{ $field->field_key }}" class="w-full px-[13.5px] py-[9px] bg-white border border-[#e8e8e8] rounded-[6px] text-[14px] text-black placeholder-[#999] tracking-[-0.154px] focus:outline-none focus:ring-1 focus:ring-black/10 transition shadow-sm">
                                            @elseif($field->type === 'email')
                                                <input type="email" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-[13.5px] py-[9px] bg-white border border-[#e8e8e8] rounded-[6px] text-[14px] text-black placeholder-[#999] tracking-[-0.154px] focus:outline-none focus:ring-1 focus:ring-black/10 transition shadow-sm">
                                            @elseif($field->type === 'phone')
                                                <input type="tel" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-[13.5px] py-[9px] bg-white border border-[#e8e8e8] rounded-[6px] text-[14px] text-black placeholder-[#999] tracking-[-0.154px] focus:outline-none focus:ring-1 focus:ring-black/10 transition shadow-sm" placeholder="Contoh: 08123456789">
                                            @elseif($field->type === 'radio')
                                                <div class="space-y-[6px]">
                                                    @foreach($field->options as $opt)
                                                        <label class="flex items-center justify-between px-[13.5px] py-[9px] bg-white border border-[#e8e8e8] border-[0.75px] rounded-[6px] cursor-pointer hover:bg-slate-50 transition w-full relative overflow-hidden">
                                                            <!-- Ellipse shadow overlay -->
                                                            <div class="-translate-x-1/2 absolute h-[28.5px] left-1/2 top-[21px] w-[429px] pointer-events-none opacity-[0.5] mix-blend-multiply">
                                                                <svg preserveAspectRatio="none" viewBox="0 0 447 46.5" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                                                    <ellipse cx="223.5" cy="23.25" rx="214.5" ry="14.25" fill="url(#paint0_radial_radio_{{ $field->id }}_{{ $loop->index }})"/>
                                                                    <defs>
                                                                        <radialGradient id="paint0_radial_radio_{{ $field->id }}_{{ $loop->index }}" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(223.5 23.25) rotate(90) scale(14.25 214.5)">
                                                                            <stop stop-color="#0A0A0A" stop-opacity="0.03"/>
                                                                            <stop offset="1" stop-color="#0A0A0A" stop-opacity="0.0"/>
                                                                        </radialGradient>
                                                                    </defs>
                                                                </svg>
                                                            </div>

                                                            <span class="text-[14px] text-[#424242] font-normal tracking-[-0.154px] z-10">{{ $opt->label }}</span>
                                                            <div class="flex items-center z-10">
                                                                <input type="radio" name="{{ $field->field_key }}" value="{{ $opt->value }}" x-model="answers.{{ $field->field_key }}" class="sr-only">
                                                                <div class="w-[15px] h-[15px] border-[#d5d7da] border-[0.75px] rounded-full flex items-center justify-center bg-white transition-all" :class="answers.{{ $field->field_key }} == '{{ $opt->value }}' ? 'border-black bg-black' : 'border-[#d5d7da]'">
                                                                    <div class="w-[5px] h-[5px] rounded-full bg-white" x-show="answers.{{ $field->field_key }} == '{{ $opt->value }}'"></div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($field->type === 'dropdown')
                                                <div x-data="{ open: false, search: '', options: {{ json_encode($field->options->map(fn($o) => ['value' => $o->value, 'label' => $o->label])) }} }" class="relative max-w-[262.5px] w-full" @click.outside="open = false">
                                                    <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-[13.5px] py-[9px] bg-white border border-[#e8e8e8] rounded-[6px] text-[14px] focus:outline-none focus:ring-1 focus:ring-black/10 transition shadow-sm text-left relative overflow-hidden">
                                                        <!-- Ellipse shadow overlay -->
                                                        <div class="-translate-x-1/2 absolute h-[28.5px] left-1/2 top-[14.75px] w-[429px] pointer-events-none opacity-70">
                                                            <svg preserveAspectRatio="none" viewBox="0 0 447 46.5" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                                                <ellipse cx="223.5" cy="23.25" rx="214.5" ry="14.25" fill="url(#paint0_radial_dropdown_{{ $field->id }})"/>
                                                                <defs>
                                                                    <radialGradient id="paint0_radial_dropdown_{{ $field->id }}" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(223.5 23.25) rotate(90) scale(14.25 214.5)">
                                                                        <stop stop-color="#0A0A0A" stop-opacity="0.03"/>
                                                                        <stop offset="1" stop-color="#0A0A0A" stop-opacity="0.0"/>
                                                                    </radialGradient>
                                                                </defs>
                                                            </svg>
                                                        </div>

                                                        <span class="relative z-10" x-text="options.find(o => o.value == answers.{{ $field->field_key }})?.label || '{{ $field->field_key === "store" ? "Pilih store kunjunganmu" : "Pilih opsi..." }}'" :class="answers.{{ $field->field_key }} ? 'text-black' : 'text-[#999]'"></span>
                                                        <svg class="w-[15px] h-[15px] text-[#999] relative z-10 shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12.5192 5.95672L7.83165 10.6442C7.78812 10.6878 7.73642 10.7224 7.67951 10.746C7.62261 10.7696 7.56161 10.7817 7.50001 10.7817C7.43841 10.7817 7.37741 10.7696 7.32051 10.746C7.2636 10.7224 7.2119 10.6878 7.16837 10.6442L2.48087 5.95672C2.39291 5.86876 2.3435 5.74947 2.3435 5.62508C2.3435 5.50069 2.39291 5.38139 2.48087 5.29344C2.56882 5.20548 2.68812 5.15607 2.81251 5.15607C2.9369 5.15607 3.05619 5.20548 3.14415 5.29344L7.50001 9.64988L11.8559 5.29344C11.8994 5.24989 11.9511 5.21534 12.008 5.19177C12.0649 5.1682 12.1259 5.15607 12.1875 5.15607C12.2491 5.15607 12.3101 5.1682 12.367 5.19177C12.4239 5.21534 12.4756 5.24989 12.5192 5.29344C12.5627 5.33699 12.5972 5.38869 12.6208 5.44559C12.6444 5.5025 12.6565 5.56349 12.6565 5.62508C12.6565 5.68667 12.6444 5.74766 12.6208 5.80456C12.5972 5.86146 12.5627 5.91317 12.5192 5.95672Z" fill="currentColor"/>
                                                        </svg>
                                                    </button>

                                                    <div x-show="open" x-transition class="absolute z-50 w-full mt-2 bg-white border border-[#e8e8e8] rounded-[6px] shadow-xl overflow-hidden">
                                                        <div class="p-2 border-b border-[#e8e8e8]">
                                                            <input type="text" x-model="search" placeholder="Cari opsi..." class="w-full px-3 py-2 bg-slate-50 border border-[#e8e8e8] rounded-[6px] text-sm focus:outline-none focus:ring-1 focus:ring-black/10 transition">
                                                        </div>
                                                        <ul class="max-h-60 overflow-y-auto p-2 space-y-1">
                                                            <template x-for="option in options.filter(o => o.label.toLowerCase().includes(search.toLowerCase()))" :key="option.value">
                                                                <li>
                                                                    <button type="button" @click="answers.{{ $field->field_key }} = option.value; open = false; search = ''" class="w-full text-left px-3 py-2 text-sm rounded-[6px] hover:bg-slate-50 transition" :class="answers.{{ $field->field_key }} == option.value ? 'bg-slate-50 text-black font-semibold' : 'text-slate-700'">
                                                                        <span x-text="option.label"></span>
                                                                    </button>
                                                                </li>
                                                            </template>
                                                            <li x-show="options.filter(o => o.label.toLowerCase().includes(search.toLowerCase())).length === 0" class="px-3 py-4 text-center text-sm text-slate-400">
                                                                Opsi tidak ditemukan
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <input type="hidden" name="{{ $field->field_key }}" :value="answers.{{ $field->field_key }}">
                                                </div>
                                            @elseif($field->type === 'checkbox')
                                                <div class="space-y-[6px]">
                                                    @foreach($field->options as $opt)
                                                        <label class="flex items-center justify-between px-[13.5px] py-[9px] bg-white border border-[#e8e8e8] border-[0.75px] rounded-[6px] cursor-pointer hover:bg-slate-50 transition w-full relative overflow-hidden">
                                                            <!-- Ellipse shadow overlay -->
                                                            <div class="-translate-x-1/2 absolute h-[28.5px] left-1/2 top-[21px] w-[429px] pointer-events-none opacity-[0.5] mix-blend-multiply">
                                                                <svg preserveAspectRatio="none" viewBox="0 0 447 46.5" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                                                                    <ellipse cx="223.5" cy="23.25" rx="214.5" ry="14.25" fill="url(#paint0_radial_checkbox_{{ $field->id }}_{{ $loop->index }})"/>
                                                                    <defs>
                                                                        <radialGradient id="paint0_radial_checkbox_{{ $field->id }}_{{ $loop->index }}" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(223.5 23.25) rotate(90) scale(14.25 214.5)">
                                                                            <stop stop-color="#0A0A0A" stop-opacity="0.03"/>
                                                                            <stop offset="1" stop-color="#0A0A0A" stop-opacity="0.0"/>
                                                                        </radialGradient>
                                                                    </defs>
                                                                </svg>
                                                            </div>

                                                            <span class="text-[14px] text-[#424242] font-normal tracking-[-0.154px] z-10">{{ $opt->label }}</span>
                                                            <div class="flex items-center z-10">
                                                                <input type="checkbox" name="{{ $field->field_key }}[]" value="{{ $opt->value }}" x-model="answers.{{ $field->field_key }}" class="sr-only">
                                                                <div class="w-[15px] h-[15px] border-[#d5d7da] border-[0.75px] rounded-[3px] flex items-center justify-center bg-white transition-all" :class="answers.{{ $field->field_key }} && answers.{{ $field->field_key }}.includes('{{ $opt->value }}') ? 'border-black bg-black' : 'border-[#d5d7da]'">
                                                                    <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="answers.{{ $field->field_key }} && answers.{{ $field->field_key }}.includes('{{ $opt->value }}')">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($field->type === 'scale')
                                                @php
                                                    $min = $field->config['scale_min'] ?? 1;
                                                    $max = $field->config['scale_max'] ?? 5;
                                                @endphp
                                                <div class="flex items-center justify-between gap-2 py-2 px-1 w-full">
                                                    <span class="text-[10px] text-[#999] font-medium tracking-tight">{{ $field->config['label_left'] ?? 'Sangat Buruk' }}</span>
                                                    <div class="flex gap-1.5">
                                                        @for($i = $min; $i <= $max; $i++)
                                                            <label class="flex items-center justify-center h-8 w-8 bg-white border border-[#e8e8e8] rounded-full cursor-pointer hover:border-black/50 [&:has(input:checked)]:bg-black [&:has(input:checked)]:text-white [&:has(input:checked)]:border-black shadow-sm transition">
                                                                <input type="radio" name="{{ $field->field_key }}" value="{{ $i }}" x-model="answers.{{ $field->field_key }}" class="sr-only">
                                                                <span class="text-xs font-semibold">{{ $i }}</span>
                                                            </label>
                                                        @endfor
                                                    </div>
                                                    <span class="text-[10px] text-[#999] font-medium tracking-tight">{{ $field->config['label_right'] ?? 'Sangat Baik' }}</span>
                                                </div>
                                            @elseif($field->type === 'rating')
                                                @php
                                                    $stars = $field->config['stars'] ?? 5;
                                                @endphp
                                                <div class="flex flex-col gap-2 w-full">
                                                    <div class="flex gap-[7.5px] py-1 justify-start">
                                                        @for($i = 1; $i <= $stars; $i++)
                                                            <button type="button" @click="answers.{{ $field->field_key }} = {{ $i }}" class="hover:scale-105 transition">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-colors duration-150" :class="answers.{{ $field->field_key }} >= {{ $i }} ? 'fill-[#FFC700] text-[#FFC700]' : 'fill-none stroke-[#D5D7DA]'" stroke-width="1.5" viewBox="0 0 24 24">
                                                                    <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                                                                </svg>
                                                            </button>
                                                        @endfor
                                                        <input type="hidden" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}">
                                                    </div>
                                                </div>
                                            @elseif($field->type === 'file')
                                                <!-- File Upload with Progress Bar -->
                                                <div class="p-4 border border-dashed border-[#e8e8e8] rounded-[6px] text-center bg-white shadow-sm" x-data="fileUploadHandler('{{ $field->id }}', '{{ $field->field_key }}')">
                                                    <input type="file" @change="onFileSelect($event)" class="hidden" :id="'file-input-{{ $field->id }}'">
                                                    
                                                    <div x-show="!uploadedFileId && !uploading">
                                                        <button type="button" @click="document.getElementById('file-input-{{ $field->id }}').click()" class="px-4 py-2 border border-[#e8e8e8] text-black bg-gradient-to-b from-white to-[#f7f7f7] rounded-[6px] hover:to-[#eee] text-xs font-semibold transition">
                                                            Pilih & Upload File
                                                        </button>
                                                        <p class="text-[#999] text-[10px] mt-1.5">Ukuran maksimal file 5 MB.</p>
                                                    </div>

                                                    <div x-show="uploading" class="space-y-2">
                                                        <span class="text-xs text-slate-500 font-semibold block">Mengunggah file...</span>
                                                        <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                            <div class="bg-black h-full" :style="'width: ' + progress + '%'"></div>
                                                        </div>
                                                    </div>

                                                    <div x-show="uploadedFileId" class="flex items-center justify-between bg-emerald-50 border border-emerald-100 p-2 rounded-[6px] text-emerald-800 text-xs font-semibold">
                                                        <span x-text="uploadedFileName" class="truncate max-w-[150px]"></span>
                                                        <button type="button" @click="clearFile()" class="text-rose-500 font-bold hover:underline ml-2">Hapus</button>
                                                    </div>

                                                    <!-- Final output field_id payload -->
                                                    <input type="hidden" name="{{ $field->field_key }}" x-model="uploadedFileId">
                                                </div>
                                            @endif
                                        </div>
                                    </div>


                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Form Submit and Customer Care Buttons -->
                <div class="mt-4 flex flex-col gap-3 relative z-10">
                    <button type="submit" class="w-full relative flex items-center justify-center gap-[9px] px-[13.5px] py-[10.5px] border-[0.75px] border-[rgba(255,255,255,0.3)] rounded-[6px] text-white font-medium text-[14px] tracking-[-0.154px] overflow-hidden shadow-[inset_0px_1.5px_1.5px_0px_rgba(255,255,255,0.05),inset_0px_1.5px_1.5px_0px_rgba(255,255,255,0.08)] hover:brightness-110 active:brightness-95 transition-all cursor-pointer">
                        <div aria-hidden class="absolute inset-0 pointer-events-none rounded-[6px]" style="background-image: url(&quot;data:image/svg+xml;utf8,<svg viewBox='0 0 533.25 38' xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none'><rect x='0' y='0' height='100%' width='100%' fill='url(%23grad)' opacity='0.20000000298023224'/><defs><radialGradient id='grad' gradientUnits='userSpaceOnUse' cx='0' cy='0' r='10' gradientTransform='matrix(-2.0833e-15 3.7604 -22.798 1.2525e-14 266.62 0.39583)'><stop stop-color='rgba(255,255,255,1)' offset='0'/><stop stop-color='rgba(255,255,255,0)' offset='1'/></radialGradient></defs></svg>&quot;), linear-gradient(180deg, #1e1e1e 0%, #0a0a0a 100%);"></div>
                        <span class="relative z-10">Submit</span>
                        <!-- Submit/Send Icon SVG from Figma -->
                        <svg class="w-[16.5px] h-[16.5px] relative z-10 shrink-0" viewBox="0 0 16.5 16.5" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M14.6515 1.8485C14.5218 1.71891 14.3599 1.62623 14.1825 1.58001C14.0051 1.53378 13.8186 1.53569 13.6421 1.58554H13.6325L1.26135 5.33928C1.06051 5.39717 0.881999 5.51471 0.749473 5.67634C0.616948 5.83796 0.536664 6.03605 0.51926 6.24433C0.501856 6.45262 0.548154 6.66128 0.652019 6.84266C0.755883 7.02404 0.912411 7.16958 1.10086 7.25999L6.57422 9.92577L9.23613 15.3959C9.31893 15.5726 9.45056 15.7219 9.61548 15.8262C9.7804 15.9304 9.97172 15.9853 10.1668 15.9844C10.1965 15.9844 10.2261 15.9831 10.2558 15.9805C10.4639 15.9636 10.6619 15.8835 10.8232 15.7509C10.9845 15.6183 11.1013 15.4396 11.1581 15.2386L14.9093 2.86751C14.9093 2.86429 14.9093 2.86106 14.9093 2.85784C14.9598 2.68186 14.9625 2.49561 14.9172 2.31822C14.8719 2.14084 14.786 1.97871 14.6515 1.8485ZM10.1726 14.9434L10.1694 14.9525V14.948L7.58742 9.64346L10.6812 6.54971C10.7738 6.45223 10.8247 6.32242 10.8229 6.18797C10.8212 6.05351 10.767 5.92505 10.672 5.82996C10.5769 5.73488 10.4484 5.68071 10.314 5.67898C10.1795 5.67726 10.0497 5.72813 9.95221 5.82075L6.85846 8.9145L1.55203 6.33251H1.54752H1.55654L13.9219 2.57811L10.1726 14.9434Z" fill="white"/>
                        </svg>
                    </button>

                    @if($form->settings['show_customer_care'] ?? true)
                        @php 
                            $ccUrl = $form->settings['customer_care_url'] ?? '#'; 
                            $ccText = $form->settings['customer_care_text'] ?? 'Customer Care Heaven Scent'; 
                        @endphp
                        <a href="{{ $ccUrl }}" target="_blank" class="w-full relative flex items-center justify-center gap-[9px] px-[13.5px] py-[10.5px] border-[0.75px] border-[rgba(232,232,232,0.9)] rounded-[6px] text-black font-medium text-[14px] tracking-[-0.154px] overflow-hidden shadow-[inset_0px_1.5px_1.5px_0px_rgba(255,255,255,0.05),inset_0px_1.5px_1.5px_0px_rgba(255,255,255,0.08)] hover:brightness-95 active:brightness-90 transition-all">
                            <div aria-hidden class="absolute inset-0 pointer-events-none rounded-[6px]" style="background-image: url(&quot;data:image/svg+xml;utf8,<svg viewBox='0 0 533.25 38' xmlns='http://www.w3.org/2000/svg' preserveAspectRatio='none'><rect x='0' y='0' height='100%' width='100%' fill='url(%23grad)' opacity='0.20000000298023224'/><defs><radialGradient id='grad' gradientUnits='userSpaceOnUse' cx='0' cy='0' r='10' gradientTransform='matrix(-2.0833e-15 3.7604 -22.798 1.2525e-14 266.62 0.39583)'><stop stop-color='rgba(242,242,242,1)' offset='0'/><stop stop-color='rgba(242,242,242,0)' offset='1'/></radialGradient></defs></svg>&quot;), linear-gradient(180deg, #ffffff 0%, #ededed 100%);"></div>
                            <span class="relative z-10">{{ $ccText }}</span>
                            <!-- WhatsApp Icon SVG from Figma -->
                            <svg class="w-[16.5px] h-[16.5px] relative z-10 shrink-0" viewBox="0 0 16.5 16.5" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.83426 9.36052L11.3167 10.1004C11.2464 10.4516 11.0565 10.7674 10.7793 10.9942C10.5022 11.2209 10.155 11.3444 9.79688 11.3437C8.56652 11.3424 7.38696 10.853 6.51697 9.98303C5.64697 9.11304 5.15761 7.93347 5.15625 6.70312C5.15615 6.34548 5.27998 5.99886 5.50666 5.72224C5.73334 5.44561 6.04887 5.25608 6.39955 5.1859L7.13947 6.66832L6.50977 7.60547C6.46271 7.67605 6.4338 7.75716 6.42559 7.84159C6.41738 7.92603 6.43013 8.01118 6.46271 8.08951C6.83163 8.96632 7.52917 9.66385 8.40598 10.0328C8.48454 10.0668 8.57034 10.0807 8.65563 10.0733C8.74093 10.0659 8.82303 10.0373 8.89453 9.99023L9.83426 9.36052ZM14.9531 8.25C14.9534 9.40727 14.654 10.5449 14.0841 11.5522C13.5143 12.5594 12.6934 13.402 11.7013 13.9978C10.7092 14.5937 9.57972 14.9225 8.42282 14.9524C7.26593 14.9822 6.12102 14.712 5.09953 14.1681L2.9049 14.8996C2.7232 14.9602 2.52821 14.969 2.34179 14.925C2.15537 14.881 1.98489 14.786 1.84945 14.6505C1.71401 14.5151 1.61897 14.3446 1.57497 14.1582C1.53098 13.9718 1.53978 13.7768 1.60037 13.5951L2.33191 11.4005C1.85377 10.5015 1.58671 9.5054 1.551 8.48781C1.51529 7.47021 1.71188 6.45786 2.12584 5.52758C2.5398 4.59731 3.16025 3.77356 3.9401 3.11888C4.71994 2.46419 5.63869 1.99577 6.6266 1.74917C7.61451 1.50258 8.64561 1.48428 9.64165 1.69567C10.6377 1.90707 11.5725 2.3426 12.3751 2.9692C13.1776 3.59581 13.8269 4.39702 14.2736 5.31203C14.7203 6.22703 14.9527 7.23178 14.9531 8.25ZM12.375 9.79687C12.3751 9.70108 12.3485 9.60716 12.2981 9.52565C12.2478 9.44414 12.1758 9.37825 12.0901 9.33539L10.0276 8.30414C9.94643 8.26368 9.85601 8.24536 9.76548 8.25101C9.67495 8.25667 9.58751 8.28611 9.51199 8.33636L8.56518 8.968C8.13054 8.72907 7.77286 8.37139 7.53393 7.93675L8.16557 6.98994C8.21582 6.91442 8.24526 6.82698 8.25092 6.73645C8.25657 6.64592 8.23825 6.5555 8.19779 6.47431L7.16654 4.41181C7.1238 4.32549 7.05773 4.25287 6.97582 4.20218C6.89392 4.15148 6.79945 4.12475 6.70312 4.125C6.01936 4.125 5.36361 4.39662 4.88012 4.88011C4.39662 5.3636 4.125 6.01936 4.125 6.70312C4.12671 8.20687 4.72482 9.64854 5.78814 10.7119C6.85145 11.7752 8.29312 12.3733 9.79688 12.375C10.1354 12.375 10.4707 12.3083 10.7835 12.1787C11.0963 12.0492 11.3805 11.8593 11.6199 11.6199C11.8593 11.3805 12.0492 11.0963 12.1788 10.7835C12.3083 10.4707 12.375 10.1354 12.375 9.79687Z" fill="#66D173"/>
                            </svg>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <script>
        function publicFormApp() {
            return {
                answers: {},
                duration: 0,
                timer: null,
                initForm() {
                    // Set up local storage autosave key
                    const storageKey = 'scentiment_draft_{{ $form->id }}';
                    const saved = localStorage.getItem(storageKey);
                    if (saved) {
                        this.answers = JSON.parse(saved);
                    }

                    // Setup checkboxes arrays and initialize other fields in answers snapshot
                    @foreach($form->fields as $field)
                        @if($field->type !== 'section' && $field->type !== 'statement')
                            if (this.answers.{{ $field->field_key }} === undefined) {
                                @if($field->type === 'checkbox')
                                    this.answers.{{ $field->field_key }} = [];
                                @else
                                    this.answers.{{ $field->field_key }} = null;
                                @endif
                            }
                        @endif
                    @endforeach

                    // Autosave watcher
                    this.$watch('answers', (value) => {
                        localStorage.setItem(storageKey, JSON.stringify(value));
                    }, { deep: true });

                    // Time duration counter
                    this.timer = setInterval(() => {
                        this.duration++;
                    }, 1000);
                },
                evaluateLogic(logic) {
                    if (!logic || !logic.condition) return true;
                    
                    const cond = logic.condition;
                    const answerValue = this.answers[cond.field];
                    
                    if (answerValue === undefined || answerValue === null || answerValue === '') return false;
                    
                    if (cond.operator === '<=') return Number(answerValue) <= Number(cond.value);
                    if (cond.operator === '>=') return Number(answerValue) >= Number(cond.value);
                    if (cond.operator === '<') return Number(answerValue) < Number(cond.value);
                    if (cond.operator === '>') return Number(answerValue) > Number(cond.value);
                    if (cond.operator === '==') return answerValue == cond.value;
                    if (cond.operator === '!=') return answerValue != cond.value;
                    
                    return true;
                },
                onSubmit(event) {
                    // Stop timer
                    clearInterval(this.timer);
                    // Clear local storage draft
                    localStorage.removeItem('scentiment_draft_{{ $form->id }}');
                }
            };
        }

        function fileUploadHandler(fieldId, fieldKey) {
            return {
                uploading: false,
                progress: 0,
                uploadedFileId: null,
                uploadedFileName: '',
                onFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('field_id', fieldId);
                    formData.append('_token', '{{ csrf_token() }}');

                    this.uploading = true;
                    this.progress = 10;

                    fetch('{{ route("public.upload", $form->slug) }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.progress = 100;
                            this.uploadedFileId = data.file_id;
                            this.uploadedFileName = data.original_name;
                            // Update parent answers
                            const app = Alpine.raw(document.querySelector('[x-data="publicFormApp()"]').__x.$data);
                            app.answers[fieldKey] = data.file_id;
                        }
                        this.uploading = false;
                    })
                    .catch(err => {
                        this.uploading = false;
                        const notyf = new Notyf({ position: {x:'center', y:'top'} });
                        notyf.error('Upload file gagal.');
                    });
                },
                clearFile() {
                    this.uploadedFileId = null;
                    this.uploadedFileName = '';
                    const app = Alpine.raw(document.querySelector('[x-data="publicFormApp()"]').__x.$data);
                    app.answers[fieldKey] = null;
                }
            };
        }
    </script>
</body>
</html>
