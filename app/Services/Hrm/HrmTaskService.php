<?php

namespace App\Services\Hrm;

use App\Notifications\Hrm\TaskAssignedNotification;

use App\Models\Hrm\Employee;
use App\Models\Hrm\HrmTask;
use App\Models\Hrm\HrmTaskAssignment;
use App\Models\Hrm\HrmTaskAttachment;
use App\Models\Hrm\HrmTaskComment;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class HrmTaskService
{
    public function create(array $data): HrmTask
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            $task = HrmTask::create($data);

            // 1. Grab the array, or default to an empty array
            $assignees = $data['assignees'] ?? [];

            // 2. If a primary assignee exists, push them into the array
            if (! empty($data['primary_assignee'])) {
                $assignees[] = $data['primary_assignee'];
            }

            // 3. Clean the array to prevent duplicate database entries
            // (in case the user selected the same person in both UI dropdowns)
            $assignees = array_unique($assignees);

            // 4. Now run your sync function with the clean, merged array
            if (! empty($assignees)) {
                $this->syncAssignees($task, $assignees, $data['primary_assignee'] ?? null);
            }

            return $task->load('assignees');
        });
    }

    public function update(HrmTask $task, array $data): HrmTask
    {
        return DB::transaction(function () use ($task, $data) {
            $task->update($data);

            // 1. Check if the frontend sent assignment data in this update
            if (array_key_exists('assignees', $data) || array_key_exists('primary_assignee', $data)) {

                // 2. Grab the array, or default to empty
                $assignees = $data['assignees'] ?? [];

                // 3. Push the primary assignee into the array if one was submitted
                if (! empty($data['primary_assignee'])) {
                    $assignees[] = $data['primary_assignee'];
                }

                // 4. Clean duplicates
                $assignees = array_unique($assignees);

                // 5. Run the sync (even if $assignees is empty, this will correctly clear out old assignments!)
                $this->syncAssignees($task, $assignees, $data['primary_assignee'] ?? null);
            }

            return $task->fresh()->load('assignees');
        });
    }

    public function delete(HrmTask $task): void
    {
        DB::transaction(fn () => $task->delete());
    }

    /**
     * Update task status with transition validation.
     */
    public function updateStatus(HrmTask $task, string $newStatus, ?string $note = null): HrmTask
    {
        if (! $task->canTransitionTo($newStatus)) {
            $allowed = implode(', ', HrmTask::STATUS_TRANSITIONS[$task->status] ?? []);
            throw new InvalidArgumentException(
                "Cannot transition from '{$task->status}' to '{$newStatus}'. Allowed: {$allowed}"
            );
        }

        return DB::transaction(function () use ($task, $newStatus, $note) {
            $updateData = ['status' => $newStatus];

            if ($newStatus === HrmTask::STATUS_COMPLETED) {
                $updateData['completed_at'] = now();
                $updateData['progress_percent'] = 100;
                if ($note) {
                    $updateData['completion_note'] = $note;
                }
            }

            $task->update($updateData);

            // Add system comment for status change
            HrmTaskComment::create([
                'hrm_task_id' => $task->id,
                'user_id' => Auth::id(),
                'body' => 'Status changed to '.HrmTask::STATUS_LABELS[$newStatus],
                'is_system' => true,
            ]);

            return $task->fresh();
        });
    }

    /**
     * Assign employees to a task.
     */
    public function syncAssignees(HrmTask $task, array $employeeIds, ?int $primaryId = null): void
    {
        // Capture existing state to prevent spamming notifications on task update
        $existingPrimaryId = $task->assignments()->where('is_primary', true)->value('employee_id');
        $existingEmployeeIds = $task->assignments()->pluck('employee_id')->toArray();

        // Remove existing assignments
        $task->assignments()->delete();

        $newAssignees = [];

        foreach ($employeeIds as $employeeId) {
            $isPrimary = ($employeeId == $primaryId);

            HrmTaskAssignment::create([
                'hrm_task_id' => $task->id,
                'employee_id' => $employeeId,
                'assigned_by' => Auth::id(),
                'is_primary' => $isPrimary,
            ]);

            // Only notify if they are a brand-new assignee OR they were just upgraded to Primary
            if (!in_array($employeeId, $existingEmployeeIds) || ($isPrimary && $employeeId != $existingPrimaryId)) {
                $newAssignees[$employeeId] = $isPrimary;
            }
        }

        // Fire notifications if there is anyone new to notify
        if (!empty($newAssignees)) {
            $this->notifyAssignees($task, $newAssignees);
        }
    }

    /**
     * Send database notifications to assigned employees.
     */
    protected function notifyAssignees(HrmTask $task, array $employeeData): void
    {
        $employees = Employee::whereIn('id', array_keys($employeeData))->with('user')->get();

        foreach ($employees as $emp) {
            if (! $emp->user) continue;

            $isPrimary = $employeeData[$emp->id] ?? false;
            Log::info('Sending notification', [
                'employee_id' => $emp->id,
                'user_id' => $emp->user->id,
                'is_primary' => $isPrimary,
            ]);

             try {
                $emp->user->notify(new TaskAssignedNotification($task, $isPrimary));

                Log::info('Notification sent successfully', [
                    'employee_id' => $emp->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send notification', [
                    'employee_id' => $emp->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Add attachment to a task.
     */
    public function addAttachment(HrmTask $task, UploadedFile $file): HrmTaskAttachment
    {
        $path = $file->store('hrm/task-attachments/'.$task->id, 'public');

        return HrmTaskAttachment::create([
            'hrm_task_id' => $task->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);
    }

    /**
     * Delete an attachment.
     */
    public function deleteAttachment(HrmTaskAttachment $attachment): void
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();
    }

    /**
     * Add comment to a task.
     */
    public function addComment(HrmTask $task, array $data): HrmTaskComment
    {
        return HrmTaskComment::create([
            'hrm_task_id' => $task->id,
            'user_id' => Auth::id(),
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
            'is_system' => false,
        ]);
    }

    /**
     * Get filtered task list.
     */
    public function getList(array $filters): LengthAwarePaginator
    {
        $query = HrmTask::with(['createdByUser', 'assignees.user']);

        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }
        if (! empty($filters['priority'])) {
            $query->byPriority($filters['priority']);
        }
        if (! empty($filters['project'])) {
            $query->where('project', $filters['project']);
        }
        if (! empty($filters['employee_id'])) {
            $query->whereHas('assignments', fn ($q) => $q->where('employee_id', $filters['employee_id']));
        }
        if (! empty($filters['overdue'])) {
            $query->overdue();
        }
        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('project', 'like', '%'.$filters['search'].'%')
                    ->orWhere('category', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->orderBy('due_date')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'medium', 'low')")
            ->paginate($filters['per_page'] ?? 25)
            ->withQueryString();
    }
}
