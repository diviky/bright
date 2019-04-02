<?php

namespace Karla\Http\Controllers\Auth;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Karla\Routing\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    protected $maxAttempts  = 5;
    protected $decayMinutes = 10;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
     */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    protected function username()
    {
        return 'username';
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        if (config('auth.throttle_key') == 'ip') {
            return $request->ip();
        } else {
            return Str::lower($request->input($this->username())) . '|' . $request->ip();
        }
    }
}
