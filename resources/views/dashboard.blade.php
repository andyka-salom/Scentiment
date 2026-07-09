<x-app-sidebar-layout title="Dashboard">
    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Form Aktif</p>
            <p class="text-3xl font-bold text-slate-900">{{ $activeForms }}</p>
            <p class="text-xs text-slate-500 mt-1">Form berstatus Published</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Respon Minggu Ini</p>
            <p class="text-3xl font-bold text-emerald-600">{{ $weeklyResponses }}</p>
            <p class="text-xs text-slate-500 mt-1">Total respons 7 hari ini</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Segera Tutup</p>
            <p class="text-3xl font-bold text-amber-600">{{ $closingSoon->count() }}</p>
            <p class="text-xs text-slate-500 mt-1">Dalam 7 hari ke depan</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Form Saya</p>
            <p class="text-3xl font-bold text-slate-900">{{ $myForms->count() }}</p>
            <p class="text-xs text-slate-500 mt-1">5 terbaru ditampilkan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- My Forms --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex justify-between items-center px-5 py-4 border-b border-slate-50">
                <h3 class="font-outfit font-semibold text-slate-800 text-sm">Form Terbaru Saya</h3>
                <a href="{{ route('forms.index') }}" class="text-xs text-slate-500 hover:text-slate-800 font-medium transition">Lihat semua →</a>
            </div>
            @if($myForms->isEmpty())
                <div class="px-5 py-10 text-center">
                    <p class="text-slate-400 text-sm">Belum ada form. <a href="{{ route('forms.index') }}" class="text-slate-700 underline">Buat form pertama.</a></p>
                </div>
            @else
                <div class="divide-y divide-slate-50">
                    @foreach($myForms as $form)
                        @php
                            $statusColors = ['draft'=>'bg-slate-100 text-slate-600','published'=>'bg-emerald-50 text-emerald-700','closed'=>'bg-rose-50 text-rose-700','archived'=>'bg-amber-50 text-amber-700'];
                        @endphp
                        <div class="flex items-center justify-between px-5 py-3.5 hover:bg-slate-50 transition group">
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('forms.build', $form) }}" class="font-medium text-slate-800 text-sm truncate block group-hover:text-slate-900">{{ $form->title }}</a>
                                <p class="text-xs text-slate-400 mt-0.5">{{ $form->responses_count }} respon · dibuat {{ $form->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="flex items-center gap-2 shrink-0 ml-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $statusColors[$form->status] ?? 'bg-slate-100' }}">
                                    {{ ucfirst($form->status) }}
                                </span>
                                <a href="{{ route('forms.responses', $form) }}" class="text-slate-400 hover:text-slate-700 transition">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-5">
            {{-- Forms Closing Soon --}}
            @if($closingSoon->isNotEmpty())
                <div class="bg-amber-50 border border-amber-100 rounded-2xl p-5">
                    <h3 class="font-outfit font-semibold text-amber-800 text-sm mb-3 flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Segera Ditutup
                    </h3>
                    <div class="space-y-2">
                        @foreach($closingSoon as $form)
                            <div class="flex items-center justify-between">
                                <a href="{{ route('forms.settings', $form) }}" class="text-sm text-amber-900 font-medium truncate hover:underline max-w-[140px]">{{ $form->title }}</a>
                                <span class="text-xs text-amber-700 shrink-0">{{ $form->closes_at->diffForHumans() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Recent Responses --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                <div class="px-4 py-3 border-b border-slate-50">
                    <h3 class="font-outfit font-semibold text-slate-800 text-sm">Respon Terbaru</h3>
                </div>
                @if($recentResponses->isEmpty())
                    <p class="px-4 py-6 text-xs text-slate-400 text-center">Belum ada respon masuk.</p>
                @else
                    <div class="divide-y divide-slate-50">
                        @foreach($recentResponses as $resp)
                            <div class="px-4 py-3 hover:bg-slate-50 transition">
                                <a href="{{ route('forms.responses.show', [$resp->form, $resp]) }}" class="block">
                                    <p class="text-xs font-medium text-slate-800 truncate">{{ $resp->form->title }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">
                                        {{ $resp->user ? $resp->user->name : 'Publik' }} · {{ $resp->submitted_at?->timezone('Asia/Jakarta')->diffForHumans() }}
                                        @if($resp->score !== null)
                                            · <span class="text-indigo-600 font-semibold">{{ $resp->score }}</span>
                                        @endif
                                    </p>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Recent Audit Activity (Admin only) --}}
            @if(count($recentActivity) > 0)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
                    <div class="px-4 py-3 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="font-outfit font-semibold text-slate-800 text-sm">Aktivitas Sistem</h3>
                        <a href="{{ route('admin.audit') }}" class="text-xs text-slate-500 hover:text-slate-700 transition">Lihat semua</a>
                    </div>
                    <div class="divide-y divide-slate-50">
                        @foreach($recentActivity->take(5) as $log)
                            <div class="px-4 py-2.5">
                                <p class="text-xs font-mono text-slate-600">{{ $log->action }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $log->user?->name ?? 'System' }} · {{ $log->created_at->timezone('Asia/Jakarta')->diffForHumans() }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

</x-app-sidebar-layout>
