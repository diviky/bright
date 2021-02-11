<?php

namespace Diviky\Bright\Http\Controllers\Auth;

use Diviky\Bright\Routing\Controller;
use Illuminate\Notifications\Notifiable;
use Diviky\Bright\Http\Controllers\Auth\Traits\Token;
use Diviky\Bright\Http\Controllers\Auth\Traits\ColumnsTrait;
use Diviky\Bright\Http\Controllers\Auth\Concerns\RegistersUsers;

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
        return view('bright::auth.register');
    }
}
