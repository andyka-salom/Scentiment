<?php

namespace App\Policies;

use App\Models\Form;
use App\Models\User;
use App\Models\FormShare;

class FormPolicy
{
    /**
     * Determine if a user can view details and responses of a form.
     */
    public function view(User $user, Form $form): bool
    {
        // 1. Super Admin and Admin can view all forms
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        // 2. Creator owns the form
        if ($form->user_id === $user->id) {
            return true;
        }

        // 3. User is direct collaborator
        $isSharedUser = FormShare::where('form_id', $form->id)
            ->where('user_id', $user->id)
            ->exists();
        if ($isSharedUser) {
            return true;
        }

        // 4. Role-based sharing
        $userRoles = $user->roles->pluck('name')->toArray();
        if (!empty($userRoles)) {
            $isSharedRole = FormShare::where('form_id', $form->id)
                ->whereIn('role_name', $userRoles)
                ->exists();
            if ($isSharedRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a user can update form structure or settings.
     */
    public function update(User $user, Form $form): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        if ($form->user_id === $user->id) {
            return true;
        }

        // Editor collab access
        $isEditorUser = FormShare::where('form_id', $form->id)
            ->where('user_id', $user->id)
            ->where('level', 'editor')
            ->exists();
        if ($isEditorUser) {
            return true;
        }

        // Editor role access
        $userRoles = $user->roles->pluck('name')->toArray();
        if (!empty($userRoles)) {
            $isEditorRole = FormShare::where('form_id', $form->id)
                ->whereIn('role_name', $userRoles)
                ->where('level', 'editor')
                ->exists();
            if ($isEditorRole) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a user can delete a form.
     */
    public function delete(User $user, Form $form): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        return $form->user_id === $user->id;
    }

    /**
     * View responses (alias of view).
     */
    public function viewResponses(User $user, Form $form): bool
    {
        return $this->view($user, $form);
    }

    /**
     * Export responses (alias of view).
     */
    public function export(User $user, Form $form): bool
    {
        return $this->view($user, $form);
    }

    /**
     * Delete a single response.
     */
    public function deleteResponse(User $user, Form $form): bool
    {
        if ($user->hasRole(['Super Admin', 'Admin'])) {
            return true;
        }

        return $form->user_id === $user->id;
    }
}
