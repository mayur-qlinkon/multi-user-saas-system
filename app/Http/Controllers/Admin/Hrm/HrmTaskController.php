<?php

namespace App\Http\Controllers\Admin\Hrm;

use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Hrm\HrmTask;
use App\Models\Hrm\HrmTaskAttachment;
use App\Services\Hrm\HrmTaskService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class HrmTaskController extends Controller
{
    public function __construct(
        protected HrmTaskService $taskService
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['status', 'priority', 'project', 'employee_id', 'overdue', 'search', 'per_page']);
        $tasks = $this->taskService->getList($filters);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'data' => $tasks]);
        }

        $employees = Employee::active()->with('user')->get();

        return view('admin.hrm.tasks.index', compact('tasks', 'employees', 'filters'));
    }

    public function create()
    {
        $employees = Employee::active()->with('user')->get();
        return view('admin.hrm.tasks.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['exists:employees,id'],
            'primary_assignee' => ['nullable', 'exists:employees,id'],
        ]);

        try {
            $task = $this->taskService->create($validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Task created.', 'data' => $task]);
            }

            return redirect()->route('admin.hrm.tasks.show', $task)
                ->with('success', 'Task created successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit(HrmTask $task)
    {
        $employees = Employee::active()->with('user')->get();
        $task->load('assignees');

        return view('admin.hrm.tasks.edit', compact('task', 'employees'));
    }

    public function show(HrmTask $task)
    {
        $task->load([
            'createdByUser', 'assignees.user',
            'attachments.uploadedByUser',
            'comments.user', 'comments.replies.user',
        ]);

        return view('admin.hrm.tasks.show', compact('task'));
    }

    public function update(Request $request, HrmTask $task)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:50'],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'start_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'progress_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['exists:employees,id'],
            'primary_assignee' => ['nullable', 'exists:employees,id'],
        ]);

        try {
            $task = $this->taskService->update($task, $validated);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Task updated.', 'data' => $task]);
            }

            return redirect()->route('admin.hrm.tasks.show', $task)
                ->with('success', 'Task updated successfully.');
        } catch (\Exception $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(HrmTask $task)
    {
        $this->taskService->delete($task);
        return response()->json(['success' => true, 'message' => 'Task deleted.']);
    }

    /**
     * Update task status.
     */
    public function updateStatus(Request $request, HrmTask $task)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(HrmTask::STATUS_LABELS))],
            'note' => ['nullable', 'string'],
        ]);

        try {
            $task = $this->taskService->updateStatus($task, $validated['status'], $validated['note'] ?? null);
            return response()->json(['success' => true, 'message' => 'Task status updated.', 'data' => $task]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Add comment to task.
     */
    public function addComment(Request $request, HrmTask $task)
    {
        $validated = $request->validate([
            'body' => ['required', 'string'],
            'parent_id' => ['nullable', 'exists:hrm_task_comments,id'],
        ]);

        $comment = $this->taskService->addComment($task, $validated);

        return response()->json(['success' => true, 'message' => 'Comment added.', 'data' => $comment->load('user')]);
    }

    /**
     * Upload attachment to task.
     */
    public function addAttachment(Request $request, HrmTask $task)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $attachment = $this->taskService->addAttachment($task, $request->file('file'));

        return response()->json(['success' => true, 'message' => 'Attachment uploaded.', 'data' => $attachment]);
    }

    /**
     * Download an attachment.
     */
    public function downloadAttachment(HrmTaskAttachment $attachment)
    {
        abort_if(! Storage::disk('public')->exists($attachment->file_path), 404, 'File not found.');

        return response()->download(
            Storage::disk('public')->path($attachment->file_path),
            $attachment->file_name ?? $attachment->original_name
        );
    }

    /**
     * Delete attachment.
     */
    public function deleteAttachment(HrmTaskAttachment $attachment)
    {
        $this->taskService->deleteAttachment($attachment);

        return response()->json(['success' => true, 'message' => 'Attachment deleted.']);
    }
}
