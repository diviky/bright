<?php

namespace Diviky\Bright\Http\Controllers\Socialite;

use App\Http\Controllers\Controller as BaseController;
use Diviky\Bright\Http\Controllers\Auth\Concerns\RegistersUsers;
use Redirect;
use Socialite;

class Controller extends BaseController
{
    use RegistersUsers;

    protected $role;

    public function connect($provider)
    {
        return Socialite::driver($provider)
            ->redirect();
    }

    public function callback($provider)
    {
        try {
            $socialite = Socialite::driver($provider)
                ->stateless()
                ->user();
        } catch (\Exception $e) {
            return redirect('login')
                ->with([
                    'status'  => 'ERROR',
                    'message' => $e->getMessage(),
                ]);
        }

        if (!$socialite->getId()) {
            return redirect('login')
                ->with([
                    'status'  => 'ERROR',
                    'message' => 'Unable to get the user details',
                ]);
        }

        $service = new Service();
        $linked  = $service->linked($provider, $socialite->getId());

        if ($linked) {
            return $service->login($linked->user_id);
        }

        $user = $service->userFound($socialite);
        if ($user) {
            $service->linkAccount($provider, $user, $socialite);

            return $service->login($user->id);
        }

        $values = [
            'name'         => $socialite->getName(),
            'email'        => $socialite->getEmail(),
            'password'     => $socialite->getNickname(),
            'status'       => 1,
        ];

        $user = $this->registers($values);

        if ($user) {
            $service->linkAccount($provider, $user, $socialite);

            return $service->login($user->id);
        }

        return redirect()->route('home');
    }
}
