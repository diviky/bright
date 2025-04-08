<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Diviky\Bright\Http\Controllers\Auth\Concerns\ColumnsTrait;
use Diviky\Bright\Http\Controllers\Auth\Concerns\Token;
use Diviky\Bright\Models\Activation;
use Diviky\Bright\Models\Models;
use Diviky\Bright\Notifications\ForgetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Api extends Controller
{
    use ColumnsTrait;
    use Token;

    public function login()
    {
        return [
            'user' => user(),
        ];
    }

    public function reset()
    {
        $this->rules([
            $this->username() => 'required|exists:' . config('bright.table.users'),
        ]);

        $user = Models::user()::where($this->username(), $this->input($this->username()))->first();

        $token = $this->saveToken($user);

        $id = $this->getTokenId();

        $user->notify(new ForgetPassword($token));

        return [
            'status' => 'OK',
            'message' => trans('Verification code sent to your registered :username.', ['username' => $this->address()]),
            'id' => $id,
        ];
    }

    public function resend($id)
    {
        if (\is_null($id)) {
            return [
                'status' => 'ERROR',
                'message' => 'Invalid request',
            ];
        }

        $activation = Activation::where('id', $id)->first();

        if (\is_null($activation)) {
            return [
                'status' => 'ERROR',
                'message' => 'Invalid request',
            ];
        }

        // is token expired
        if (isset($activation->expires_at) && $activation->expires_at->lessThan(now())) {
            return [
                'status' => 'ERROR',
                'message' => 'Token Expired. Please request again',
            ];
        }

        $user = Models::user()::where('id', $activation->user_id)->first();

        $user->notify(new ForgetPassword($activation->token));

        $activation->delete();

        return [
            'status' => 'OK',
            'message' => trans('Verification code resent to your registered :username.', ['username' => $this->address()]),
            'id' => $id,
        ];
    }

    public function verify($id)
    {
        if (\is_null($id)) {
            return [
                'status' => 'ERROR',
                'message' => 'Invalid request',
            ];
        }

        $this->rules([
            'token' => 'required',
        ]);

        $token = $this->input('token');

        $activation = Activation::where('token', $token)
            ->where('id', $id)
            ->first();

        if (empty($activation)) {
            return [
                'status' => 'ERROR',
                'message' => 'Invalid verification code.',
            ];
        }

        // is token expired
        if (isset($activation->expires_at) && $activation->expires_at->lessThan(now())) {
            return [
                'status' => 'ERROR',
                'message' => 'Token Expired. Please request again',
            ];
        }

        $user = Models::user()::where('id', $activation->user_id)->first();

        $activation->delete();

        return [
            'status' => 'OK',
            'message' => 'Account verified successfully.',
            'user' => $user,
        ];
    }

    public function change()
    {
        $this->rules([
            'password' => 'required|confirmed|min:8',
        ]);

        $user = user();
        $user = $this->resetPassword($user, $this->input('password'));

        if ($user) {
            return [
                'status' => 'OK',
                'message' => 'Password changed successfully.',
                'user' => $user,
            ];
        }

        return [
            'status' => 'ERROR',
            'message' => 'Unable to reset password.',
        ];
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
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

        return $user;
    }
}
