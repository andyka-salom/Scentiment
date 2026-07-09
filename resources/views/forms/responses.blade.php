<x-app-sidebar-layout title="Respons — {{ $form->title }}">
    <x-slot name="topbarActions">
        <button onclick="document.getElementById('export-modal').classList.remove('hidden')"
                class="px-3 py-1.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition text-sm font-medium flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Ekspor
        </button>
    </x-slot>

    <x-form-context-tabs :form="$form" />

    <!-- DataTables Assets CDN -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        /* Custom Datatables premium overrides */
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.375rem;
            border-color: #e2e8f0;
            padding-right: 2rem;
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.375rem;
            border-color: #e2e8f0;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #0f172a !important;
            color: #ffffff !important;
            border: 1px solid #0f172a !important;
            border-radius: 0.375rem;
        }
    </style>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filter Bar -->
            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm mb-6">
                <h3 class="font-outfit font-bold text-sm text-slate-800 uppercase tracking-wider mb-4">Saring Data Respons</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="filter-start-date" class="block text-xs font-semibold text-slate-600 mb-1">Mulai Tanggal</label>
                        <input type="date" id="filter-start-date" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs">
                    </div>
                    <div>
                        <label for="filter-end-date" class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                        <input type="date" id="filter-end-date" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs">
                    </div>
                    <div>
                        <label for="filter-version" class="block text-xs font-semibold text-slate-600 mb-1">Versi Form</label>
                        <select id="filter-version" class="w-full px-3 py-1.5 border border-slate-200 rounded-lg text-xs">
                            <option value="">Semua Versi</option>
                            @for($v = 1; $v <= $form->current_version; $v++)
                                <option value="{{ $v }}">Versi {{ $v }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button onclick="reloadTable()" class="w-full py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800 transition">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table Card -->
            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table id="responses-table" class="w-full text-left border-collapse text-sm text-slate-700">
                        <thead>
                            <tr class="border-b border-slate-100 text-slate-500 uppercase tracking-wider text-xs font-semibold">
                                <th class="py-3">Waktu Submit</th>
                                <th class="py-3">Responden</th>
                                <th class="py-3">Durasi</th>
                                <th class="py-3">Skor</th>
                                <th class="py-3">Grade</th>
                                <th class="py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="export-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 flex items-center justify-center hidden">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-slate-900">Ekspor Data</h3>
                <button onclick="document.getElementById('export-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            
            <form action="{{ route('forms.export', $form) }}" method="POST">
                @csrf
                <!-- Filter values cloned for exporter -->
                <input type="hidden" name="start_date" id="export-start-date">
                <input type="hidden" name="end_date" id="export-end-date">
                <input type="hidden" name="version" id="export-version">

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">Pilih Format File</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="flex flex-col items-center justify-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 [&:has(input:checked)]:border-slate-900">
                            <input type="radio" name="format" value="xlsx" checked class="sr-only">
                            <span class="font-bold text-sm text-slate-800">Excel (XLSX)</span>
                            <span class="text-slate-400 text-3xs mt-1">Sangat Direkomendasikan</span>
                        </label>
                        <label class="flex flex-col items-center justify-center p-4 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 [&:has(input:checked)]:border-slate-900">
                            <input type="radio" name="format" value="csv" class="sr-only">
                            <span class="font-bold text-sm text-slate-800">CSV Polos</span>
                            <span class="text-slate-400 text-3xs mt-1">Data mentah tabular</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-slate-50">
                    <button type="button" onclick="document.getElementById('export-modal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white rounded-lg text-sm font-semibold">
                        Unduh File
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTables Integration script -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        let table;

        $(document).ready(function() {
            table = $('#responses-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("forms.responses.data", $form) }}',
                    data: function (d) {
                        d.start_date = $('#filter-start-date').val();
                        d.end_date = $('#filter-end-date').val();
                        d.version = $('#filter-version').val();
                    }
                },
                columns: [
                    { data: 'submitted_time', name: 'submitted_at' },
                    { data: 'respondent_info', name: 'user_id', orderable: false },
                    { data: 'duration_seconds', name: 'duration_seconds', render: function(d) { return d ? d + 's' : '-'; } },
                    { data: 'score', name: 'score', defaultContent: '-' },
                    { data: 'grade', name: 'grade', defaultContent: '-' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right' }
                ],
                order: [[0, 'desc']],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ respons",
                    paginate: {
                        next: "Lanjut",
                        previous: "Kembali"
                    }
                }
            });

            // Set up listener for export filters
            $('form').submit(function() {
                $('#export-start-date').val($('#filter-start-date').val());
                $('#export-end-date').val($('#filter-end-date').val());
                $('#export-version').val($('#filter-version').val());
            });
        });

        function reloadTable() {
            table.draw();
        }
    </script>

</x-app-sidebar-layout>
