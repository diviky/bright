<?php

namespace Karla\Http\Controllers\Auth;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Karla\Http\Controllers\Auth\Models\Activation;
use Karla\Http\Controllers\Auth\Traits\Token;
use Karla\Notifications\ForgetPassword;
use Karla\Routing\Controller;
use Karla\User;

class ForgotPasswordController extends Controller
{
    use Token;

    protected function username()
    {
        return 'username';
    }

    protected function address()
    {
        return 'mobile';
    }

    public function reset(Request $request)
    {
        if ($request->isMethod('post')) {
            $this->validate($request, [
                $this->username() => 'required|exists:auth_users',
            ]);

            $user = User::where($this->username(), $request->input($this->username()))->first();

            $token = $this->saveToken($user);

            session(['reset-token' => $this->getTokenId()]);

            $user->notify(new ForgetPassword($token));

            return [
                'status'   => 'success',
                'message'  => __('Verification code sent to your registered :username.', ['username' => $this->address()]),
                'redirect' => 'password.verify',
            ];
        }

        return view('auth.passwords.token');
    }

    public function resend(Request $request)
    {
        $id = session('reset-token');

        if (is_null($id)) {
            return [
                'message'  => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        $activation = Activation::where('id', $id)->first();

        if (is_null($activation)) {
            return [
                'message'  => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        $user = User::where('id', $activation->user_id)->first();

        $user->notify(new ForgetPassword($activation->token));

        return [
            'status'  => 'success',
            'message' => __('Verification code resent to your registered  :username.', ['username' => $this->address()]),
            'next'    => [
                'back' => true,
            ],
        ];
    }

    public function verify(Request $request)
    {
        $id = session('reset-token');

        if (is_null($id)) {
            return [
                'message'  => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        if ($request->isMethod('get')) {
            return view('auth.verify');
        }

        $this->validate($request, [
            'token' => 'required',
        ]);

        $token = $request->input('token');

        $activation = Activation::where('token', $token)
            ->where('id', $id)
            ->first();

        if (empty($activation)) {
            return [
                'status'   => 'ERROR',
                'message'  => 'Invalid verification code.',
                'redirect' => 'password.verify',
            ];
        }

        session(['forget-userid' => $activation->user_id]);

        $activation->delete();
        session()->forget('reset-token');

        return [
            'status'   => 'OK',
            'message'  => 'Account verified successfully.',
            'redirect' => 'password.change',
        ];
    }

    public function change(Request $request)
    {
        $id = session('forget-userid');

        if (is_null($id)) {
            return redirect()->route('password.reset')
                ->with('message', 'Invalid request');
        }

        if ($request->isMethod('get')) {
            return view('auth.passwords.change');
        }

        $this->validate($request, [
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::where('id', $id)->first();

        if ($this->resetPassword($user, $request->input('password'))) {
            session()->forget('forget-userid');

            return redirect()->route('home');
        }

        return redirect()->back()
                ->with('status', 'error')
                ->with('message', 'Unable to reset password');
    }

    /**
     * Reset the given user's password.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string                                      $password
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        if (!$user->save()) {
            return false;
        }

        event(new PasswordReset($user));

        Auth::guard()->login($user);

        return true;
    }
}
