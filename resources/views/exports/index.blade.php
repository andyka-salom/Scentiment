<x-app-sidebar-layout title="Riwayat Export">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
        <div class="px-5 py-4 border-b border-slate-50">
            <h3 class="font-outfit font-semibold text-slate-800">Riwayat Export</h3>
            <p class="text-xs text-slate-500 mt-0.5">File download kedaluwarsa dalam 24 jam setelah dibuat.</p>
        </div>

        @if($exports->isEmpty())
            <div class="py-16 text-center">
                <div class="inline-flex p-4 bg-slate-50 rounded-full mb-3">
                    <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                </div>
                <h4 class="text-slate-700 font-semibold text-sm">Belum ada riwayat export</h4>
                <p class="text-slate-400 text-sm mt-1">Export respons dari halaman Respon setiap form.</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($exports as $export)
                    <div class="flex items-center justify-between px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center shrink-0">
                                <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">{{ $export->form->title ?? 'Form Dihapus' }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Format: <span class="uppercase font-mono font-semibold text-slate-600">{{ $export->format }}</span>
                                    · {{ $export->row_count }} baris
                                    · {{ $export->created_at->timezone('Asia/Jakarta')->format('d M Y H:i') }}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @php
                                $statusMap = [
                                    'done'       => ['text' => 'Selesai', 'class' => 'bg-emerald-50 text-emerald-700'],
                                    'pending'    => ['text' => 'Antri', 'class' => 'bg-slate-100 text-slate-600'],
                                    'processing' => ['text' => 'Diproses', 'class' => 'bg-blue-50 text-blue-700'],
                                    'failed'     => ['text' => 'Gagal', 'class' => 'bg-rose-50 text-rose-700'],
                                ];
                                $s = $statusMap[$export->status] ?? $statusMap['pending'];
                            @endphp
                            <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $s['class'] }}">{{ $s['text'] }}</span>

                            @if($export->status === 'done' && $export->file_path && now()->lt($export->expires_at))
                                <a href="{{ Storage::url($export->file_path) }}"
                                   class="px-3 py-1.5 text-xs font-semibold bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition flex items-center gap-1.5">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                    Unduh
                                </a>
                            @elseif($export->status === 'done')
                                <span class="text-xs text-slate-400 italic">Kedaluwarsa</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="px-5 py-4 border-t border-slate-50">
                {{ $exports->links() }}
            </div>
        @endif
    </div>
</x-app-sidebar-layout>
