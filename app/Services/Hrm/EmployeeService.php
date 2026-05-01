<?php

namespace App\Services\Hrm;

use App\Models\Hrm\Employee;
use App\Models\Hrm\User;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    public function __construct(protected ImageUploadService $imageService) {}

    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = Auth::id();
            $data['company_id'] = Auth::user()->company_id;
            $storeIds = $this->normalizeStoreIds($data);

            if (isset($data['photo'])) {
                $data['photo'] = $this->imageService->upload($data['photo'], 'employees/photos', [
                    'width' => 400,
                    'height' => 400,
                    'crop' => true,
                ]);
            }

            $data['id_proof'] = $this->uploadDocument($data['id_proof'] ?? null, 'employees/documents');
            $data['address_proof'] = $this->uploadDocument($data['address_proof'] ?? null, 'employees/documents');

            unset($data['store_ids']);

            $employee = Employee::create($data);
            $employee->user->stores()->sync($storeIds);

            // If the linked user has no non-employee, non-customer roles,
            // reclassify them as 'employee' type so they stop consuming a user_limit seat.
            $linkedUser = $employee->user;
            $hasFullSystemRole = $linkedUser->roles()
                ->whereNotIn('slug', ['employee', 'customer'])
                ->exists();

            if (! $hasFullSystemRole) {
                $linkedUser->update(['user_type' => 'employee']);
            }

            return $employee->fresh(['user.stores', 'shift']);
        });
    }

    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            $storeIds = $this->normalizeStoreIds($data, $employee);

            if (isset($data['photo'])) {
                $this->imageService->delete($employee->photo);
                $data['photo'] = $this->imageService->upload($data['photo'], 'employees/photos', [
                    'width' => 400,
                    'height' => 400,
                    'crop' => true,
                ]);
            }

            if (isset($data['id_proof'])) {
                $this->deleteDocument($employee->id_proof);
                $data['id_proof'] = $this->uploadDocument($data['id_proof'], 'employees/documents');
            }

            if (isset($data['address_proof'])) {
                $this->deleteDocument($employee->address_proof);
                $data['address_proof'] = $this->uploadDocument($data['address_proof'], 'employees/documents');
            }

            unset($data['store_ids']);

            $employee->update($data);
            $employee->user->stores()->sync($storeIds);

            return $employee->fresh(['user.stores', 'shift']);
        });
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            // Capture user_id before soft-delete removes the relationship.
            $userId = $employee->user_id;

            $employee->delete();

            // Revert the user to 'full' type so they are counted correctly.
            // They no longer have an active employee profile, so they would
            // consume a user_limit seat if they retain any system access.
            if ($userId) {
                User::where('id', $userId)->update(['user_type' => 'full']);
            }
        });
    }

    protected function normalizeStoreIds(array &$data, ?Employee $employee = null): array
    {
        $existingStoreIds = $employee?->user?->stores()->pluck('stores.id')->all() ?? [];
        $storeIds = collect($data['store_ids'] ?? $existingStoreIds)
            ->filter()
            ->map(fn ($storeId) => (int) $storeId);

        if (! empty($data['store_id'])) {
            $storeIds->prepend((int) $data['store_id']);
        }

        $storeIds = $storeIds->unique()->values()->all();

        if (empty($data['store_id']) && ! empty($storeIds)) {
            $data['store_id'] = $storeIds[0];
        }

        return $storeIds;
    }

    private function uploadDocument($file, string $path): ?string
    {
        if (! $file) {
            return null;
        }

        return $file->store($path, 'public');
    }

    private function deleteDocument(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
