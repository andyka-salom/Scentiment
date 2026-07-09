<x-app-sidebar-layout title="Konfigurasi Global">
    <div class="max-w-2xl space-y-5">

        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3">
            <svg class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <p class="text-sm text-amber-800">Halaman konfigurasi global sedang dalam pengembangan. Pengaturan saat ini menggunakan nilai default dari <code class="font-mono bg-amber-100 px-1 rounded">.env</code>.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-50">
                <h3 class="font-outfit font-semibold text-slate-800">Upload & Storage</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-slate-50">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Max File Upload Size</p>
                        <p class="text-xs text-slate-400 mt-0.5">Ukuran maksimal per file pada form publik</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-800">5 MB</span>
                </div>
                <div class="flex items-center justify-between py-3 border-b border-slate-50">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Storage Driver</p>
                        <p class="text-xs text-slate-400 mt-0.5">Driver penyimpanan file saat ini</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-800 font-mono">{{ config('filesystems.default', 'local') }}</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Retensi File Export</p>
                        <p class="text-xs text-slate-400 mt-0.5">File export otomatis dihapus setelah</p>
                    </div>
                    <span class="text-sm font-semibold text-slate-800">24 Jam</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-50">
                <h3 class="font-outfit font-semibold text-slate-800">Keamanan</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center justify-between py-3 border-b border-slate-50">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">Rate Limit Submit Publik</p>
                        <p class="text-xs text-slate-400 mt-0.5">Batas permintaan per IP per menit</p>
                    </div>
                    <span class="text-sm font-semibold text-emerald-700">10/menit</span>
                </div>
                <div class="flex items-center justify-between py-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">IP Hashing</p>
                        <p class="text-xs text-slate-400 mt-0.5">IP responden publik disimpan sebagai hash SHA-256</p>
                    </div>
                    <span class="text-sm font-semibold text-emerald-700 flex items-center gap-1">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                        Aktif
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm">
            <div class="px-5 py-4 border-b border-slate-50">
                <h3 class="font-outfit font-semibold text-slate-800">Informasi Sistem</h3>
            </div>
            <div class="p-5 space-y-3">
                @foreach([
                    ['Laravel', app()->version()],
                    ['PHP', PHP_VERSION],
                    ['Environment', app()->environment()],
                    ['Database', config('database.default')],
                    ['Queue Driver', config('queue.default')],
                ] as [$label, $val])
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">{{ $label }}</span>
                        <span class="text-sm font-mono font-semibold text-slate-800">{{ $val }}</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</x-app-sidebar-layout>
