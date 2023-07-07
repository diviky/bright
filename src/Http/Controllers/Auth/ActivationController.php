<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Auth;

use Diviky\Bright\Http\Controllers\Auth\Traits\ColumnsTrait;
use Diviky\Bright\Http\Controllers\Auth\Traits\Token;
use Diviky\Bright\Models\Activation;
use Diviky\Bright\Notifications\SendActivationToken;
use Diviky\Bright\Routing\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class ActivationController extends Controller
{
    use Notifiable;
    use Token;
    use ColumnsTrait;

    /**
     * @return \Illuminate\Contracts\View\View|string[]
     *
     * @psalm-return \Illuminate\Contracts\View\View|array{status: mixed|string, message: string, redirect?: string}
     */
    public function activate(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('bright::auth.activate');
        }

        $request->validate([
            'token' => 'required',
        ]);

        $token = $request->input('token');
        $user = user();

        if (1 == $user->status) {
            return [
                'status' => 'OK',
                'message' => 'Your account is already activated',
                'redirect' => 'home',
            ];
        }

        $activation = Activation::where('token', $token)
            ->where('user_id', $user->id)
            ->first();

        if (empty($activation)) {
            return [
                'status' => 'ERROR',
                'message' => 'Invalid activation key.',
            ];
        }

        // is token expired
        if (isset($activation->expires_at) && $activation->expires_at->lessThan(now())) {
            return [
                'status' => 'ERROR',
                'message' => 'Token Expired. Please request again',
            ];
        }

        event(new Verified($user));

        $user->status = 1;
        $user->save();

        $activation->delete();

        return [
            'status' => 'OK',
            'message' => 'Your account activated successfully.',
            'redirect' => 'home',
        ];
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend()
    {
        $user = Auth::user();

        if (isset($user)) {
            $token = $this->saveToken($user);
            $user->notify(new SendActivationToken($token));
        }

        return response()->json([
            'status' => 'OK',
            'message' => trans('Verification code resent to your registered :username.', ['username' => $this->address()]),
        ]);
    }
}
