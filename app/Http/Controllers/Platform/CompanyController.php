<?php
namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CompanyStoreRequest;
use App\Http\Requests\Platform\CompanyUpdateRequest;
use App\Models\Company;
use App\Models\State;
use App\Services\Platform\CompanyOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    public function __construct(
        private CompanyOnboardingService $companyService
    ) {}

    public function index()
    {
        $companies = Company::withCount(['users', 'stores'])
            ->with(['users' => fn($q) => $q->whereHas('roles', fn($r) => $r->where('slug', 'owner'))->limit(1)])
            ->latest()
            ->get();

        return view('platform.companies.index', compact('companies'));
    }

    public function create()
    {
        $states = State::where('is_active', true)->orderBy('name')->get();
        return view('platform.companies.create', compact('states'));
    }

    public function store(CompanyStoreRequest $request)
    {
        try {
            $this->companyService->onboard($request->validated());
            return redirect()->route('platform.companies.index')
                ->with('success', 'Company & Owner onboarded successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Onboarding failed: ' . $e->getMessage());
        }
    }

    public function show(Company $company)
    {
        $company->load([
            'users.roles',
            'stores',
            'subscription.plan',
            'state',
        ]);

        $owner = $company->users->first(fn($u) => $u->roles->contains('slug', 'owner'));

        return view('platform.companies.show', compact('company', 'owner'));
    }

    public function edit(Company $company)
    {
        $states = State::where('is_active', true)->orderBy('name')->get();
        return view('platform.companies.edit', compact('company', 'states'));
    }

    public function update(CompanyUpdateRequest $request, Company $company)
    {
        try {
            $this->companyService->update($company, $request->validated());
            return redirect()->route('platform.companies.show', $company)
                ->with('success', 'Company updated successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy(Company $company)
    {
        // Hard guard: never allow deletion of a company that owns super admin accounts.
        $hasSuperAdmin = $company->users()
            ->where(function ($q) {
                $q->where('is_super_admin', true)
                    ->orWhereHas('roles', fn ($r) => $r->where('slug', 'super_admin'));
            })
            ->exists();

        if ($hasSuperAdmin) {
            $msg = "Cannot delete \"{$company->name}\" — it contains the platform super admin account. This is a system-protected company.";

            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        try {
            $this->companyService->delete($company);

            if (request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Company terminated successfully.']);
            }

            return redirect()->route('platform.companies.index')
                ->with('success', 'Company terminated successfully!');
        } catch (\Exception $e) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Termination failed: ' . $e->getMessage()], 500);
            }

            return back()->with('error', 'Termination failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: check if a slug is available.
     * GET platform/companies/{company}/slug-check?slug=xxx
     * Pass company id = 0 (or a dummy) for create context, real id for edit context.
     */
    public function slugCheck(Request $request, ?Company $company = null)
    {
        $slug = Str::slug($request->query('slug', ''));

        if (empty($slug)) {
            return response()->json(['available' => false, 'message' => 'Slug cannot be empty.']);
        }

        $query = Company::where('slug', $slug);

        // Only exclude self when actually editing an existing company
        if ($company && $company->exists) {
            $query->where('id', '!=', $company->id);
        }

        $taken = $query->exists();

        return response()->json([
            'available' => !$taken,
            'slug'      => $slug,
            'message'   => $taken ? 'This slug is already taken.' : 'Slug is available.',
        ]);
    }
}
