<x-app-sidebar-layout title="Pengguna & Role">
    <x-slot name="topbarActions">
        <a href="#" class="px-4 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm font-semibold flex items-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
            Tambah Pengguna
        </a>
    </x-slot>

    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-50">
            <h3 class="font-outfit font-semibold text-slate-800">Daftar Pengguna</h3>
            <p class="text-xs text-slate-500 mt-0.5">Total {{ $users->total() }} pengguna terdaftar.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Nama</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Email</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Role</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Bergabung</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($users as $user)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                        {{ strtoupper(substr($user->name, 0, 2)) }}
                                    </div>
                                    <span class="font-medium text-slate-800">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-slate-600">{{ $user->email }}</td>
                            <td class="px-5 py-3.5">
                                @foreach($user->roles as $role)
                                    <span class="px-2 py-0.5 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-full">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td class="px-5 py-3.5 text-slate-500 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                            <td class="px-5 py-3.5 text-right">
                                <button class="text-xs text-slate-500 hover:text-slate-800 font-medium transition">Edit</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-5 py-4 border-t border-slate-50">
            {{ $users->links() }}
        </div>
    </div>
</x-app-sidebar-layout>
