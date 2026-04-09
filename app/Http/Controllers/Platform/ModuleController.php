<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ModuleController extends Controller
{
    /**
     * Display the single-page CRUD view.
     */
    public function index()
    {
        // Fetch directly from the model
        $modules = Module::latest()->get();

        return view('platform.modules', compact('modules'));
    }

    /**
     * Store a newly created module.
     */
    public function store(Request $request)
    {
        // Inline Validation
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:modules,slug'],
        ]);

        // Use provided slug, or fallback to auto-generating from the name
        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        // Safely handle HTML checkbox boolean
        $data['is_active'] = $request->has('is_active');

        Module::create($data);

        return redirect()->route('platform.modules.index')
            ->with('success', 'Module created successfully.');
    }

    /**
     * Update the specified module.
     */
    public function update(Request $request, Module $module)
    {
        // Inline Validation (Ignoring current module's slug)
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('modules', 'slug')->ignore($module->id),
            ],
        ]);

        // Use provided slug, or fallback to auto-generating from the name
        $data['slug'] = ! empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['name']);

        // Safely handle HTML checkbox boolean
        $data['is_active'] = $request->has('is_active');

        $module->update($data);

        return redirect()->route('platform.modules.index')
            ->with('success', 'Module updated successfully.');
    }

    /**
     * Remove the specified module.
     */
    public function destroy(Module $module)
    {
        $module->delete();

        return back()->with('success', 'Module deleted successfully.');
    }
}
