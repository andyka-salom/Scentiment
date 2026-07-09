<x-app-sidebar-layout title="Bagikan — {{ $form->title }}">
    <x-form-context-tabs :form="$form" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Share Link & QR Panel -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                        <h3 class="font-outfit font-bold text-lg text-slate-800 mb-4">Link Pengisian</h3>
                        
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">URL Publik</label>
                            <div class="flex gap-2">
                                <input type="text" readonly id="public-url" value="{{ route('public.form', $form->slug) }}" class="flex-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono focus:outline-none">
                                <button onclick="navigator.clipboard.writeText(document.getElementById('public-url').value); window.notyf.success('URL disalin!')" class="px-3 py-1.5 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                                    Copy
                                </button>
                            </div>
                        </div>

                        <!-- QR Code Section -->
                        <div class="border-t border-slate-50 pt-4 text-center">
                            <span class="block text-xs font-semibold text-slate-600 mb-2">Scan QR Code</span>
                            <!-- Generate QR Code using a public API or local helper, let's use an elegant iframe/image generator -->
                            <div class="inline-block p-3 bg-slate-50 border border-slate-100 rounded-xl">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(route('public.form', $form->slug)) }}" alt="QR Code" class="h-40 w-40">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collaborators Access List -->
                <div class="col-span-12 lg:col-span-8 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="font-outfit font-bold text-lg text-slate-800">Akses Kolaborasi</h3>
                            <p class="text-slate-500 text-xs mt-1">Kelola pengguna atau peran internal yang memiliki izin untuk memantau atau mengedit form.</p>
                        </div>
                    </div>

                    <!-- Share List -->
                    <div class="overflow-x-auto mb-8">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                                    <th class="py-3 pr-4">Nama Kolaborator</th>
                                    <th class="py-3 px-4">Tipe Otoritas</th>
                                    <th class="py-3 px-4">Level Akses</th>
                                    <th class="py-3 pl-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50 text-sm text-slate-700">
                                <!-- Owner -->
                                <tr>
                                    <td class="py-3 pr-4 font-semibold text-slate-900">
                                        {{ $form->user->name }}
                                        <span class="ml-2 px-2 py-0.5 text-3xs bg-slate-900 text-white rounded font-normal uppercase">Owner</span>
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-xs">Pemilik Form</td>
                                    <td class="py-3 px-4 font-semibold text-xs text-slate-800">Editor Utama</td>
                                    <td class="py-3 pl-4 text-right">-</td>
                                </tr>
                                
                                @foreach($shares as $share)
                                    <tr>
                                        <td class="py-3 pr-4 font-medium text-slate-800">
                                            @if($share->user)
                                                {{ $share->user->name }}
                                            @elseif($share->role_name)
                                                <span class="italic font-bold">Role: {{ $share->role_name }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 text-xs">
                                            {{ $share->user ? 'Personal User' : 'Role-based Group' }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $share->level === 'editor' ? 'bg-amber-50 text-amber-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ $share->level === 'editor' ? 'Editor (Ubah & Lihat)' : 'Viewer (Lihat saja)' }}
                                            </span>
                                        </td>
                                        <td class="py-3 pl-4 text-right">
                                            <form action="{{ route('forms.share.destroy', [$form, $share]) }}" method="POST" class="delete-form" data-confirm="Cabut akses kolaborator ini?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-500 hover:text-rose-700 text-xs font-bold">
                                                    Hapus Akses
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Invite New Collaborator -->
                    <div class="border-t border-slate-50 pt-6">
                        <h4 class="font-outfit font-semibold text-sm text-slate-800 mb-4">Tambah Kolaborator Baru</h4>
                        
                        <form action="{{ route('forms.share.update', $form) }}" method="POST">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label for="user_id" class="block text-xs font-semibold text-slate-600 mb-1">Pilih User Personal</label>
                                    <select name="user_id" id="user_id" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        <option value="">-- Pilih User --</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="role_name" class="block text-xs font-semibold text-slate-600 mb-1">ATAU Pilih Grup Role</label>
                                    <select name="role_name" id="role_name" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        <option value="">-- Pilih Role --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}">{{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="level" class="block text-xs font-semibold text-slate-600 mb-1">Level Izin Akses</label>
                                    <select name="level" id="level" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs">
                                        <option value="viewer">Viewer (Lihat respons & export)</option>
                                        <option value="editor">Editor (Ubah form & responses)</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                                Tambah Akses Kolaborator
                            </button>
                        </form>
                    </div>

                </div>
    </div>

</x-app-sidebar-layout>
