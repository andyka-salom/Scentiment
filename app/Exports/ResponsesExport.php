<?php

namespace App\Exports;

use App\Models\Form;
use App\Models\Response as FormResponse;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ResponsesExport implements FromArray, WithHeadings, WithMapping
{
    protected Form $form;
    protected $fields;
    protected $responses;

    public function __construct(Form $form, $responses)
    {
        $this->form = $form;
        // Load active or union fields
        $this->fields = $form->fields()->whereNotIn('type', ['section', 'statement'])->get();
        $this->responses = $responses;
    }

    public function array(): array
    {
        return $this->responses->toArray();
    }

    public function headings(): array
    {
        $headings = [
            'Waktu Submit',
            'Responden',
            'Durasi (detik)',
            'Skor',
            'Grade',
        ];

        foreach ($this->fields as $field) {
            $headings[] = $field->label;
        }

        return $headings;
    }

    /**
     * @param mixed $row
     */
    public function map($row): array
    {
        // $row is array representation of response
        $submittedAt = isset($row['submitted_at']) 
            ? \Carbon\Carbon::parse($row['submitted_at'])->timezone('Asia/Jakarta')->format('Y-m-d H:i:s') 
            : '';

        $respondent = isset($row['user']['name']) ? $row['user']['name'] : 'Publik';
        $duration = $row['duration_seconds'] ?? '';
        $score = $row['score'] ?? '';
        $grade = $row['grade'] ?? '';

        $mapped = [
            $submittedAt,
            $respondent,
            $duration,
            $score,
            $grade,
        ];

        $snapshot = $row['answers_snapshot'] ?? [];

        foreach ($this->fields as $field) {
            $val = $snapshot[$field->field_key] ?? '';
            
            // Format arrays or objects (like checkbox or matrix)
            if (is_array($val)) {
                if (isset($val['value']) && $val['value'] === '__other__') {
                    $val = 'Lainnya: ' . ($val['other'] ?? '');
                } else {
                    $val = implode('; ', $val);
                }
            }
            
            $mapped[] = $val;
        }

        return $mapped;
    }
}
