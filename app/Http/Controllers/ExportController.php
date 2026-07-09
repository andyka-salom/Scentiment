<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Export;
use App\Models\Form;
use App\Models\Response as FormResponse;
use App\Exports\ResponsesExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ExportController extends Controller
{
    /**
     * Show export history for the current user.
     */
    public function index()
    {
        $exports = Export::where('user_id', Auth::id())
            ->with('form')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('exports.index', compact('exports'));
    }

    /**
     * Trigger responses download as XLSX or CSV.
     */
    public function export(Request $request, Form $form)
    {
        $this->authorize('export', $form);

        $format = $request->input('format', 'xlsx');
        if (!in_array($format, ['xlsx', 'csv'])) {
            $format = 'xlsx';
        }

        // Query responses
        $query = FormResponse::with('user')
            ->where('form_id', $form->id)
            ->where('status', 'complete');

        // Apply same filters as DataTables
        if ($request->filled('start_date')) {
            $query->whereDate('submitted_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('submitted_at', '<=', $request->end_date);
        }
        if ($request->filled('version')) {
            $query->where('form_version', $request->version);
        }
        if ($request->filled('flagged')) {
            $query->where('is_flagged', $request->flagged === 'yes');
        }

        $responses = $query->orderBy('submitted_at', 'desc')->get();
        $rowCount = $responses->count();

        $filename = 'responses_' . $form->slug . '_' . Carbon::now()->format('Ymd_His') . '.' . $format;

        // Audit log via helper method
        AuditLog::log('export.triggered', $form, [
            'format'    => $format,
            'filters'   => $request->only(['start_date', 'end_date', 'version', 'flagged']),
            'row_count' => $rowCount,
            'filename'  => $filename,
        ]);

        // Record export job
        Export::create([
            'form_id'      => $form->id,
            'user_id'      => Auth::id(),
            'format'       => $format,
            'filters'      => $request->only(['start_date', 'end_date', 'version', 'flagged']),
            'status'       => 'done',
            'row_count'    => $rowCount,
            'file_path'    => null, // sync export, no file stored
            'expires_at'   => now()->addHours(24),
            'completed_at' => now(),
        ]);

        $exportClass = new ResponsesExport($form, $responses);

        if ($format === 'csv') {
            return Excel::download($exportClass, $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($exportClass, $filename, \Maatwebsite\Excel\Excel::XLSX);
    }
}
