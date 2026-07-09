<x-app-sidebar-layout title="Rincian Jawaban">
    <x-form-context-tabs :form="$form" />

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- Main Content: Q&A List -->
                <div class="col-span-12 lg:col-span-8 bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="font-outfit font-bold text-lg text-slate-800">Daftar Jawaban</h3>
                        <p class="text-slate-500 text-xs mt-1">Menampilkan pertanyaan-pertanyaan yang dijawab pada versi {{ $response->form_version }}.</p>
                    </div>

                    @php
                        $answers = $response->answers_snapshot ?? [];
                    @endphp

                    <div class="space-y-6 divide-y divide-slate-50">
                        @foreach($fields as $index => $field)
                            @if(in_array($field['type'], ['section', 'statement']))
                                <div class="pt-6 first:pt-0">
                                    <h4 class="font-outfit font-bold text-md text-slate-800">{{ $field['label'] }}</h4>
                                    <p class="text-slate-500 text-xs mt-1">{{ $field['description'] }}</p>
                                </div>
                            @else
                                <div class="pt-6 first:pt-0">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="text-3xs font-semibold px-1.5 py-0.5 rounded bg-slate-100 text-slate-500 uppercase">{{ $field['type'] }}</span>
                                    </div>
                                    <h4 class="font-semibold text-sm text-slate-800">{{ $field['label'] }}</h4>
                                    
                                    <div class="mt-2 bg-slate-50 rounded-lg p-3 border border-slate-100 text-sm text-slate-700 font-medium">
                                        @php
                                            $val = $answers[$field['field_key']] ?? null;
                                        @endphp

                                        @if($val === null || $val === '')
                                            <span class="italic text-slate-400">Tidak Dijawab / Kosong</span>
                                        @else
                                            @if($field['type'] === 'checkbox')
                                                @if(is_array($val))
                                                    <ul class="list-disc list-inside space-y-1">
                                                        @foreach($val as $item)
                                                            <li>{{ $item }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    {{ $val }}
                                                @endif
                                            @elseif($field['type'] === 'matrix')
                                                <div class="space-y-1">
                                                    @foreach($val as $rowKey => $rowVal)
                                                        <div><span class="font-bold">{{ $rowKey }}:</span> {{ $rowVal }}</div>
                                                    @endforeach
                                                </div>
                                            @elseif(in_array($field['type'], ['file', 'signature']))
                                                @php
                                                    // Load file model representation
                                                    $fileIds = is_array($val) ? $val : [$val];
                                                    $files = \App\Models\ResponseFile::whereIn('id', $fileIds)->get();
                                                @endphp
                                                @if($files->isEmpty())
                                                    <span class="text-slate-400 italic">File Lampiran Hilang</span>
                                                @else
                                                    <div class="space-y-1">
                                                        @foreach($files as $file)
                                                            <a href="{{ route('forms.files.download', [$form, $file]) }}" class="text-indigo-600 hover:underline flex items-center gap-1">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                                                {{ $file->original_name }} ({{ round($file->size_bytes / 1024) }} KB)
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            @elseif(is_array($val) && isset($val['value']) && $val['value'] === '__other__')
                                                <span>Lainnya: {{ $val['other'] ?? '' }}</span>
                                            @else
                                                {{ $val }}
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Sidebar Content: Response Metadata -->
                <div class="col-span-12 lg:col-span-4 space-y-6">
                    <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                        <h3 class="font-outfit font-bold text-lg text-slate-800 mb-4">Metadata Submission</h3>
                        
                        <div class="space-y-4 text-xs font-semibold text-slate-600 divide-y divide-slate-50">
                            <div class="flex justify-between py-2 first:pt-0">
                                <span>Status</span>
                                <span class="px-2 py-0.5 rounded-full bg-slate-900 text-white uppercase">{{ $response->status }}</span>
                            </div>
                            
                            <div class="flex justify-between py-3">
                                <span>Waktu Kirim</span>
                                <span class="text-slate-800">{{ $response->submitted_at ? $response->submitted_at->timezone('Asia/Jakarta')->format('d M Y H:i:s') : '-' }}</span>
                            </div>

                            <div class="flex justify-between py-3">
                                <span>Lama Pengisian</span>
                                <span class="text-slate-800">{{ $response->duration_seconds ? $response->duration_seconds . ' detik' : '-' }}</span>
                            </div>

                            @if($form->is_assessment)
                                <div class="flex justify-between py-3">
                                    <span>Skor Penilaian</span>
                                    <span class="text-slate-800 font-bold text-sm">{{ $response->score !== null ? $response->score : '-' }}</span>
                                </div>

                                <div class="flex justify-between py-3">
                                    <span>Grade Akhir</span>
                                    <span class="text-emerald-700 font-bold text-sm">{{ $response->grade ?: '-' }}</span>
                                </div>
                            @endif

                            <div class="flex justify-between py-3">
                                <span>IP Address Hash</span>
                                <span class="text-slate-400 select-all font-mono">{{ substr($response->ip_hash, 0, 16) }}...</span>
                            </div>

                            <div class="flex flex-col gap-1 py-3 last:pb-0">
                                <span>User Agent</span>
                                <span class="text-slate-500 font-normal leading-relaxed break-words text-2xs">{{ $response->user_agent }}</span>
                            </div>
                        </div>
                    </div>
                </div>
    </div>

</x-app-sidebar-layout>
