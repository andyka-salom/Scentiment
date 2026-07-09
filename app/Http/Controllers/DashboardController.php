<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Export;
use App\Models\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Forms accessible by this user
        $accessibleFormIds = $this->getAccessibleFormIds($user);

        // KPI: active forms
        $activeForms = Form::whereIn('id', $accessibleFormIds)
            ->where('status', Form::STATUS_PUBLISHED)
            ->count();

        // KPI: responses this week
        $weeklyResponses = \App\Models\Response::whereIn('form_id', $accessibleFormIds)
            ->where('status', 'complete')
            ->whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        // Forms closing soon (within 7 days)
        $closingSoon = Form::whereIn('id', $accessibleFormIds)
            ->where('status', Form::STATUS_PUBLISHED)
            ->whereNotNull('closes_at')
            ->where('closes_at', '>', now())
            ->where('closes_at', '<=', now()->addDays(7))
            ->orderBy('closes_at')
            ->limit(5)
            ->get();

        // My own forms summary
        $myForms = Form::where('user_id', $user->id)
            ->withCount('responses')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Recent audit activity (Super Admin/Admin only)
        $recentActivity = [];
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            $recentActivity = AuditLog::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        // Recent responses across accessible forms
        $recentResponses = \App\Models\Response::whereIn('form_id', $accessibleFormIds)
            ->with('form')
            ->where('status', 'complete')
            ->orderBy('submitted_at', 'desc')
            ->limit(8)
            ->get();

        return view('dashboard', compact(
            'activeForms',
            'weeklyResponses',
            'closingSoon',
            'myForms',
            'recentActivity',
            'recentResponses'
        ));
    }

    private function getAccessibleFormIds($user): array
    {
        $userRoles = $user->roles->pluck('name')->toArray();

        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return Form::pluck('id')->toArray();
        }

        // Own forms + shared forms
        $ownIds = Form::where('user_id', $user->id)->pluck('id')->toArray();
        $sharedIds = \App\Models\FormShare::where('user_id', $user->id)
            ->orWhereIn('role_name', $userRoles)
            ->pluck('form_id')
            ->toArray();

        return array_unique(array_merge($ownIds, $sharedIds));
    }
}
