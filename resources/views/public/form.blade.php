<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $form->title }} — Heaven Scent</title>

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
        
        <!-- Header -->
        <header class="text-center mb-10">
            <h1 class="font-serif text-3xl md:text-4xl font-semibold text-charcoal mb-3">{{ $form->title }}</h1>
            @if($form->description)
                <p class="text-slate-500 text-sm max-w-lg mx-auto">{{ $form->description }}</p>
            @endif
        </header>

        <!-- Form Submission Error Alert -->
        @if($errors->any())
            <div class="mb-6 p-4 bg-rose-50 text-rose-800 border border-rose-100 rounded-xl text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Form Content -->
        <div class="bg-white rounded-3xl p-6 sm:p-10 shadow-sm border border-slate-100/50">
            <form action="{{ route('public.submit', $form->slug) }}" method="POST" id="main-form" @submit="onSubmit($event)">
                @csrf
                <!-- Duration tracking & Honeypot -->
                <input type="hidden" name="_duration" x-model="duration">
                <input type="text" name="_hp" value="" tabindex="-1" autocomplete="off" class="opacity-0 absolute -left-[9999px] top-0 h-0 w-0" aria-hidden="true">

                <!-- Dynamic fields rendered page by page -->
                <div class="space-y-8">
                    @foreach($form->fields as $field)
                        @if($field->type === 'section')
                            <div class="border-t border-slate-100 pt-8 first:border-0 first:pt-0">
                                <h3 class="font-serif text-xl font-bold text-charcoal">{{ $field->label }}</h3>
                                @if($field->description)
                                    <p class="text-slate-400 text-xs mt-1">{{ $field->description }}</p>
                                @endif
                            </div>
                        @elseif($field->type === 'statement')
                            <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm text-slate-600 leading-relaxed">
                                {{ $field->label }}
                            </div>
                        @else
                            <!-- Input Questions -->
                            <div class="space-y-2">
                                <label class="block text-sm font-semibold text-charcoal">
                                    {{ $field->label }}
                                    @if($field->is_required)
                                        <span class="text-rose-500 font-bold">*</span>
                                    @endif
                                </label>
                                @if($field->description)
                                    <p class="text-xs text-slate-400 mb-2">{{ $field->description }}</p>
                                @endif

                                <!-- Render inputs dynamically based on field types -->
                                @if($field->type === 'short_text')
                                    <input type="text" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent">
                                @elseif($field->type === 'long_text')
                                    <textarea name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent"></textarea>
                                @elseif($field->type === 'number')
                                    <input type="number" name="{{ $field->field_key }}" x-model.number="answers.{{ $field->field_key }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent">
                                @elseif($field->type === 'email')
                                    <input type="email" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent">
                                @elseif($field->type === 'phone')
                                    <input type="tel" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent" placeholder="Contoh: 08123456789">
                                @elseif($field->type === 'radio')
                                    <div class="space-y-2">
                                        @foreach($field->options as $opt)
                                            <label class="flex items-center gap-3 px-4 py-2.5 border border-slate-100 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                                                <input type="radio" name="{{ $field->field_key }}" value="{{ $opt->value }}" x-model="answers.{{ $field->field_key }}" class="text-gold focus:ring-gold border-slate-200">
                                                <span class="text-sm font-medium text-slate-700">{{ $opt->label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @elseif($field->type === 'dropdown')
                                    <select name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-gold focus:border-transparent">
                                        <option value="">Pilih Opsi</option>
                                        @foreach($field->options as $opt)
                                            <option value="{{ $opt->value }}">{{ $opt->label }}</option>
                                        @endforeach
                                    </select>
                                @elseif($field->type === 'checkbox')
                                    <div class="space-y-2">
                                        <!-- Store checkbox as array in Alpine -->
                                        @foreach($field->options as $opt)
                                            <label class="flex items-center gap-3 px-4 py-2.5 border border-slate-100 rounded-xl cursor-pointer hover:bg-slate-50 transition">
                                                <input type="checkbox" name="{{ $field->field_key }}[]" value="{{ $opt->value }}" x-model="answers.{{ $field->field_key }}" class="text-gold focus:ring-gold rounded border-slate-200">
                                                <span class="text-sm font-medium text-slate-700">{{ $opt->label }}</span>
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
                                                <label class="flex items-center justify-center h-10 w-10 border border-slate-200 rounded-full cursor-pointer hover:border-gold [&:has(input:checked)]:bg-gold [&:has(input:checked)]:text-white [&:has(input:checked)]:border-gold">
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
                                    <div class="flex gap-1.5 py-2">
                                        @for($i = 1; $i <= $stars; $i++)
                                            <button type="button" @click="answers.{{ $field->field_key }} = {{ $i }}" class="text-slate-200 hover:scale-110 transition" :class="{'text-amber-400': answers.{{ $field->field_key }} >= {{ $i }}}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 fill-current" viewBox="0 0 24 24"><path d="M12 .587l3.668 7.431 8.2 1.192-5.934 5.787 1.4 8.168L12 18.896l-7.334 3.857 1.4-8.168L.132 9.21l8.2-1.192L12 .587z"/></svg>
                                            </button>
                                        @endfor
                                        <!-- Hidden input to submit the actual rating -->
                                        <input type="hidden" name="{{ $field->field_key }}" x-model="answers.{{ $field->field_key }}">
                                    </div>
                                @elseif($field->type === 'file')
                                    <!-- File Upload with Progress Bar -->
                                    <div class="p-4 border border-dashed border-slate-200 rounded-2xl text-center" x-data="fileUploadHandler('{{ $field->id }}', '{{ $field->field_key }}')">
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
                        @endif
                    @endforeach
                </div>

                <div class="mt-10 border-t border-slate-100 pt-6 flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-charcoal text-white rounded-xl text-sm font-semibold hover:bg-black transition shadow-sm">
                        Kirim Jawaban
                    </button>
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
