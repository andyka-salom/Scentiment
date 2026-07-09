<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\ResponseAnswer;
use App\Services\FieldTypeRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function __construct(
        protected FieldTypeRegistry $registry
    ) {}

    /**
     * Show analytics page.
     */
    public function show(Form $form)
    {
        $this->authorize('view', $form);
        return view('forms.analytics', compact('form'));
    }

    /**
     * Fetch analytics dataset JSON for charts.
     */
    public function data(Form $form)
    {
        $this->authorize('view', $form);

        $responses = $form->responses()->where('status', 'complete')->get();
        $totalResponses = $responses->count();
        
        $responsesToday = $form->responses()
            ->where('status', 'complete')
            ->whereDate('submitted_at', today())
            ->count();

        $avgDuration = $responses->avg('duration_seconds');

        // 1. Responses per day (last 30 days)
        $history = $form->responses()
            ->where('status', 'complete')
            ->where('submitted_at', '>=', now()->subDays(30))
            ->select(DB::raw("DATE(submitted_at) as date"), DB::raw("COUNT(*) as count"))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $historyLabels = [];
        $historyData = [];
        for ($i = 29; $i >= 0; $i--) {
            $d = today()->subDays($i)->format('Y-m-d');
            $historyLabels[] = today()->subDays($i)->format('d M');
            
            $match = $history->firstWhere('date', $d);
            $historyData[] = $match ? $match->count : 0;
        }

        // 2. Aggregate per question
        $questionsData = [];
        $fields = $form->fields()->whereNotIn('type', ['section', 'statement'])->get();
        
        foreach ($fields as $field) {
            if (!$this->registry->has($field->type)) {
                continue;
            }

            $answers = ResponseAnswer::where('field_id', $field->id)->get();
            $agg = $this->registry->get($field->type)->aggregate($answers, $field);

            $questionsData[] = [
                'field_key' => $field->field_key,
                'label' => $field->label,
                'type' => $field->type,
                'aggregation' => $agg,
            ];
        }

        return response()->json([
            'summary' => [
                'total' => $totalResponses,
                'today' => $responsesToday,
                'avg_duration' => $avgDuration ? round($avgDuration) : 0,
                'completion_rate' => $totalResponses > 0 ? 100 : 0, // mock completion rate
            ],
            'chart_history' => [
                'labels' => $historyLabels,
                'data' => $historyData,
            ],
            'questions' => $questionsData,
        ]);
    }
}
