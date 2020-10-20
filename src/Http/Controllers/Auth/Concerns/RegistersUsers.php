<?php

namespace Diviky\Bright\Http\Controllers\Auth\Concerns;

use Diviky\Bright\Models\Models;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

trait RegistersUsers
{
    public function register($values)
    {
        event(new Registered($user = $this->create($values)));

        return $this->registered($user);
    }

    protected function registered($user)
    {
        //Assign a role to user
        $role = $this->role ?: config('auth.user.role');

        if ($role) {
            $user->assignRole($role);
            $user->assignOwnRole($role);
        }

        $user->assignParent();

        return $user;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function create(array $values)
    {
        $status = config('auth.user.status', $values['status']);

        return Models::user()::create([
            'name'         => $values['name'],
            'email'        => $values['email'],
            'password'     => Hash::make($values['password']),
            'status'       => $status,
            'access_token' => Str::random(36),
        ]);
    }
}
