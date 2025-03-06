<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth\Concerns;

use Diviky\Bright\Events\Registered as AccountCreated;
use Diviky\Bright\Models\Models;
use Diviky\Bright\Notifications\SendActivationToken;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

trait RegistersUsers
{
    /**
     * Register the user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    public function registers(array $values)
    {
        $user = $this->create($values);
        event(new Registered($user));
        event(new AccountCreated($user, $values));

        $this->registered($user);

        return $user;
    }

    /**
     * Handle a registration request for the application.
     *
     * @return string[]
     *
     * @psalm-return array{message: array<array-key, mixed>|string, redirect: string, status: "OK"}
     */
    public function register(Request $request): array
    {
        $values = $request->all();
        $this->validator($values)->validate();

        $user = $this->registers($values);

        $this->guard()->login($user);

        $next = ($user->status == 0) ? 'activate' : $this->redirectPath();

        return [
            'redirect' => $next,
            'status' => 'OK',
            'message' => trans('Registration success.'),
        ];
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        try {
            $path = $this->session->pull('url.intended');

            if ($path) {
                return $path;
            }
        } catch (\Exception $e) {
            report($e);
        }

        if (\method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return \property_exists($this, 'redirectTo') ? $this->redirectTo : '/';
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  mixed  $user
     */
    protected function registered($user): void
    {
        if ($user->status == 0) {
            $token = $this->saveToken($user);
            $user->notify(new SendActivationToken($token));
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $rules)
    {
        return Validator::make($rules, [
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|unique:' . config('bright.table.users'),
            'password' => ['required', Password::min(8)->max(20)->mixedCase()->numbers()->symbols()->uncompromised()],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    protected function create(array $values)
    {
        $status = $values['status'] ?? config('auth.user.status', 0);

        return Models::user()::create([
            'name' => $values['name'],
            'email' => $values['email'],
            'password' => Hash::make($values['password']),
            'status' => $status,
            'access_token' => Str::random(60),
        ]);
    }
}
