<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormShare;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class FormController extends Controller
{
    /**
     * Display a listing of forms.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $filter = $request->query('filter');
        
        $query = Form::withCount('responses')->with('user');

        if ($filter === 'shared') {
            // Shared with user directly or via user's role
            $userRoles = $user->roles->pluck('name')->toArray();
            
            $query->where(function ($q) use ($user, $userRoles) {
                $q->whereHas('shares', function ($sq) use ($user, $userRoles) {
                    $sq->where('user_id', $user->id)
                       ->orWhereIn('role_name', $userRoles);
                });
            });
        } else {
            // Own forms (or all forms if Admin/Super Admin and not explicitly filtered)
            if (!$user->hasRole(['Super Admin', 'Admin'])) {
                $query->where('user_id', $user->id);
            }
        }

        $forms = $query->orderBy('created_at', 'desc')->paginate(12);

        return view('forms.index', compact('forms', 'filter'));
    }

    /**
     * Store a newly created form in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $slug = Str::slug($request->title);
        // Check uniqueness and append suffix if needed
        $originalSlug = $slug;
        $count = 1;
        while (Form::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $form = Form::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description,
            'slug' => $slug,
            'status' => Form::STATUS_DRAFT,
            'access_type' => Form::ACCESS_PUBLIC,
            'settings' => [
                'one_response_per_user' => false,
                'show_progress' => true,
                'show_title' => true,
                'show_description' => true,
                'success_message' => 'Terima kasih! Jawaban Anda telah kami terima.',
                'redirect_url' => null,
                'captcha_enabled' => false,
                'show_score' => false,
                'grade_map' => [
                    ['min' => 0, 'max' => 59, 'label' => 'Perlu Perbaikan'],
                    ['min' => 60, 'max' => 79, 'label' => 'Baik'],
                    ['min' => 80, 'max' => 100, 'label' => 'Sangat Baik'],
                ],
                'response_limit' => null,
                'notify_on_response' => false,
                'allow_draft' => false
            ],
        ]);

        AuditLog::log('form.created', $form, ['title' => $form->title, 'slug' => $form->slug]);

        return redirect()->route('forms.build', $form)
            ->with('success', 'Form berhasil dibuat!');
    }

    /**
     * Show form builder canvas.
     */
    public function build(Form $form)
    {
        $this->authorize('update', $form);
        $form->load('fields.options');
        return view('forms.build', compact('form'));
    }

    /**
     * Show form settings.
     */
    public function settings(Form $form)
    {
        $this->authorize('update', $form);
        return view('forms.settings', compact('form'));
    }

    /**
     * Update form settings.
     */
    public function updateSettings(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $request->validate([
            'access_type' => 'required|in:public,internal,token',
            'status' => 'required|in:draft,published,closed,archived',
            'is_assessment' => 'boolean',
            'opens_at' => 'nullable|date',
            'closes_at' => 'nullable|date',
            // settings
            'one_response_per_user' => 'boolean',
            'show_progress' => 'boolean',
            'show_title' => 'boolean',
            'show_description' => 'boolean',
            'success_message' => 'required|string|max:500',
            'redirect_url' => 'nullable|url',
            'captcha_enabled' => 'boolean',
            'show_score' => 'boolean',
            'response_limit' => 'nullable|integer|min:1',
            'notify_on_response' => 'boolean',
            'grade_map' => 'nullable|array',
        ]);

        $settings = $form->settings ?? [];
        $settings['one_response_per_user'] = $request->boolean('one_response_per_user');
        $settings['show_progress'] = $request->boolean('show_progress');
        $settings['show_title'] = $request->boolean('show_title');
        $settings['show_description'] = $request->boolean('show_description');
        $settings['success_message'] = $request->success_message;
        $settings['redirect_url'] = $request->redirect_url;
        $settings['captcha_enabled'] = $request->boolean('captcha_enabled');
        $settings['show_score'] = $request->boolean('show_score');
        $settings['response_limit'] = $request->filled('response_limit') ? (int) $request->response_limit : null;
        $settings['notify_on_response'] = $request->boolean('notify_on_response');

        if ($request->has('grade_map')) {
            $settings['grade_map'] = $request->grade_map;
        }

        $wasPublished = $form->status !== Form::STATUS_PUBLISHED && $request->status === Form::STATUS_PUBLISHED;

        $form->update([
            'access_type' => $request->access_type,
            'status'      => $request->status,
            'is_assessment' => $request->boolean('is_assessment'),
            'opens_at'    => $request->opens_at,
            'closes_at'   => $request->closes_at,
            'published_at' => $request->status === Form::STATUS_PUBLISHED && !$form->published_at ? now() : $form->published_at,
            'settings'    => $settings,
        ]);

        // Audit trail for publish/unpublish
        if ($wasPublished) {
            AuditLog::log('form.published', $form, ['title' => $form->title]);
        }

        return redirect()->back()->with('success', 'Pengaturan berhasil diperbarui!');
    }

    /**
     * Update only form status.
     */
    public function updateStatus(Request $request, Form $form)
    {
        $this->authorize('update', $form);
        $request->validate(['status' => 'required|in:draft,published,closed,archived']);
        
        $wasPublished = $form->status !== Form::STATUS_PUBLISHED && $request->status === Form::STATUS_PUBLISHED;

        $form->update([
            'status' => $request->status,
            'published_at' => $request->status === Form::STATUS_PUBLISHED && !$form->published_at ? now() : $form->published_at,
        ]);

        if ($wasPublished) {
            AuditLog::log('form.published', $form, ['title' => $form->title]);
        }

        return redirect()->back()->with('success', 'Status form berhasil diperbarui!');
    }

    /**
     * Show share and collaboration panel.
     */
    public function share(Form $form)
    {
        $this->authorize('update', $form);
        $shares = $form->shares()->with('user')->get();
        $users = User::where('id', '!=', Auth::id())->get();
        $roles = Role::where('name', '!=', 'Super Admin')->get();

        return view('forms.share', compact('form', 'shares', 'users', 'roles'));
    }

    /**
     * Add or update collaboration.
     */
    public function updateShare(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'role_name' => 'nullable|exists:roles,name',
            'level' => 'required|in:viewer,editor',
        ]);

        if (!$request->user_id && !$request->role_name) {
            return redirect()->back()->withErrors('Pilih Pengguna atau Role untuk dibagikan.');
        }

        FormShare::create([
            'form_id' => $form->id,
            'user_id' => $request->user_id,
            'role_name' => $request->role_name,
            'level' => $request->level,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'Kolaborator berhasil ditambahkan!');
    }

    /**
     * Remove collaboration.
     */
    public function removeShare(Form $form, FormShare $share)
    {
        $this->authorize('update', $form);
        $share->delete();

        return redirect()->back()->with('success', 'Akses kolaborator dihapus!');
    }

    /**
     * Preview form.
     */
    public function preview(Form $form)
    {
        $this->authorize('view', $form);
        $form->load('fields.options');
        return view('forms.preview', compact('form'));
    }

    /**
     * Remove the specified form from storage.
     */
    public function destroy(Form $form)
    {
        $this->authorize('delete', $form);

        AuditLog::log('form.deleted', $form, ['title' => $form->title]);

        $form->delete();

        return redirect()->route('forms.index')
            ->with('success', 'Form berhasil dihapus ke Trash!');
    }
}
