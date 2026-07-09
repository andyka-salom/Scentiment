<x-app-sidebar-layout title="Analitik Respons: {{ $form->title }}">
    <x-form-context-tabs :form="$form" />

    <div class="py-12" x-data="analyticsApp()" x-init="fetchData()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Summary KPI Widgets -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-xs font-semibold text-slate-400 block uppercase">Total Respons</span>
                    <span class="text-3xl font-extrabold text-slate-800 mt-2 block" x-text="summary.total">0</span>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-xs font-semibold text-slate-400 block uppercase">Respons Hari Ini</span>
                    <span class="text-3xl font-extrabold text-slate-800 mt-2 block" x-text="summary.today">0</span>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-xs font-semibold text-slate-400 block uppercase">Rata-rata Durasi</span>
                    <span class="text-3xl font-extrabold text-slate-800 mt-2 block" x-text="summary.avg_duration + 's'">0s</span>
                </div>
                <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                    <span class="text-xs font-semibold text-slate-400 block uppercase">Completion Rate</span>
                    <span class="text-3xl font-extrabold text-emerald-600 mt-2 block" x-text="summary.completion_rate + '%'">0%</span>
                </div>
            </div>

            <!-- Response History Chart -->
            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm mb-8">
                <h3 class="font-outfit font-bold text-md text-slate-800 mb-4">Tren Respons (30 Hari Terakhir)</h3>
                <div class="h-[250px]">
                    <canvas id="history-chart"></canvas>
                </div>
            </div>

            <!-- Question Breakdown Analysis -->
            <div class="border-t border-slate-100 pt-8">
                <h3 class="font-outfit font-bold text-lg text-slate-800 mb-6">Analisis Per Pertanyaan</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <template x-for="(q, qIdx) in questions" :key="qIdx">
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm">
                            <h4 class="font-semibold text-sm text-slate-800 mb-4" x-text="q.label"></h4>
                            
                            <!-- Choice Fields chart container -->
                            <div x-show="['radio', 'checkbox', 'dropdown', 'scale', 'rating'].includes(q.type)" class="h-[200px] flex items-center justify-center">
                                <canvas :id="'chart-q-' + qIdx"></canvas>
                            </div>

                            <!-- Text fields aggregation list fallback -->
                            <div x-show="['short_text', 'long_text', 'email', 'phone'].includes(q.type)" class="space-y-2 max-h-[200px] overflow-y-auto pr-1">
                                <template x-for="(count, val) in q.aggregation">
                                    <div class="flex justify-between items-center bg-slate-50 border border-slate-100 px-3 py-2 rounded-lg text-xs font-medium text-slate-700">
                                        <span x-text="val"></span>
                                        <span class="px-2 py-0.5 bg-slate-200 text-slate-600 font-bold rounded" x-text="count + 'x'"></span>
                                    </div>
                                </template>
                                <div x-show="Object.keys(q.aggregation).length === 0" class="text-center py-8 text-slate-400 text-xs">
                                    Belum ada jawaban.
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <script>
        function analyticsApp() {
            return {
                summary: {},
                questions: [],
                fetchData() {
                    fetch('{{ route("forms.analytics.data", $form) }}')
                        .then(res => res.json())
                        .then(data => {
                            this.summary = data.summary;
                            this.questions = data.questions;
                            
                            // Initialize History Chart
                            this.renderHistoryChart(data.chart_history);

                            // Initialize individual charts
                            this.$nextTick(() => {
                                this.questions.forEach((q, idx) => {
                                    if (['radio', 'checkbox', 'dropdown', 'scale', 'rating'].includes(q.type)) {
                                        this.renderQuestionChart(idx, q);
                                    }
                                });
                            });
                        });
                },
                renderHistoryChart(history) {
                    const ctx = document.getElementById('history-chart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: history.labels,
                            datasets: [{
                                label: 'Jumlah Respons',
                                data: history.data,
                                borderColor: '#0f172a',
                                backgroundColor: 'rgba(15, 23, 42, 0.05)',
                                fill: true,
                                tension: 0.3
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                y: { beginAtZero: true, ticks: { precision: 0 } }
                            }
                        }
                    });
                },
                renderQuestionChart(idx, q) {
                    const ctx = document.getElementById('chart-q-' + idx).getContext('2d');
                    const labels = Object.keys(q.aggregation);
                    const data = Object.values(q.aggregation);

                    new Chart(ctx, {
                        type: ['scale', 'rating'].includes(q.type) ? 'bar' : 'pie',
                        data: {
                            labels: labels,
                            datasets: [{
                                data: data,
                                backgroundColor: [
                                    '#0f172a', '#334155', '#475569', '#64748b', '#94a3b8', '#cbd5e1'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: { boxWidth: 12, font: { size: 10 } }
                                }
                            }
                        }
                    });
                }
            };
        }
    </script>
</x-app-sidebar-layout>
