<x-app-sidebar-layout title="Pengaturan — {{ $form->title }}">
    <x-form-context-tabs :form="$form" />

    <div class="max-w-4xl">
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('forms.settings.update', $form) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- General Settings -->
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                        <h3 class="font-outfit font-bold text-lg text-slate-800 mb-4">Pengaturan Umum</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">Status Form</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                    <option value="draft" {{ $form->status === 'draft' ? 'selected' : '' }}>Draft (Hanya Creator)</option>
                                    <option value="published" {{ $form->status === 'published' ? 'selected' : '' }}>Publik (Published)</option>
                                    <option value="closed" {{ $form->status === 'closed' ? 'selected' : '' }}>Tutup Respons</option>
                                    <option value="archived" {{ $form->status === 'archived' ? 'selected' : '' }}>Arsip</option>
                                </select>
                            </div>

                            <div>
                                <label for="access_type" class="block text-sm font-semibold text-slate-700 mb-2">Tipe Akses</label>
                                <select name="access_type" id="access_type" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-slate-900 focus:border-transparent">
                                    <option value="public" {{ $form->access_type === 'public' ? 'selected' : '' }}>Terbuka untuk Publik</option>
                                    <option value="internal" {{ $form->access_type === 'internal' ? 'selected' : '' }}>Wajib Login (Heaven Scent)</option>
                                    <option value="token" {{ $form->access_type === 'token' ? 'selected' : '' }}>Hanya yang Memiliki Token</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-50 mt-6 pt-6">
                            <div class="flex items-center justify-between py-2">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 block">Tampilkan Judul Form</span>
                                    <span class="text-xs text-slate-500">Tampilkan judul di form publik.</span>
                                </div>
                                <input type="checkbox" name="show_title" value="1" {{ ($form->settings['show_title'] ?? true) ? 'checked' : '' }} class="rounded border-slate-200 text-slate-900 focus:ring-slate-900">
                            </div>

                            <div class="flex items-center justify-between py-2">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 block">Tampilkan Deskripsi Form</span>
                                    <span class="text-xs text-slate-500">Tampilkan deskripsi di form publik.</span>
                                </div>
                                <input type="checkbox" name="show_description" value="1" {{ ($form->settings['show_description'] ?? true) ? 'checked' : '' }} class="rounded border-slate-200 text-slate-900 focus:ring-slate-900">
                            </div>
                        </div>
                    </div>

                    <!-- Limits & Scheduling -->
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                        <h3 class="font-outfit font-bold text-lg text-slate-800 mb-4">Batasan & Penjadwalan</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                            <div>
                                <label for="opens_at" class="block text-sm font-semibold text-slate-700 mb-2">Mulai Dibuka</label>
                                <input type="datetime-local" name="opens_at" id="opens_at" value="{{ $form->opens_at ? $form->opens_at->format('Y-m-d\TH:i') : '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            </div>

                            <div>
                                <label for="closes_at" class="block text-sm font-semibold text-slate-700 mb-2">Ditutup Otomatis</label>
                                <input type="datetime-local" name="closes_at" id="closes_at" value="{{ $form->closes_at ? $form->closes_at->format('Y-m-d\TH:i') : '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 border-t border-slate-50 pt-4">
                            <div class="flex items-center justify-between py-2">
                                <div>
                                    <span class="text-sm font-semibold text-slate-700 block">Satu Respons Per User</span>
                                    <span class="text-xs text-slate-500">Membatasi responden agar hanya bisa mengisi 1 kali.</span>
                                </div>
                                <input type="checkbox" name="one_response_per_user" value="1" {{ ($form->settings['one_response_per_user'] ?? false) ? 'checked' : '' }} class="rounded border-slate-200 text-slate-900 focus:ring-slate-900">
                            </div>

                            <div>
                                <label for="response_limit" class="block text-sm font-semibold text-slate-700 mb-1">Kuota Maksimal Respons</label>
                                <input type="number" name="response_limit" id="response_limit" value="{{ $form->settings['response_limit'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" placeholder="Tanpa batas">
                            </div>
                        </div>
                    </div>

                    <!-- Submission Landing & Feedback -->
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                        <h3 class="font-outfit font-bold text-lg text-slate-800 mb-4">Pengalaman Halaman Sukses</h3>
                        
                        <div class="mb-4">
                            <label for="success_message" class="block text-sm font-semibold text-slate-700 mb-2">Pesan Sukses Custom</label>
                            <textarea name="success_message" id="success_message" rows="3" required class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm">{{ $form->settings['success_message'] ?? 'Terima kasih! Jawaban Anda telah kami terima.' }}</textarea>
                        </div>

                        <div>
                            <label for="redirect_url" class="block text-sm font-semibold text-slate-700 mb-2">Redirect URL setelah submit (Opsional)</label>
                            <input type="url" name="redirect_url" id="redirect_url" value="{{ $form->settings['redirect_url'] ?? '' }}" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm" placeholder="https://example.com/thank-you">
                        </div>
                    </div>

                    <!-- Assessment Mode Toggle & Settings -->
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm" x-data="{ isAssessment: {{ $form->is_assessment ? 'true' : 'false' }} }">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h3 class="font-outfit font-bold text-lg text-slate-800">Mode Penilaian (Assessment)</h3>
                                <p class="text-slate-500 text-xs mt-1">Mengaktifkan bobot skor untuk kuis, ujian, atau evaluasi.</p>
                            </div>
                            <input type="checkbox" name="is_assessment" value="1" x-model="isAssessment" class="rounded border-slate-200 text-slate-900 focus:ring-slate-900">
                        </div>

                        <!-- Grade Mapping Config -->
                        <div x-show="isAssessment" class="border-t border-slate-50 pt-4">
                            <div class="flex justify-between items-center mb-4">
                                <span class="text-sm font-semibold text-slate-700">Skema Grade Penilaian</span>
                                <span class="text-xs text-slate-500">Rentang skor -> Nama Grade</span>
                            </div>

                            <div class="space-y-3">
                                @php
                                    $grades = $form->settings['grade_map'] ?? [
                                        ['min' => 0, 'max' => 59, 'label' => 'Perlu Perbaikan'],
                                        ['min' => 60, 'max' => 79, 'label' => 'Baik'],
                                        ['min' => 80, 'max' => 100, 'label' => 'Sangat Baik']
                                    ];
                                @endphp
                                @foreach($grades as $index => $grade)
                                    <div class="flex items-center gap-3">
                                        <input type="number" name="grade_map[{{ $index }}][min]" value="{{ $grade['min'] }}" class="w-20 px-2 py-1 border border-slate-200 rounded-lg text-xs" placeholder="Min">
                                        <span class="text-slate-400 text-xs">sampai</span>
                                        <input type="number" name="grade_map[{{ $index }}][max]" value="{{ $grade['max'] }}" class="w-20 px-2 py-1 border border-slate-200 rounded-lg text-xs" placeholder="Max">
                                        <input type="text" name="grade_map[{{ $index }}][label]" value="{{ $grade['label'] }}" class="flex-1 px-3 py-1 border border-slate-200 rounded-lg text-xs" placeholder="Label Grade">
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex items-center justify-between mt-6 pt-4 border-t border-slate-50">
                                <div>
                                    <span class="text-xs font-semibold text-slate-600 block">Tampilkan Hasil Skor</span>
                                    <span class="text-xs text-slate-500">Tampilkan skor kelulusan kepada responden setelah selesai mengisi form.</span>
                                </div>
                                <input type="checkbox" name="show_score" value="1" {{ ($form->settings['show_score'] ?? false) ? 'checked' : '' }} class="rounded border-slate-200 text-slate-900 focus:ring-slate-900">
                            </div>
                        </div>
                    </div>

                    <!-- Submit Actions -->
                    <div class="flex justify-end gap-3">
                        <button type="submit" class="px-6 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
    </div>

</x-app-sidebar-layout>
