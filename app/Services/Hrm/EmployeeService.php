<?php

namespace App\Services\Hrm;

use App\Models\Hrm\Employee;
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

            // 1. Profile Photo -> Use ImageUploadService (Crop to 400x400, optimize)
            if (isset($data['photo'])) {
                $data['photo'] = $this->imageService->upload($data['photo'], 'employees/photos', [
                    'width'  => 400,
                    'height' => 400,
                    'crop'   => true, 
                ]);
            }

            // 2. Official Documents -> Use Native Storage (100% original quality, allows PDF)
            $data['id_proof']      = $this->uploadDocument($data['id_proof'] ?? null, 'employees/documents');
            $data['address_proof'] = $this->uploadDocument($data['address_proof'] ?? null, 'employees/documents');

            return Employee::create($data);
        });
    }

    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            
            // Photo Update
            if (isset($data['photo'])) {
                $this->imageService->delete($employee->photo);
                $data['photo'] = $this->imageService->upload($data['photo'], 'employees/photos', [
                    'width' => 400, 'height' => 400, 'crop' => true
                ]);
            }
            
            // Document Updates
            if (isset($data['id_proof'])) {
                $this->deleteDocument($employee->id_proof);
                $data['id_proof'] = $this->uploadDocument($data['id_proof'], 'employees/documents');
            }
            
            if (isset($data['address_proof'])) {
                $this->deleteDocument($employee->address_proof);
                $data['address_proof'] = $this->uploadDocument($data['address_proof'], 'employees/documents');
            }

            $employee->update($data);
            return $employee->fresh();
        });
    }

    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            // Optional: Delete physical files
            // $this->imageService->delete($employee->photo);
            // $this->deleteDocument($employee->id_proof);
            // $this->deleteDocument($employee->address_proof);
            
            $employee->delete();
        });
    }

    // ── Helper Methods for Raw Documents ──

    private function uploadDocument($file, string $path): ?string
    {
        if (!$file) return null;
        
        // Native Laravel storage. Preserves 100% quality and keeps exact file format (PDF, JPG, etc.)
        return $file->store($path, 'public'); 
    }

    private function deleteDocument(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}