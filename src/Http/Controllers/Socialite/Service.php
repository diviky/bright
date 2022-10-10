<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Socialite;

use App\Models\User;
use Diviky\Bright\Models\SocialiteUser;
use Illuminate\Support\Facades\Auth;

class Service
{
    /**
     * Check accont is linked and return details.
     *
     * @param string $provider
     * @param int    $socialite_id
     *
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    public function linked($provider, $socialite_id)
    {
        return SocialiteUser::where('provider', $provider)
            ->where('socialite_id', $socialite_id)
            ->first();
    }

    /**
     * Link user with social account.
     *
     * @param string $provider
     * @param object $user
     * @param object $socialite
     *
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    public function linkAccount($provider, $user, $socialite)
    {
        $values = [];
        $values['user_id'] = $user->id;
        $values['provider'] = $provider;
        $values['socialite_id'] = $socialite->getId();
        $values['nickname'] = $socialite->getNickname();
        $values['name'] = $socialite->getName();
        $values['email'] = $socialite->getEmail();
        $values['secret'] = $socialite->token;
        $values['refresh_token'] = $socialite->refreshToken;
        $values['expires'] = $socialite->expiresIn;

        return SocialiteUser::create($values);
    }

    /**
     * Check the user exists with email address.
     *
     * @param object $social
     *
     * @return null|\Illuminate\Database\Eloquent\Model
     */
    public function userFound($social)
    {
        return User::where('email', $social->email)->first();
    }

    /**
     * @param int    $user_id
     * @param string $redirect
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function login($user_id, $redirect = '/')
    {
        $result = Auth::guard()->loginUsingId($user_id);

        if (!$result) {
            abort(401, 'Unable to login');
        }

        return redirect($redirect);
    }
}
