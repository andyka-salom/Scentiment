<x-app-sidebar-layout title="Preview Form: {{ $form->title }}">
    <x-form-context-tabs :form="$form" />

    <div class="max-w-3xl mx-auto mt-6">
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 mb-6 flex items-start gap-3">
            <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <div>
                <p class="text-sm font-semibold text-amber-800">Mode Preview</p>
                <p class="text-xs text-amber-700 mt-0.5">Ini adalah pratinjau tampilan publik form Anda. Respon yang dikirim melalui halaman ini tidak akan disimpan ke database.</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden mb-10 pointer-events-none">
            {{-- Header Form --}}
            <div class="px-8 py-10 border-b border-slate-100 bg-slate-50 text-center">
                <h1 class="text-3xl font-outfit font-bold text-slate-900 mb-3">{{ $form->title }}</h1>
                @if($form->description)
                    <p class="text-slate-600 text-sm whitespace-pre-wrap max-w-2xl mx-auto">{{ $form->description }}</p>
                @endif
            </div>

            {{-- Fields Form (Read-only render for preview) --}}
            <div class="px-8 py-8 space-y-8">
                @forelse($form->fields as $index => $field)
                    <div class="form-field-preview">
                        <label class="block text-base font-semibold text-slate-800 mb-2">
                            {{ $index + 1 }}. {{ $field->label }}
                            @if($field->is_required)
                                <span class="text-rose-500 ml-1">*</span>
                            @endif
                        </label>
                        @if($field->description)
                            <p class="text-sm text-slate-500 mb-3">{{ $field->description }}</p>
                        @endif

                        {{-- Render dummy inputs based on type --}}
                        <div class="mt-3">
                            @if(in_array($field->type, ['short_text', 'email', 'phone', 'number']))
                                <input type="text" disabled class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50" placeholder="Jawaban singkat Anda...">
                            @elseif($field->type === 'long_text')
                                <textarea disabled rows="3" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl bg-slate-50" placeholder="Jawaban panjang Anda..."></textarea>
                            @elseif(in_array($field->type, ['radio', 'dropdown']))
                                <div class="space-y-2">
                                    @foreach($field->options as $opt)
                                        <label class="flex items-center gap-3 text-slate-700">
                                            <input type="radio" disabled class="h-4 w-4 text-slate-400">
                                            <span>{{ $opt->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif($field->type === 'checkbox')
                                <div class="space-y-2">
                                    @foreach($field->options as $opt)
                                        <label class="flex items-center gap-3 text-slate-700">
                                            <input type="checkbox" disabled class="h-4 w-4 rounded text-slate-400">
                                            <span>{{ $opt->label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif($field->type === 'scale')
                                <div class="flex items-center justify-between gap-2 max-w-md">
                                    <span class="text-sm text-slate-500">1</span>
                                    <div class="flex gap-2 flex-1 justify-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <label class="flex flex-col items-center gap-1">
                                                <input type="radio" disabled class="h-4 w-4 text-slate-400">
                                                <span class="text-xs text-slate-500">{{ $i }}</span>
                                            </label>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-slate-500">5</span>
                                </div>
                            @else
                                <div class="p-4 border-2 border-dashed border-slate-200 rounded-xl bg-slate-50 text-center text-sm text-slate-400">
                                    Input tipe {{ $field->type }}
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-400 text-sm">
                        Belum ada pertanyaan di form ini.
                    </div>
                @endforelse

                @if($form->fields->count() > 0)
                    <div class="pt-6 border-t border-slate-100">
                        <button disabled class="px-6 py-3 bg-slate-300 text-white font-semibold rounded-xl w-full sm:w-auto">
                            Kirim Jawaban
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-sidebar-layout>
