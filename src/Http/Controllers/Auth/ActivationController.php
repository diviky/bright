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
     * @return \Illuminate\View\View|string[]
     *
     * @psalm-return \Illuminate\View\View|array{status: string, message: string, redirect?: string}
     */
    public function activate(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('bright::auth.activate');
        }

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
     * @return (array|null|string)[]
     *
     * @psalm-return array{status: string, message: array|null|string}
     */
    public function resend(): array
    {
        $user = Auth::user();

        if (is_null($user)) {
            return [
                'status' => 'ERROR',
                'message' => __('Unable to find the user'),
            ];
        }

        $token = $this->saveToken($user);

        $user->notify(new SendActivationToken($token));

        return [
            'status' => 'OK',
            'message' => __('Verification code resent to your registered :username.', ['username' => $this->address()]),
        ];
    }
}
