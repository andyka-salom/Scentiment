<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * User management page.
     */
    public function users()
    {
        abort_unless(Auth::user()->hasRole('Super Admin') || Auth::user()->hasPermissionTo('manage_users'), 403);

        $users = \App\Models\User::with('roles')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * Audit log page.
     */
    public function audit(Request $request)
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $logs = $query->paginate(30)->withQueryString();
        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.audit', compact('logs', 'users'));
    }

    /**
     * Global settings page.
     */
    public function settings()
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);
        return view('admin.settings');
    }

    /**
     * Trash (soft-deleted forms).
     */
    public function trash()
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);

        $trashedForms = Form::onlyTrashed()
            ->with('user')
            ->orderBy('deleted_at', 'desc')
            ->paginate(20);

        return view('admin.trash', compact('trashedForms'));
    }

    /**
     * Restore a soft-deleted form.
     */
    public function restore(int $id)
    {
        abort_unless(Auth::user()->hasRole('Super Admin'), 403);

        $form = Form::onlyTrashed()->findOrFail($id);

        // Only allow restore within 30 days
        if ($form->deleted_at->diffInDays(now()) > 30) {
            return redirect()->back()->withErrors('Form sudah melebihi batas 30 hari dan tidak dapat direstore.');
        }

        $form->restore();

        AuditLog::log('form.restored', $form, ['title' => $form->title]);

        return redirect()->back()->with('success', 'Form berhasil direstore!');
    }
}
