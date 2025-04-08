<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth;

use Diviky\Bright\Http\Controllers\Auth\Concerns\AuthenticatesUsers;
use Diviky\Bright\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Show the application's login form.
     */
    public function showLoginForm(): \Illuminate\Contracts\View\View
    {
        return view('bright::auth.login');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return config('auth.columns.username', 'email');
    }

    /**
     * Get the throttle key for the given request.
     */
    protected function throttleKey(Request $request): ?string
    {
        if (config('auth.throttle_key') == 'ip') {
            return $request->ip();
        }

        return Str::lower($request->input($this->username())) . '|' . $request->ip();
    }
}
