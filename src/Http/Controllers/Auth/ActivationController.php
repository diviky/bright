<?php

namespace Karla\Http\Controllers\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Karla\Http\Controllers\Auth\Models\Activation;
use Karla\Http\Controllers\Auth\Traits\ColumnsTrait;
use Karla\Http\Controllers\Auth\Traits\Token;
use Karla\Notifications\SendActivationToken;
use Karla\Routing\Controller;

class ActivationController extends Controller
{
    use Notifiable;
    use Token;
    use ColumnsTrait;

    public function activate(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('karla::auth.activate');
        }

        $token = $request->input('token');
        $user  = user();

        if (1 == $user->status) {
            return [
                'status'   => 'OK',
                'message'  => 'Your account is already activated',
                'redirect' => 'home',
            ];
        }

        $activation = Activation::where('token', $token)
            ->where('user_id', $user->id)
            ->first();

        if (empty($activation)) {
            return [
                'status'  => 'ERROR',
                'message' => 'Invalid activation key.',
            ];
        }

        event(new Verified($user));

        Auth::user()->status = 1;
        Auth::user()->save();

        $activation->delete();

        return [
            'status'   => 'OK',
            'message'  => 'Your account activated successfully.',
            'redirect' => 'home',
        ];
    }

    public function resend()
    {
        $user = Auth::user();

        $token = $this->saveToken($user);

        $user->notify(new SendActivationToken($token));

        return [
            'status'  => 'OK',
            'message' => __('Verification code resent to your registered :username.', ['username' => $this->address()]),
        ];
    }
}
