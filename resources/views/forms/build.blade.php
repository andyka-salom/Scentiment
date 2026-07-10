<x-app-sidebar-layout title="Builder — {{ $form->title }}">
    <x-slot name="topbarActions">
        <a href="{{ $form->status === 'draft' ? route('forms.preview', $form) : route('public.form', $form->slug) }}" target="_blank"
           class="px-3 py-1.5 border border-slate-200 text-slate-700 rounded-lg hover:bg-slate-50 transition text-sm font-medium flex items-center gap-1.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
            {{ $form->status === 'draft' ? 'Preview Form' : 'Buka Form' }}
        </a>
    </x-slot>

    <x-form-context-tabs :form="$form" />

    <!-- Builder Workspace -->
    <div x-data="builderApp()" x-init="initBuilder()">
        <div class="grid grid-cols-12 gap-5 items-start">
                
                <!-- Left Panel: Fields Palette -->
                <div class="col-span-12 md:col-span-4 lg:col-span-3 bg-white rounded-2xl border border-slate-100 p-5 shadow-sm static md:sticky md:top-20 z-10">
                    <h3 class="font-outfit font-semibold text-sm text-slate-800 uppercase tracking-wider mb-4">Tipe Pertanyaan</h3>
                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="type in fieldTypes">
                            <button @click="addField(type.code)" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-lg border border-slate-100 hover:border-slate-200 hover:bg-slate-50 transition text-left text-sm font-medium text-slate-700">
                                <span class="text-slate-400" x-html="type.icon"></span>
                                <span x-text="type.label"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Center Panel: Form Canvas -->
                <div class="col-span-12 md:col-span-8 lg:col-span-9 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                    <div class="border-b border-slate-100 pb-4 mb-6">
                        <h3 class="font-outfit font-bold text-lg text-slate-800">Kanvas Form</h3>
                        <p class="text-slate-500 text-xs mt-1">Gunakan drag handle untuk mengatur ulang urutan pertanyaan. Klik pada pertanyaan untuk mengedit detailnya.</p>
                    </div>

                    <!-- Fields Canvas Container -->
                    <div id="fields-canvas" class="space-y-4 min-h-[300px]">
                        <template x-for="(field, index) in fields" :key="field.id">
                            <div :data-id="field.id" class="p-0 rounded-xl border bg-white shadow-sm flex flex-col transition relative group" :class="{'ring-2 ring-slate-900 border-transparent my-6': selectedField && selectedField.id === field.id, 'border-slate-100 hover:border-slate-200': !(selectedField && selectedField.id === field.id)}" @click="if(!selectedField || selectedField.id !== field.id) selectField(field)">
                                
                                <!-- Drag Handle (Top center for active, left side for inactive) -->
                                <div class="cursor-move text-slate-300 hover:text-slate-500 p-1 flex justify-center drag-handle" :class="{'w-full bg-slate-50 rounded-t-xl py-2 border-b border-slate-100': selectedField && selectedField.id === field.id, 'absolute left-2 top-4': !(selectedField && selectedField.id === field.id)}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" /></svg>
                                </div>

                                <div class="p-5 flex-1 w-full" :class="{'pl-10': !(selectedField && selectedField.id === field.id)}">
                                    
                                    <!-- Inactive State (Preview) -->
                                    <div x-show="!selectedField || selectedField.id !== field.id">
                                        <div class="flex justify-between items-start mb-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-600" x-text="getFieldTypeLabel(field.type)"></span>
                                                <span x-show="field.is_required" class="text-xs font-bold text-rose-500">* Required</span>
                                            </div>
                                            <button @click.stop="deleteField(field.id)" class="opacity-0 group-hover:opacity-100 p-1.5 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </div>
                                        <h4 class="font-semibold text-slate-800 text-lg mt-2" x-text="field.label || '(Pertanyaan Tanpa Label)'"></h4>
                                        <p class="text-slate-500 text-sm mt-1" x-show="field.description" x-text="field.description"></p>
                                        
                                        <!-- Preview Options -->
                                        <div x-show="['radio', 'checkbox', 'dropdown'].includes(field.type)" class="mt-4 space-y-2">
                                            <template x-for="opt in field.options">
                                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                                    <div class="w-4 h-4 border border-slate-300 flex-shrink-0" :class="{'rounded-full': field.type === 'radio', 'rounded-sm': field.type === 'checkbox'}"></div>
                                                    <span x-text="opt.label"></span>
                                                </div>
                                            </template>
                                        </div>
                                        
                                        <!-- Preview Custom Button -->
                                        <div x-show="field.type === 'button'" class="mt-4">
                                            <div class="inline-flex items-center gap-2 px-6 py-2 rounded-xl text-white bg-slate-800 text-sm font-semibold">
                                                <span x-text="field.config?.button_text || field.label || 'Klik Di Sini'"></span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Active State (Edit Mode) -->
                                    <div x-show="selectedField && selectedField.id === field.id">
                                        <div class="space-y-4">
                                            <div class="flex flex-col sm:flex-row gap-4">
                                                <div class="flex-1">
                                                    <input type="text" x-model="selectedField.label" @input="updateFieldDebounced(selectedField)" class="w-full px-4 py-3 bg-slate-50 border-transparent focus:bg-white border focus:border-slate-300 rounded-xl text-lg font-semibold text-slate-900 focus:ring-0 placeholder:font-normal transition-colors" placeholder="Pertanyaan">
                                                </div>
                                                <div class="w-full sm:w-1/3">
                                                    <div class="px-3 py-3 border border-slate-200 rounded-xl bg-slate-50 text-sm text-slate-600 font-medium flex items-center justify-between">
                                                        <span x-text="getFieldTypeLabel(selectedField.type)"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <input type="text" x-model="selectedField.description" @input="updateFieldDebounced(selectedField)" class="w-full px-4 py-2 bg-slate-50 border-transparent focus:bg-white border focus:border-slate-300 rounded-xl text-sm text-slate-600 focus:ring-0 transition-colors" placeholder="Deskripsi (Opsional)">
                                            </div>

                                            <!-- Button Config -->
                                            <div x-show="selectedField.type === 'button'" class="bg-slate-50 p-4 rounded-xl space-y-3 border border-slate-100 mt-2">
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Teks Tombol</label>
                                                    <input type="text" x-model="selectedField.config.button_text" @input="updateFieldDebounced(selectedField)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" placeholder="Contoh: Chat WhatsApp">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 mb-1">URL Tujuan</label>
                                                    <input type="url" x-model="selectedField.config.button_url" @input="updateFieldDebounced(selectedField)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" placeholder="Contoh: https://wa.me/628123456789">
                                                </div>
                                            </div>

                                            <!-- Statement Config (for statement / info text type) -->
                                            <div x-show="selectedField.type === 'statement'" class="bg-amber-50 p-4 rounded-xl space-y-3 border border-amber-100 mt-2">
                                                <p class="text-xs font-semibold text-amber-700">Konfigurasi Tombol WA (Opsional)</p>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Teks Tombol</label>
                                                    <input type="text" x-model="selectedField.config.button_text" @input="updateFieldDebounced(selectedField)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" placeholder="Contoh: Hubungi customer care via Whatsapp">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 mb-1">URL / Nomor WhatsApp</label>
                                                    <input type="text" x-model="selectedField.config.button_url" @input="updateFieldDebounced(selectedField)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" placeholder="Contoh: https://wa.me/628123456789">
                                                </div>
                                            </div>

                                            <!-- Choice Options -->
                                            <div x-show="['radio', 'checkbox', 'dropdown'].includes(selectedField.type)" class="mt-4">
                                                <div class="space-y-2">
                                                    <template x-for="(opt, oIdx) in selectedField.options" :key="oIdx">
                                                        <div class="flex items-center gap-2 group">
                                                            <div class="w-4 h-4 border border-slate-300 flex-shrink-0" :class="{'rounded-full': selectedField.type === 'radio', 'rounded-sm': selectedField.type === 'checkbox'}"></div>
                                                            <input type="text" x-model="opt.label" @input="updateFieldDebounced(selectedField)" class="flex-1 px-3 py-2 border border-slate-200 hover:border-slate-300 focus:border-slate-400 rounded-lg text-sm focus:ring-0 transition-colors" placeholder="Opsi">
                                                            <input type="number" x-model="opt.score" @input="updateFieldDebounced(selectedField)" class="w-20 px-2 py-2 border border-slate-200 rounded-lg text-sm focus:ring-0" placeholder="Skor" title="Skor opsional">
                                                            <button @click="removeOption(selectedField, oIdx)" class="text-slate-300 hover:text-rose-500 p-1.5 transition-colors">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                            </button>
                                                        </div>
                                                    </template>
                                                    <div class="flex items-center gap-2 mt-2">
                                                        <div class="w-4 h-4 flex-shrink-0"></div>
                                                        <button @click="addOption(selectedField)" class="text-sm text-slate-500 hover:text-slate-800 font-medium px-3 py-2">Tambah Opsi</button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Scale Specific Property Management -->
                                            <div x-show="selectedField.type === 'scale'" class="grid grid-cols-2 gap-4 mt-2">
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Skala Min</label>
                                                    <input type="number" x-model.number="selectedField.config.scale_min" @input="updateFieldDebounced(selectedField)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-semibold text-slate-600 mb-1">Skala Max</label>
                                                    <input type="number" x-model.number="selectedField.config.scale_max" @input="updateFieldDebounced(selectedField)" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                                                </div>
                                            </div>

                                            <!-- Footer Actions -->
                                            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                                                <div class="flex items-center gap-2">
                                                    <button @click="updateField(selectedField)" class="text-sm font-semibold text-white bg-slate-900 hover:bg-slate-800 px-5 py-2 rounded-xl transition-colors shadow-sm">Simpan</button>
                                                    <button @click="selectField(null)" class="text-sm font-medium text-slate-500 hover:text-slate-800 px-4 py-2 rounded-xl hover:bg-slate-50 transition-colors">Tutup</button>
                                                </div>
                                                
                                                <div class="flex items-center gap-4">
                                                    <button @click="deleteField(selectedField.id)" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Pertanyaan">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    </button>
                                                    
                                                    <div class="w-px h-6 bg-slate-200"></div>

                                                    <label class="flex items-center gap-2 cursor-pointer">
                                                        <span class="text-sm font-medium text-slate-600">Wajib Diisi</span>
                                                        <div class="relative inline-flex items-center">
                                                            <input type="checkbox" x-model="selectedField.is_required" @change="updateField(selectedField)" class="sr-only peer">
                                                            <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </template>

                        <!-- Empty Canvas State -->
                        <div x-show="fields.length === 0" class="text-center py-12 text-slate-400 border-2 border-dashed border-slate-100 rounded-2xl">
                            <p class="text-sm">Kanvas kosong. Pilih tipe pertanyaan di panel kiri untuk mulai menambahkan.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    <!-- AlpineJS logic for the Form Builder App -->
    <script>
        const builderNotyf = new Notyf({
            duration: 5000,
            position: { x: 'right', y: 'bottom' },
            dismissible: true
        });

        function builderApp() {
            return {
                fields: @json($form->fields),
                selectedField: null,
                debounceTimer: null,
                fieldTypes: [
                    { code: 'short_text', label: 'Teks Pendek', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>' },
                    { code: 'long_text', label: 'Teks Panjang', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" /></svg>' },
                    { code: 'number', label: 'Angka', icon: '<span class="text-xs font-bold font-mono">123</span>' },
                    { code: 'email', label: 'Email', icon: '<span class="text-xs font-bold font-mono">@</span>' },
                    { code: 'phone', label: 'Nomor HP', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>' },
                    { code: 'radio', label: 'Pilihan Tunggal (Radio)', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' },
                    { code: 'checkbox', label: 'Pilihan Ganda (Checkbox)', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>' },
                    { code: 'dropdown', label: 'Menu Tarik (Dropdown)', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>' },
                    { code: 'scale', label: 'Skala Linear', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 2v-6m3 10V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2z" /></svg>' },
                    { code: 'rating', label: 'Bintang (Rating)', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.907c.961 0 1.371 1.24.588 1.81l-3.97 2.883a1 1 0 00-.364 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.971-2.883a1 1 0 00-1.176 0l-3.97 2.883c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.364-1.118l-3.97-2.883c-.783-.57-.372-1.81.587-1.81h4.908a1 1 0 00.95-.69l1.519-4.674z" /></svg>' },
                    { code: 'file', label: 'Upload File', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" /></svg>' },
                    { code: 'section', label: 'Pemisah Halaman (Section)', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" /></svg>' },
                    { code: 'statement', label: 'Teks Informasi', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>' },
                    { code: 'button', label: 'Custom Button (Tautan)', icon: '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" /></svg>' }
                ],
                initBuilder() {
                    const el = document.getElementById('fields-canvas');
                    Sortable.create(el, {
                        handle: '.drag-handle',
                        animation: 150,
                        ghostClass: 'bg-slate-50',
                        onEnd: (evt) => {
                            const newOrder = Array.from(el.querySelectorAll('[data-id]'))
                                .map(item => parseInt(item.getAttribute('data-id')));
                            this.saveOrder(newOrder);
                        }
                    });
                },
                getFieldTypeLabel(code) {
                    const type = this.fieldTypes.find(t => t.code === code);
                    return type ? type.label : code;
                },
                selectField(field) {
                    this.selectedField = field;
                },
                addField(type) {
                    fetch('{{ route("fields.store", $form) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            label: 'Pertanyaan Baru',
                            type: type,
                            is_required: false,
                            config: type === 'scale' ? { scale_min: 1, scale_max: 5 } : {},
                            options: ['radio', 'checkbox', 'dropdown'].includes(type) ? [
                                { label: 'Opsi 1', value: 'opsi_1' },
                                { label: 'Opsi 2', value: 'opsi_2' }
                            ] : null
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            this.fields.push(data.field);
                            this.selectedField = data.field;
                            builderNotyf.success('Pertanyaan berhasil ditambahkan!');
                        }
                    });
                },
                updateField(field) {
                    fetch(`/app/forms/{{ $form->id }}/fields/${field.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            label: field.label,
                            description: field.description,
                            is_required: field.is_required,
                            config: field.config,
                            logic: field.logic,
                            options: field.options
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            // Update local record
                            const idx = this.fields.findIndex(f => f.id === field.id);
                            this.fields[idx] = data.field;
                            builderNotyf.success('Perubahan disimpan.');
                        }
                    });
                },
                updateFieldDebounced(field) {
                    clearTimeout(this.debounceTimer);
                    this.debounceTimer = setTimeout(() => {
                        this.updateField(field);
                    }, 800);
                },
                saveOrder(newOrder) {
                    fetch('{{ route("fields.reorder", $form) }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            order: newOrder
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            builderNotyf.success('Urutan pertanyaan disimpan!');
                        }
                    });
                },
                deleteField(id) {
                    Swal.fire({
                        title: 'Konfirmasi',
                        text: 'Apakah Anda yakin ingin menghapus pertanyaan ini?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        customClass: { popup: 'font-inter', title: 'font-outfit font-semibold' }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/app/forms/{{ $form->id }}/fields/${id}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if(data.success) {
                                    this.fields = this.fields.filter(f => f.id !== id);
                                    if (this.selectedField && this.selectedField.id === id) {
                                        this.selectedField = null;
                                    }
                                    builderNotyf.success('Pertanyaan berhasil dihapus!');
                                }
                            });
                        }
                    });
                },
                addOption(field) {
                    if (!field.options) field.options = [];
                    const len = field.options.length + 1;
                    field.options.push({ label: `Opsi ${len}`, value: `opsi_${len}`, score: null });
                    this.updateField(field);
                },
                removeOption(field, idx) {
                    field.options.splice(idx, 1);
                    this.updateField(field);
                }
            };
        }
    </script>
</x-app-sidebar-layout>
