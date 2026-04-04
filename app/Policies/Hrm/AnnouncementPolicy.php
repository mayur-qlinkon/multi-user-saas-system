<?php

namespace App\Policies\Hrm;

use App\Models\Hrm\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    /**
     * Owner / super-admin bypass — unrestricted access to everything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['owner', 'super-admin'])) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission(['view-announcements', 'manage-announcements']);
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->hasAnyPermission(['view-announcements', 'manage-announcements']);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-announcements');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        if (!$user->hasPermissionTo('manage-announcements')) {
            return false;
        }

        // Published announcements need extra permission to edit
        if ($announcement->status === Announcement::STATUS_PUBLISHED) {
            return $user->hasPermissionTo('edit-published-announcements');
        }

        return true; // draft / scheduled — freely editable
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        if (!$user->hasPermissionTo('manage-announcements')) {
            return false;
        }

        if ($announcement->status === Announcement::STATUS_PUBLISHED) {
            return $user->hasPermissionTo('delete-published-announcements');
        }

        return true;
    }

    public function restore(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('manage-announcements');
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('force-delete-announcements');
    }

    public function publish(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('publish-announcements')
            && in_array($announcement->status, [
                Announcement::STATUS_DRAFT,
                Announcement::STATUS_SCHEDULED,
            ]);
    }

    public function unpublish(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('publish-announcements')
            && $announcement->status === Announcement::STATUS_PUBLISHED;
    }

    public function duplicate(User $user, Announcement $announcement): bool
    {
        return $user->hasPermissionTo('manage-announcements');
    }
}