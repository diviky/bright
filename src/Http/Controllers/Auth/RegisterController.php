<?php

namespace Karla\Http\Controllers\Auth;

use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Karla\Http\Controllers\Auth\Traits\ColumnsTrait;
use Karla\Http\Controllers\Auth\Traits\Token;
use Karla\Notifications\SendActivationToken;
use Karla\Routing\Controller;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
     */

    use RegistersUsers;
    use Notifiable;
    use Token;
    use ColumnsTrait;

    protected $role;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('karla::auth.register');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => 'required|string|max:100',
            'email'    => 'required|string|email|unique:auth_users',
            'password' => 'required|case_diff|numbers|letters|min:6|max:20',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name'         => $data['name'],
            'email'        => $data['email'],
            'password'     => Hash::make($data['password']),
            'status'       => 0,
            'access_token' => Str::random(36),
        ]);

        return $user;
    }

    /**
     * The user has been registered.
     *
     * @param \Illuminate\Http\Request $request
     * @param mixed                    $user
     *
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //Assign a role to user
        $role = $this->role ?: config('auth.user.role');

        $user->assignRole($role);
        $user->assignOwnRole($role);
        $user->assignParent();

        if (0 == $user->status) {
            $token = $this->saveToken($user);
            $user->notify(new SendActivationToken($token));
        }

        $next = (0 == $user->status) ? 'user.activate' : $this->redirectPath();

        return [
            'redirect' => $next,
            'status'   => 'OK',
            'message'  => \_('Registration success. Redirecting..'),
        ];
    }
}
