<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\AuditLog;
use App\Models\Response as FormResponse;
use App\Models\ResponseFile;
use App\Services\FormSubmissionService;
use App\Services\FormVersionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Exception;

class ResponseController extends Controller
{
    public function __construct(
        protected FormSubmissionService $submissionService,
        protected FormVersionManager $versionManager
    ) {}

    /**
     * Display backoffice response list view.
     */
    public function index(Form $form)
    {
        $this->authorize('viewResponses', $form);
        $fields = $form->fields()->whereNotIn('type', ['section', 'statement'])->get();
        return view('forms.responses', compact('form', 'fields'));
    }

    /**
     * Yajra DataTables JSON provider.
     */
    public function data(Request $request, Form $form)
    {
        $this->authorize('viewResponses', $form);

        // Fetch query
        $query = FormResponse::where('form_id', $form->id)
            ->where('status', 'complete');

        // Apply filters
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

        return DataTables::eloquent($query)
            ->addColumn('submitted_time', function ($row) {
                return $row->submitted_at ? $row->submitted_at->timezone('Asia/Jakarta')->format('d M Y H:i') : '-';
            })
            ->addColumn('respondent_info', function ($row) {
                return $row->user ? $row->user->name : 'Publik';
            })
            ->addColumn('actions', function ($row) use ($form) {
                $detailUrl = route('forms.responses.show', [$form, $row]);
                $deleteUrl = route('forms.responses.destroy', [$form, $row]);
                return '<div class="flex gap-2">
                    <a href="'.$detailUrl.'" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">Detail</a>
                    <form action="'.$deleteUrl.'" method="POST" onsubmit="return confirm(\'Apakah Anda yakin?\')">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-semibold">Hapus</button>
                    </form>
                </div>';
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    /**
     * Display a specific response detail.
     */
    public function show(Form $form, FormResponse $response)
    {
        $this->authorize('viewResponses', $form);

        // Load historical schema if version is different, else current schema
        $schema = null;
        if ($response->form_version !== $form->current_version) {
            $schema = $this->versionManager->getSchema($form, $response->form_version);
        }

        $fields = [];
        if ($schema && isset($schema['fields'])) {
            $fields = $schema['fields'];
        } else {
            $fields = $form->fields()->with('options')->get()->toArray();
        }

        return view('forms.response-detail', compact('form', 'response', 'fields'));
    }

    /**
     * Delete a response.
     */
    public function destroy(Form $form, FormResponse $response)
    {
        $this->authorize('deleteResponse', $form);

        AuditLog::log('response.deleted', $form, [
            'response_uuid'  => $response->uuid,
            'submitted_at'   => $response->submitted_at?->toISOString(),
        ]);

        $response->delete();

        return redirect()->route('forms.responses', $form)
            ->with('success', 'Respon berhasil dihapus!');
    }

    /**
     * Render public form page.
     */
    public function showPublic(string $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        // Check if closed
        if ($form->status === Form::STATUS_DRAFT) {
            abort(404, 'Form tidak ditemukan atau belum dipublikasi.');
        }
        if ($form->status === Form::STATUS_CLOSED || $form->status === Form::STATUS_ARCHIVED) {
            return view('public.closed', compact('form'));
        }

        // Check schedule
        $now = now();
        if ($form->opens_at && $now->lt($form->opens_at)) {
            abort(403, 'Pendaftaran/form belum dibuka.');
        }
        if ($form->closes_at && $now->gt($form->closes_at)) {
            return view('public.closed', compact('form'));
        }

        // Check access type
        if ($form->access_type === Form::ACCESS_INTERNAL && !auth()->check()) {
            return redirect()->route('login');
        }

        $form->load('fields.options');

        return view('public.form', compact('form'));
    }

    /**
     * Handle public form submit.
     */
    public function submitPublic(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        try {
            $response = $this->submissionService->submit(
                $form,
                $request->all(),
                $request->ip(),
                $request->userAgent(),
                auth()->id()
            );

            return redirect()->route('public.success', [$slug, $response->uuid]);
        } catch (Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show success landing page.
     */
    public function success(string $slug, string $responseUuid)
    {
        $form = Form::where('slug', $slug)->firstOrFail();
        $response = FormResponse::where('uuid', $responseUuid)->firstOrFail();

        return view('public.success', compact('form', 'response'));
    }

    /**
     * Resume draft form submission.
     */
    public function resume(string $slug, string $token)
    {
        $form = Form::where('slug', $slug)->firstOrFail();
        
        // Find incomplete response by uuid (token)
        $response = FormResponse::where('uuid', $token)
            ->where('form_id', $form->id)
            ->where('status', 'draft')
            ->firstOrFail();

        $form->load('fields.options');

        return view('public.form', compact('form', 'response'));
    }

    /**
     * Handle file uploads via public forms.
     */
    public function uploadFile(Request $request, string $slug)
    {
        $form = Form::where('slug', $slug)->firstOrFail();

        $request->validate([
            'file' => 'required|file|max:5120', // Max 5MB
            'field_id' => 'required|exists:form_fields,id',
        ]);

        $uploadedFile = $request->file('file');
        
        // Save to private storage folder
        $path = $uploadedFile->store('private/form_' . $form->id);

        $responseFile = ResponseFile::create([
            'response_id' => 0, // temporary, linked on response submit
            'field_id' => $request->field_id,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size_bytes' => $uploadedFile->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'file_id' => $responseFile->id,
            'original_name' => $responseFile->original_name,
        ]);
    }

    /**
     * Download or view private file (via temporary signed route).
     */
    public function downloadFile(Form $form, ResponseFile $file)
    {
        $this->authorize('viewResponses', $form);

        if (!Storage::exists($file->path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::download($file->path, $file->original_name);
    }
}
