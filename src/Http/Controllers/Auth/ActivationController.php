<?php

namespace Karla\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Karla\Http\Controllers\Auth\Models\Activation;
use Karla\Http\Controllers\Auth\Traits\Token;
use Karla\Notifications\SendActivationToken;
use Karla\Routing\Controller;

class ActivationController extends Controller
{
    use Notifiable;
    use Token;

    public function activate(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('auth.activate');
        }

        $token = $request->input('token');

        if (auth()->user()->status == 1) {
            return redirect()->route('home')
                ->with('status', 'success')
                ->with('message', 'Your account is already activated.');
        }

        $activation = Activation::where('token', $token)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (empty($activation)) {
            return redirect()->route('user.verify')
                ->with('status', 'error')
                ->with('message', 'No such token in the database!');
        }

        Auth::user()->status = 1;
        Auth::user()->save();

        $activation->delete();

        return redirect()->route('home')
            ->with('status', 'success')
            ->with('message', 'You successfully activated your account!');
    }

    public function resend()
    {
        $user = Auth::user();

        $token = $this->saveToken($user);

        $user->notify(new SendActivationToken($token));

        return redirect()->back()
            ->with('status', 'success')
            ->with('message', 'Verification code resent to your registered mobile number.');
    }
}
