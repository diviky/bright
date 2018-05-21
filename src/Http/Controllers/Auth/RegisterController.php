<?php

namespace Karla\Http\Controllers\Auth;

use Karla\Http\Controllers\Auth\Traits\Token;
use Karla\Http\Controllers\Controller;
use Karla\Notifications\SendActivationToken;
use Karla\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:100',
            //'username' => 'required|string|regex:([0-9A-Za-z]+)|max:50|unique:auth_users',
            'mobile' => 'required|string|regex:([789][0-9]{9})|unique:auth_users',
            'password' => 'required|string|min:6',
        ]);
    }

    protected function username()
    {
        return 'username';
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'username' => $data['mobile'],
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password']),
            'status' => 0,
            'api_token' => Str::random(32),
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //Assign a role to user
        $user->assignRole('customer');

        $token = $this->saveToken($user);

        $user->notify(new SendActivationToken($token));

        return [
            'next' => $this->redirectPath(),
        ];
    }
}
