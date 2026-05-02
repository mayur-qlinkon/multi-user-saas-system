<?php

namespace App\Services\Hrm;

use App\Models\Hrm\Employee;
use App\Models\User;
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

            // 1. Create the dedicated Employee User Account
            $user = User::create([
                'company_id' => $data['company_id'],
                'name'       => $data['name'],
                'email'      => $data['email'],
                'phone'      => $data['phone'] ?? null,
                'password'   => bcrypt($data['password']),
                'user_type'  => 'employee', // Hardcoded strictly as employee
                'status'     => 'active',
            ]);

            // 2. Attach new user_id and remove user-specific fields before creating Employee
            $data['user_id'] = $user->id;
            unset($data['name'], $data['email'], $data['phone'], $data['password']);

            // 3. Create Employee
            $employee = Employee::create($data);
            $employee->user->stores()->sync($storeIds);

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
            // 1. Extract and update the linked User account details
            $userData = [
                'name'  => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
            ];
            // Only update password if a new one was provided
            if (!empty($data['password'])) {
                $userData['password'] = bcrypt($data['password']);
            }
            $employee->user->update($userData);
            // 2. Unset user fields so they don't break the Employee model update
            unset($data['name'], $data['email'], $data['phone'], $data['password'], $data['store_ids']);
            // 3. Update the Employee record
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

            // Because Employee accounts are strictly 1:1 and separate from system accounts,
            // deleting the employee profile should also delete the linked employee login.
            if ($userId) {
                User::where('id', $userId)->delete();
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
