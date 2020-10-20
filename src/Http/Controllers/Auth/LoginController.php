<?php

namespace Diviky\Bright\Http\Controllers\Auth;

use Diviky\Bright\Routing\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginController extends Controller
{
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

    protected $maxAttempts  = 5;
    protected $decayMinutes = 10;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('bright::auth.login');
    }

    protected function username()
    {
        return config('auth.columns.username', 'email');
    }

    /**
     * Get the throttle key for the given request.
     *
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        if ('ip' == config('auth.throttle_key')) {
            return $request->ip();
        }

        return Str::lower($request->input($this->username())) . '|' . $request->ip();
    }
}
