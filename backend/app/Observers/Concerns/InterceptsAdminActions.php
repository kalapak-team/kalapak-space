<?php

namespace App\Observers\Concerns;

use App\Exceptions\ActionInterceptedException;
use App\Models\ApprovalRequest;
use App\Models\UserPermission;
use Illuminate\Database\Eloquent\Model;

/**
 * Used by observers on Project, Tag, and BlogCategory.
 * When an admin lacks the per-resource permission, the mutation is intercepted
 * and queued as a pending approval request for Super Admin review.
 *
 * Each concrete observer must declare:
 *   protected string $resource = 'projects'; // or 'categories' or 'tags'
 */
trait InterceptsAdminActions
{
    /**
     * Returns true if the action should be intercepted (queued for approval).
     * $action: 'create' | 'update' | 'delete'
     */
    private function shouldIntercept(string $action): bool
    {
        $user = request()->user();

        if (!$user || !$user->isAdmin()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return false;
        }

        $permission = UserPermission::where('user_id', $user->id)
            ->where('resource', $this->resource)
            ->first();

        $column = 'can_' . $action;
        return !($permission?->$column ?? false);
    }

    public function creating(Model $model): void
    {
        if (!$this->shouldIntercept('create')) {
            return;
        }

        ApprovalRequest::create([
            'requested_by' => request()->user()->id,
            'action' => 'create',
            'subject_type' => get_class($model),
            'subject_id' => null,
            'payload' => $model->getAttributes(),
            'status' => 'pending',
        ]);

        throw new ActionInterceptedException();
    }

    public function updating(Model $model): void
    {
        if (!$this->shouldIntercept('update')) {
            return;
        }

        ApprovalRequest::create([
            'requested_by' => request()->user()->id,
            'action' => 'update',
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'payload' => $model->getDirty(),
            'status' => 'pending',
        ]);

        throw new ActionInterceptedException();
    }

    public function deleting(Model $model): void
    {
        if (!$this->shouldIntercept('delete')) {
            return;
        }

        ApprovalRequest::create([
            'requested_by' => request()->user()->id,
            'action' => 'delete',
            'subject_type' => get_class($model),
            'subject_id' => $model->getKey(),
            'payload' => ['id' => $model->getKey(), 'title' => $model->title ?? $model->name ?? $model->getKey()],
            'status' => 'pending',
        ]);

        throw new ActionInterceptedException();
    }
}

