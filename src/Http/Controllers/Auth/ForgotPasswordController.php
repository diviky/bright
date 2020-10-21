<?php

namespace Diviky\Bright\Http\Controllers\Auth;

use Diviky\Bright\Http\Controllers\Auth\Traits\ColumnsTrait;
use Diviky\Bright\Http\Controllers\Auth\Traits\Token;
use Diviky\Bright\Models\Activation;
use Diviky\Bright\Models\Models;
use Diviky\Bright\Notifications\ForgetPassword;
use Diviky\Bright\Routing\Controller;
use Diviky\Bright\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ForgotPasswordController extends Controller
{
    use Token;
    use ColumnsTrait;

    public function reset(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                $this->username() => 'required|exists:' . config('bright.table.users'),
            ]);

            $user = Models::user()::where($this->username(), $request->input($this->username()))->first();

            $token = $this->saveToken($user);

            session(['reset-token' => $this->getTokenId()]);

            $user->notify(new ForgetPassword($token));

            return [
                'status'   => 'OK',
                'message'  => __('Verification code sent to your registered :username.', ['username' => $this->address()]),
                'redirect' => 'password.verify',
            ];
        }

        return view('bright::auth.passwords.token');
    }

    public function resend(Request $request)
    {
        $id = session('reset-token');

        if (\is_null($id)) {
            return [
                'status'   => 'ERROR',
                'message'  => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        $activation = Activation::where('id', $id)->first();

        if (\is_null($activation)) {
            return [
                'status'   => 'ERROR',
                'message'  => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        $user = Models::user()::where('id', $activation->user_id)->first();

        $user->notify(new ForgetPassword($activation->token));

        return [
            'status'  => 'OK',
            'message' => __('Verification code resent to your registered  :username.', ['username' => $this->address()]),
        ];
    }

    public function verify(Request $request)
    {
        $id = session('reset-token');

        if (\is_null($id)) {
            return [
                'status'   => 'ERROR',
                'message'  => 'Invalid request',
                'redirect' => 'password.reset',
            ];
        }

        if ($request->isMethod('get')) {
            return view('bright::auth.verify');
        }

        $request->validate([
            'token' => 'required',
        ]);

        $token = $request->input('token');

        $activation = Activation::where('token', $token)
            ->where('id', $id)
            ->first();

        if (empty($activation)) {
            return [
                'status'  => 'ERROR',
                'message' => 'Invalid verification code.',
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

        if (\is_null($id)) {
            return redirect()->route('password.reset')
                ->with('message', 'Invalid request');
        }

        if ($request->isMethod('get')) {
            return view('bright::auth.passwords.change');
        }

        $request->validate([
            'password' => 'required|confirmed|min:8',
        ]);

        $user = Models::user()::where('id', $id)->first();

        if ($this->resetPassword($user, $request->input('password'))) {
            session()->forget('forget-userid');

            return redirect()->route('home');
        }

        return redirect()->back()
            ->with('status', 'ERROR')
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
        $user->setAccessToken(Str::random(30));

        if (!$user->save()) {
            return false;
        }

        event(new PasswordReset($user));

        Auth::guard()->login($user);

        return true;
    }
}
