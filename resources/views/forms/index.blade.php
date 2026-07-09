<x-app-sidebar-layout title="{{ $filter === 'shared' ? 'Dibagikan ke Saya' : 'Form Saya' }}">
    <x-slot name="topbarActions">
        <button onclick="document.getElementById('create-form-modal').classList.remove('hidden')"
                class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition duration-150 text-sm font-semibold flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Buat Form Baru
        </button>
    </x-slot>

    <!-- Empty State -->
    @if($forms->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center shadow-sm">
            <div class="inline-flex p-4 bg-slate-50 text-slate-400 rounded-full mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-1">Belum ada form</h3>
            <p class="text-slate-500 text-sm max-w-sm mx-auto mb-6">Mulai buat form penilaian, survey atau kuisioner pertama Anda.</p>
            <button onclick="document.getElementById('create-form-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition duration-150 text-sm font-semibold inline-flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Buat Form Pertama
            </button>
        </div>
    @else
        <!-- Forms Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @foreach($forms as $form)
                @php
                    $statusColors = [
                        'draft'     => 'bg-slate-100 text-slate-700',
                        'published' => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                        'closed'    => 'bg-rose-50 text-rose-700 border border-rose-100',
                        'archived'  => 'bg-amber-50 text-amber-700 border border-amber-100',
                    ];
                    $statusLabels = ['draft' => 'Draft', 'published' => 'Publik', 'closed' => 'Ditutup', 'archived' => 'Arsip'];
                @endphp
                <div class="bg-white rounded-2xl border border-slate-100 p-5 flex flex-col shadow-sm hover:shadow-md transition duration-200 group">
                    <div class="flex justify-between items-start mb-3">
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColors[$form->status] ?? 'bg-slate-100' }}">
                            {{ $statusLabels[$form->status] ?? $form->status }}
                        </span>
                        <span class="text-xs text-slate-400 font-medium flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                            {{ $form->responses_count }} Respon
                        </span>
                    </div>

                    <h3 class="text-base font-bold text-slate-800 mb-1 leading-snug line-clamp-1">{{ $form->title }}</h3>
                    <p class="text-slate-500 text-sm mb-4 line-clamp-2 flex-1">{{ $form->description ?: 'Tidak ada deskripsi.' }}</p>

                    <div class="pt-3 border-t border-slate-50 mt-auto">
                        <!-- Primary Actions -->
                        <div class="grid grid-cols-3 gap-1.5 text-center text-xs font-semibold mb-2">
                            <a href="{{ route('forms.build', $form) }}"
                               class="py-2 text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition flex items-center justify-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                Builder
                            </a>
                            <a href="{{ route('forms.responses', $form) }}"
                               class="py-2 text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition flex items-center justify-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                Respon
                            </a>
                            <a href="{{ route('forms.analytics', $form) }}"
                               class="py-2 text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition flex items-center justify-center gap-1">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                                Analitik
                            </a>
                        </div>
                        <!-- Secondary Actions -->
                        <div class="flex justify-between items-center pt-2 border-t border-slate-50">
                            <a href="{{ route('forms.settings', $form) }}"
                               class="text-xs text-slate-500 hover:text-slate-700 transition font-medium">
                                Pengaturan
                            </a>
                            <form action="{{ route('forms.destroy', $form) }}" method="POST" class="delete-form" data-confirm="Yakin ingin memindahkan form ini ke Trash?">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-600 transition font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $forms->links() }}
        </div>
    @endif

    <!-- Create Form Modal -->
    <div id="create-form-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-100 mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-900">Buat Form Baru</h3>
                <button onclick="document.getElementById('create-form-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form action="{{ route('forms.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">Judul Form</label>
                    <input type="text" name="title" id="title" required
                           class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent"
                           placeholder="mis. Survey Kepuasan Pelanggan 2025">
                </div>

                <div class="mb-6">
                    <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="description" id="description" rows="3"
                              class="w-full px-3.5 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent"
                              placeholder="Jelaskan tujuan form ini..."></textarea>
                </div>

                <div class="flex justify-end gap-3 border-t border-slate-50 pt-4">
                    <button type="button" onclick="document.getElementById('create-form-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold hover:bg-slate-200 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold hover:bg-slate-800 transition">
                        Buat & Mulai
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-app-sidebar-layout>
