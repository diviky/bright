<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth;

use Diviky\Bright\Http\Controllers\Auth\Concerns\ColumnsTrait;
use Diviky\Bright\Http\Controllers\Auth\Concerns\RegistersUsers;
use Diviky\Bright\Http\Controllers\Auth\Concerns\Token;
use Diviky\Bright\Routing\Controller;
use Illuminate\Notifications\Notifiable;

class RegisterController extends Controller
{
    use ColumnsTrait;
    use Notifiable;

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
    use Token;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Show the application registration form.
     */
    public function showRegistrationForm(): \Illuminate\Contracts\View\View
    {
        return view('bright::auth.register');
    }
}
