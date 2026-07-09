<x-app-sidebar-layout title="Trash">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="font-outfit font-semibold text-slate-800">Form Terhapus (Trash)</h3>
                <p class="text-xs text-slate-500 mt-0.5">Form akan dihapus permanen setelah 30 hari.</p>
            </div>
            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-xs font-semibold">{{ $trashedForms->total() }} Form</span>
        </div>

        @if($trashedForms->isEmpty())
            <div class="py-16 text-center">
                <div class="inline-flex p-4 bg-slate-50 rounded-full mb-3">
                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </div>
                <h4 class="text-slate-700 font-semibold text-sm">Trash Kosong</h4>
                <p class="text-slate-400 text-sm mt-1">Tidak ada form yang dihapus.</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($trashedForms as $form)
                    <div class="flex items-center justify-between px-5 py-4 hover:bg-slate-50 transition">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center shrink-0 text-slate-400">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800 line-through decoration-slate-300">{{ $form->title }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    Pemilik: <span class="font-medium text-slate-700">{{ $form->user->name ?? 'Unknown' }}</span>
                                    <span class="mx-1.5 text-slate-300">•</span>
                                    Dihapus: {{ $form->deleted_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                </p>
                                @php
                                    $daysLeft = 30 - $form->deleted_at->diffInDays(now());
                                @endphp
                                <p class="text-xs {{ $daysLeft <= 3 ? 'text-rose-600 font-medium' : 'text-slate-400' }} mt-0.5">
                                    Akan dihapus permanen dalam {{ $daysLeft }} hari
                                </p>
                            </div>
                        </div>
                        <div class="shrink-0">
                            @if($daysLeft >= 0)
                                <form action="{{ route('admin.trash.restore', $form->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-slate-900 text-white text-xs font-semibold rounded-lg hover:bg-slate-800 transition flex items-center gap-1.5">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        Restore
                                    </button>
                                </form>
                            @else
                                <span class="px-2 py-1 text-xs text-rose-600 bg-rose-50 rounded font-medium">Expired</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-slate-50">
                {{ $trashedForms->links() }}
            </div>
        @endif
    </div>
</x-app-sidebar-layout>
