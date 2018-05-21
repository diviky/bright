<?php

namespace Karla\Http\Controllers\Auth;

use Karla\Http\Controllers\Auth\Models\Activation;
use Karla\Http\Controllers\Auth\Traits\Token;
use Karla\Http\Controllers\Controller;
use Karla\Notifications\SendActivationToken;
use Karla\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    use Token;

    public function reset(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.passwords.username');
        }

        $this->validate($request, [
            'username' => 'required|exists:auth_users',
        ]);

        $user = User::where('username', $request->input('username'))->first();

        $token = $this->saveToken($user);

        session(['reset-token' => $this->getTokenId()]);

        $user->notify(new SendActivationToken($token));

        return [
            'status' => 'success',
            'message' => 'Verification code sent to your registered mobile number!',
            'redirect' => 'password.verify',
        ];
    }

    public function resend(Request $request)
    {
        $id = session('reset-token');

        if (is_null($id)) {
            return [
                'message' => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        $activation = Activation::where('id', $id)->first();

        if (is_null($activation)) {
            return [
                'message' => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        $user = User::where('id', $activation->user_id)->first();

        $user->notify(new SendActivationToken($activation->token));

        return [
            'status' => 'success',
            'message' => 'Verification code resent to your registered mobile number!',
            'next' => [
                'back' => true,
            ],
        ];
    }

    public function verify(Request $request)
    {
        $id = session('reset-token');

        if (is_null($id)) {
            return [
                'message' => 'Invalid request',
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
                'status' => 'error',
                'message' => 'Invalid verification code.',
                'next' => 'password.verify',
            ];
        }

        session(['forget-userid' => $activation->user_id]);

        $activation->delete();
        session()->forget('reset-token');

        return redirect()->route('password.change');
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
        } else {
            return redirect()->back()
                ->with('status', 'error')
                ->with('message', 'Unable to reset password');
        }
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
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
