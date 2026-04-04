<?php

namespace App\Services\Auth;

use App\Events\UserLoggedIn;
use App\Events\UserRegistered;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function __construct(
        private UserRepository $userRepo,
    ) {}

    

    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $company = Company::create([
                'name'     => $data['company_name'],
                'slug'     => Str::slug($data['company_name']) . '-' . Str::random(5),
                'state_id' => $data['state_id']??null,
                'country'  => $data['country'] ?? 'India',
            ]);

            $user = $this->userRepo->create([
                'company_id'   => $company->id,
                'name'         => $data['name'],
                'email'        => $data['email'],
                'password'     => Hash::make($data['password']),
                'phone_number' => $data['phone_number'] ?? null,
                'state_id'     => $data['state_id']??null,
                'country'      => $data['country'] ?? 'India',
                'image'        => $data['image_path'] ?? null,
            ]);

            $ownerRole = Role::firstOrCreate(
                ['slug' => 'owner'],
                ['name' => 'Owner']
            );

            $user->roles()->syncWithoutDetaching([$ownerRole->id]);

            return $user;
        });
    }
    
    public function login(array $credentials, bool $remember = false): User
    {
        $user = $this->userRepo->findByEmail($credentials['email']);

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.failed')],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account is ' . $user->status . '. Please contact support.'],
            ]);
        }

        Auth::login($user, $remember);

        /*
        |--------------------------------------------------------------------------
        | Store session context
        |--------------------------------------------------------------------------
        */

        session([
            'company_id' => $user->company_id,
            'stores'     => $user->stores->pluck('id')->toArray(),
            'roles'      => $user->roles->pluck('slug')->toArray(),
        ]);

        return $user;
    }


    public function logout(): void
    {
        Auth::logout();
    }
}