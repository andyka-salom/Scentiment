<x-app-sidebar-layout title="Galeri Template">
    <div class="mb-5">
        <p class="text-slate-500 text-sm">Pilih template untuk memulai form dengan cepat. Template berisi struktur pertanyaan yang siap digunakan.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($templates as $template)
            @php
                $colorMap = [
                    'indigo'  => ['bg' => 'bg-indigo-50',  'text' => 'text-indigo-600',  'btn' => 'border-indigo-200 text-indigo-700 hover:bg-indigo-50'],
                    'yellow'  => ['bg' => 'bg-yellow-50',  'text' => 'text-yellow-600',  'btn' => 'border-yellow-200 text-yellow-700 hover:bg-yellow-50'],
                    'emerald' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600', 'btn' => 'border-emerald-200 text-emerald-700 hover:bg-emerald-50'],
                    'blue'    => ['bg' => 'bg-blue-50',    'text' => 'text-blue-600',    'btn' => 'border-blue-200 text-blue-700 hover:bg-blue-50'],
                    'purple'  => ['bg' => 'bg-purple-50',  'text' => 'text-purple-600',  'btn' => 'border-purple-200 text-purple-700 hover:bg-purple-50'],
                ];
                $c = $colorMap[$template['color']] ?? $colorMap['indigo'];
            @endphp
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col hover:shadow-md transition group">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} flex items-center justify-center shrink-0">
                        <svg class="h-5 w-5 {{ $c['text'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $template['icon'] }}" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-outfit font-semibold text-slate-800 text-sm leading-snug">{{ $template['title'] }}</h3>
                        <span class="inline-block mt-1 text-xs text-slate-400 font-medium bg-slate-50 border border-slate-100 px-2 py-0.5 rounded-full">{{ $template['category'] }}</span>
                    </div>
                </div>
                <p class="text-slate-500 text-sm flex-1 mb-5">{{ $template['description'] }}</p>
                <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                    <span class="text-xs text-slate-400">{{ $template['questions'] }} pertanyaan</span>
                    <form action="{{ route('forms.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="title" value="{{ $template['title'] }}">
                        <input type="hidden" name="description" value="{{ $template['description'] }}">
                        <input type="hidden" name="template" value="{{ $template['id'] }}">
                        <button type="submit"
                                class="px-3 py-1.5 text-xs font-semibold border rounded-lg transition {{ $c['btn'] }}">
                            Gunakan Template
                        </button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 bg-slate-50 border border-slate-100 rounded-2xl p-6 flex items-center gap-4">
        <div class="w-10 h-10 rounded-xl bg-slate-200 flex items-center justify-center shrink-0">
            <svg class="h-5 w-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
        </div>
        <div class="flex-1">
            <h4 class="font-semibold text-slate-700 text-sm">Mulai dari Awal</h4>
            <p class="text-slate-500 text-xs mt-0.5">Buat form kosong dan tambahkan pertanyaan sendiri sesuai kebutuhan.</p>
        </div>
        <button onclick="document.getElementById('create-form-modal-template').classList.remove('hidden')"
                class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition shrink-0">
            Buat Form Kosong
        </button>
    </div>

    {{-- Create Form Modal --}}
    <div id="create-form-modal-template" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 mx-4">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-slate-900">Buat Form Baru</h3>
                <button onclick="document.getElementById('create-form-modal-template').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <form action="{{ route('forms.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Judul Form</label>
                    <input type="text" name="title" required
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                           placeholder="mis. Survey Kepuasan Pelanggan">
                </div>
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi (Opsional)</label>
                    <textarea name="description" rows="2"
                              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-900"
                              placeholder="Jelaskan tujuan form..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('create-form-modal-template').classList.add('hidden')"
                            class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition">Buat</button>
                </div>
            </form>
        </div>
    </div>
</x-app-sidebar-layout>
