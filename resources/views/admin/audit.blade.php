<x-app-sidebar-layout title="Audit Log">
    {{-- Filter bar --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Tipe Aksi</label>
            <input type="text" name="action" value="{{ request('action') }}" placeholder="mis. form.created"
                   class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900">
        </div>
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs font-semibold text-slate-500 mb-1">Pengguna</label>
            <select name="user_id" class="w-full px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900">
                <option value="">Semua Pengguna</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Dari</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">Sampai</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="px-3 py-2 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-900">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-sm font-semibold rounded-lg hover:bg-slate-800 transition">Filter</button>
            <a href="{{ route('admin.audit') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition">Reset</a>
        </div>
    </form>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-50">
            <h3 class="font-outfit font-semibold text-slate-800">Audit Log</h3>
            <p class="text-xs text-slate-500 mt-0.5">{{ $logs->total() }} entri · Immutable · Retensi 1 tahun</p>
        </div>

        @if($logs->isEmpty())
            <div class="py-16 text-center">
                <p class="text-slate-400 text-sm">Belum ada entri audit log.</p>
            </div>
        @else
            <div class="divide-y divide-slate-50">
                @foreach($logs as $log)
                    <div class="px-5 py-3.5 flex items-start gap-4 hover:bg-slate-50 transition">
                        <div class="shrink-0 mt-0.5">
                            @php
                                $actionColor = match(true) {
                                    str_starts_with($log->action, 'form.') => 'bg-indigo-100 text-indigo-600',
                                    str_starts_with($log->action, 'response.') => 'bg-amber-100 text-amber-600',
                                    str_starts_with($log->action, 'export.') => 'bg-emerald-100 text-emerald-600',
                                    default => 'bg-slate-100 text-slate-500',
                                };
                            @endphp
                            <span class="inline-block px-2 py-0.5 text-xs font-mono font-semibold rounded {{ $actionColor }}">
                                {{ $log->action }}
                            </span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700">
                                <span class="font-semibold">{{ $log->user?->name ?? 'System' }}</span>
                                @if($log->meta)
                                    — {{ collect($log->meta)->map(fn($v,$k) => "$k: $v")->implode(', ') }}
                                @endif
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $log->created_at->timezone('Asia/Jakarta')->format('d M Y H:i:s') }}
                                @if($log->ip)
                                    · IP: {{ substr($log->ip, 0, 12) }}…
                                @endif
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="px-5 py-4 border-t border-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-app-sidebar-layout>
