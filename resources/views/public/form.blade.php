<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->title }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

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
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
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
            color: #2B2B2B;
        }
    </style>
</head>
<body class="font-sans antialiased min-h-screen py-12 px-4 sm:px-6">

    <div class="max-w-2xl mx-auto" x-data="publicFormApp()" x-init="initForm()">
        
        <!-- Form Submission Error Alert -->
        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 text-rose-800 border border-rose-100 rounded-xl text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Header / Logo -->
        <header class="text-center mb-8">
            <div class="flex justify-center mb-6">
                <!-- SVG Logo representing Heaven Scent -->
                <svg width="64" height="64" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M25 25V75M25 50H45M45 25V75" stroke="#111" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M55 75V55C55 55 65 50 75 55V75M75 55C75 55 65 60 55 55M55 25V45C55 45 65 40 75 45V25M75 45C75 45 65 50 55 45" stroke="#111" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    <text x="50" y="95" font-family="'Inter', sans-serif" font-size="12" font-weight="bold" fill="#111" text-anchor="middle" letter-spacing="0.1em">HEAVEN SCENT</text>
                </svg>
            </div>
            
            @if($form->settings['show_title'] ?? true)
                <h1 class="text-2xl md:text-3xl font-bold text-charcoal mb-4">{{ $form->title }}</h1>
            @endif
            
            @if(($form->settings['show_description'] ?? true) && $form->description)
                <p class="text-slate-500 text-sm max-w-xl mx-auto whitespace-pre-line">{{ $form->description }}</p>
            @endif
        </header>

        <!-- Form Content -->
        <div class="bg-white rounded-[2rem] p-6 sm:p-10 shadow-sm border border-slate-100/50">
            <form action="{{ route('public.submit', $form->slug) }}" method="POST" id="main-form" @submit="onSubmit($event)">
                @csrf
                <!-- Duration tracking & Honeypot -->
                <input type="hidden" name="_duration" x-model="duration">
                <input type="text" name="_hp" value="" tabindex="-1" autocomplete="off" class="opacity-0 absolute -left-[9999px] top-0 h-0 w-0" aria-hidden="true">

                <!-- Dynamic fields rendered page by page -->
                <div class="space-y-6">
                    @foreach($form->fields as $field)
                        @php
                            $hasLogic = !empty($field->logic) && is_array($field->logic);
                        @endphp
                        
                        <div @if($hasLogic) x-show="evaluateLogic({{ json_encode($field->logic) }})" x-transition x-cloak @endif>
                            @if($field->type === 'section')
                                <div class="border-t border-slate-100 pt-8 mt-4 first:border-0 first:pt-0 first:mt-0">
                                    <h3 class="text-xl font-bold text-charcoal">{{ $field->label }}</h3>
                                    @if($field->description)
                                        <p class="text-slate-400 text-xs mt-1">{{ $field->description }}</p>
                                    @endif
                                </div>
                            @elseif($field->type === 'statement')
                                <div class="p-6 bg-rose-50 border border-rose-100 rounded-2xl text-center">
                                    <p class="text-sm font-medium text-rose-900 mb-4">{{ $field->label }}</p>
                                    @if(isset($field->config['button_url']))
                                        <a href="{{ $field->config['button_url'] }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-sm w-full md:w-auto">
                                            {{ $field->config['button_text'] ?? 'Click Here' }}
                                            <!-- WhatsApp Icon -->
                                            <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.571-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                                        </a>
                                    @endif
                                </div>
                            @else
                                <!-- Input Questions -->
                                <div class="grid grid-cols-1 md:grid-cols-[1fr_1.5fr] gap-4 md:gap-8 items-start py-2">
                                    <div>
                                        <label class="block text-sm font-semibold text-charcoal pt-2 md:pt-3">
                                            {{ $field->label }}
                                            @if($field->is_required)
                                                <span class="text-rose-500 font-bold">*</span>
                                            @endif
                                        </label>
                                        @if($field->description)
                                            <p class="text-xs text-slate-400 mt-1">{{ $field->description }}</p>
                                        @endif
                                    </div>

                                    <div class="w-full">
                                        <!-- Render inputs dynamically based on field types -->
                                        @if($field->type === 'short_text')
                                            <input type="text" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent transition shadow-sm">
                                        @elseif($field->type === 'long_text')
                                            <textarea name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" rows="4" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent transition shadow-sm" placeholder="Tulis di sini.."></textarea>
                                        @elseif($field->type === 'number')
                                            <input type="number" name="{{ $field->field_key }}" x-model.number="answers.{{ $field->field_key }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent transition shadow-sm">
                                        @elseif($field->type === 'email')
                                            <input type="email" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent transition shadow-sm">
                                        @elseif($field->type === 'phone')
                                            <input type="tel" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent transition shadow-sm" placeholder="Contoh: 08123456789">
                                        @elseif($field->type === 'radio')
                                            <div class="space-y-2">
                                                @foreach($field->options as $opt)
                                                    <label class="flex items-center gap-3 px-4 py-3 border border-slate-100 rounded-xl cursor-pointer hover:bg-slate-50 transition shadow-sm">
                                                        <input type="radio" name="{{ $field->field_key }}" value="{{ $opt->value }}" x-model="answers.{{ $field->field_key }}" class="text-gold focus:ring-gold border-slate-200">
                                                        <span class="text-sm font-medium text-slate-700 flex-1">{{ $opt->label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($field->type === 'dropdown')
                                            <select name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent transition shadow-sm appearance-none">
                                                <option value="">Pilih opsi...</option>
                                                @foreach($field->options as $opt)
                                                    <option value="{{ $opt->value }}">{{ $opt->label }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($field->type === 'checkbox')
                                            <div class="space-y-2">
                                                <!-- Store checkbox as array in Alpine -->
                                                @foreach($field->options as $opt)
                                                    <label class="flex items-center justify-between gap-3 px-4 py-3 border border-slate-100 rounded-xl cursor-pointer hover:bg-slate-50 transition shadow-sm">
                                                        <span class="text-sm font-medium text-slate-700">{{ $opt->label }}</span>
                                                        <input type="checkbox" name="{{ $field->field_key }}[]" value="{{ $opt->value }}" x-model="answers.{{ $field->field_key }}" class="text-pink-400 focus:ring-pink-400 rounded border-slate-200 w-5 h-5">
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($field->type === 'scale')
                                            @php
                                                $min = $field->config['scale_min'] ?? 1;
                                                $max = $field->config['scale_max'] ?? 5;
                                            @endphp
                                            <div class="flex items-center justify-between gap-2 py-4 px-2">
                                                <span class="text-xs text-slate-400 font-semibold">{{ $field->config['label_left'] ?? 'Sangat Buruk' }}</span>
                                                <div class="flex gap-2">
                                                    @for($i = $min; $i <= $max; $i++)
                                                        <label class="flex items-center justify-center h-10 w-10 border border-slate-200 rounded-full cursor-pointer hover:border-pink-400 [&:has(input:checked)]:bg-pink-400 [&:has(input:checked)]:text-white [&:has(input:checked)]:border-pink-400 shadow-sm transition">
                                                            <input type="radio" name="{{ $field->field_key }}" value="{{ $i }}" x-model="answers.{{ $field->field_key }}" class="sr-only">
                                                            <span class="text-sm font-bold">{{ $i }}</span>
                                                        </label>
                                                    @endfor
                                                </div>
                                                <span class="text-xs text-slate-400 font-semibold">{{ $field->config['label_right'] ?? 'Sangat Baik' }}</span>
                                            </div>
                                        @elseif($field->type === 'rating')
                                            @php
                                                $stars = $field->config['stars'] ?? 5;
                                            @endphp
                                            <div class="flex gap-2 py-2">
                                                @for($i = 1; $i <= $stars; $i++)
                                                    <button type="button" @click="answers.{{ $field->field_key }} = {{ $i }}" class="text-slate-200 hover:scale-110 transition drop-shadow-sm" :class="{'text-pink-400': answers.{{ $field->field_key }} >= {{ $i }}}">
                                                        <!-- Rounded star icon -->
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192L12 .587z"/></svg>
                                                    </button>
                                                @endfor
                                                <!-- Hidden input to submit the actual rating -->
                                                <input type="hidden" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}">
                                            </div>
                                        @elseif($field->type === 'file')
                                            <!-- File Upload with Progress Bar -->
                                            <div class="p-4 border border-dashed border-slate-200 rounded-2xl text-center shadow-sm" x-data="fileUploadHandler('{{ $field->id }}', '{{ $field->field_key }}')">
                                                <input type="file" @change="onFileSelect($event)" class="hidden" :id="'file-input-{{ $field->id }}'">
                                                
                                                <div x-show="!uploadedFileId && !uploading">
                                                    <button type="button" @click="document.getElementById('file-input-{{ $field->id }}').click()" class="px-4 py-2 border border-slate-200 text-slate-700 rounded-xl hover:bg-slate-50 text-xs font-semibold">
                                                        Pilih & Upload File
                                                    </button>
                                                    <p class="text-slate-400 text-3xs mt-1">Ukuran maksimal file 5 MB.</p>
                                                </div>

                                                <div x-show="uploading" class="space-y-2">
                                                    <span class="text-xs text-slate-500 font-semibold block">Mengunggah file...</span>
                                                    <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                        <div class="bg-gold h-full" :style="'width: ' + progress + '%'"></div>
                                                    </div>
                                                </div>

                                                <div x-show="uploadedFileId" class="flex items-center justify-between bg-emerald-50 border border-emerald-100 p-2 rounded-xl text-emerald-800 text-xs font-semibold">
                                                    <span x-text="uploadedFileName"></span>
                                                    <button type="button" @click="clearFile()" class="text-rose-500 font-bold hover:underline">Hapus</button>
                                                </div>

                                                <!-- Final output field_id payload -->
                                                <input type="hidden" name="{{ $field->field_key }}" x-model="uploadedFileId">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="mt-10 flex flex-col gap-4">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-8 py-3.5 bg-[#1C1C1C] text-white rounded-xl text-sm font-semibold hover:bg-black transition shadow-md">
                        Submit
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>

                    <a href="https://wa.me/1234567890" target="_blank" class="w-full flex items-center justify-center gap-2 px-8 py-3.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 transition shadow-sm">
                        Customer Care Heaven Scent
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.571-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                    </a>
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

                    // Setup checkboxes arrays in answers snapshot
                    @foreach($form->fields as $field)
                        @if($field->type === 'checkbox')
                            if (!this.answers.{{ $field->field_key }}) {
                                this.answers.{{ $field->field_key }} = [];
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
