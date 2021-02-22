<?php

namespace Diviky\Bright\Http\Controllers\Socialite;

use App\Models\User;
use Diviky\Bright\Models\SocialiteUser;
use Illuminate\Support\Facades\Auth;

class Service
{
    public function logUserIn($id)
    {
        if (!empty($id)) {
            $row = $this->db->table('auth_user_socialites')
                ->where('id', $id)
                ->get(['user_id'])
                ->first();

            return $this->process($row->user_id);
        }
    }

    public function linked($provider, $socialite_id)
    {
        return SocialiteUser::where('provider', $provider)
            ->where('socialite_id', $socialite_id)
            ->first();
    }

    public function linkAccount($provider, $user, $socialite)
    {
        $values                         = [];
        $values['user_id']              = $user->id;
        $values['provider']             = $provider;
        $values['socialite_id']         = $socialite->getId();
        $values['nickname']             = $socialite->getNickname();
        $values['name']                 = $socialite->getName();
        $values['email']                = $socialite->getEmail();
        $values['secret']               = $socialite->token;
        $values['refresh_token']        = $socialite->refreshToken;
        $values['expires']              = $socialite->expiresIn;

        return SocialiteUser::create($values);
    }

    public function userFound($social)
    {
        return User::where('email', $social->email)->first();
    }

    public function login($user_id, $redirect = '/')
    {
        $result = Auth::guard()->loginUsingId($user_id);

        if (!$result) {
            abort(401, 'Unable to login');
        }

        return redirect($redirect);
    }
}
