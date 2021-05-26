<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Controllers\Socialite;

use App\Http\Controllers\Controller as BaseController;
use Diviky\Bright\Http\Controllers\Auth\Concerns\RegistersUsers;
use Socialite;

class Controller extends BaseController
{
    use RegistersUsers;

    /**
     * Redirect user to for login.
     *
     * @param string $provider
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function connect($provider)
    {
        return Socialite::driver($provider)
            ->redirect();
    }

    /**
     * @param string $provider
     *
     * @return null|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function callback($provider)
    {
        try {
            $socialite = Socialite::driver($provider)
                ->stateless()
                ->user();
        } catch (\Exception $e) {
            return redirect('login')
                ->with([
                    'status' => 'ERROR',
                    'message' => $e->getMessage(),
                ]);
        }

        if (!$socialite->getId()) {
            return redirect('login')
                ->with([
                    'status' => 'ERROR',
                    'message' => 'Unable to get the user details',
                ]);
        }

        $service = new Service();
        $linked = $service->linked($provider, $socialite->getId());

        if ($linked) {
            return $service->login($linked->user_id, $this->redirectPath());
        }

        $user = $service->userFound($socialite);
        if ($user) {
            $service->linkAccount($provider, $user, $socialite);

            return $service->login($user->id, $this->redirectPath());
        }

        $name = \explode(' ', $socialite->getName(), 2);

        $values = [
            'socialite_id' => $socialite->getId(),
            'name' => $socialite->getName(),
            'nickname' => $socialite->getNickname(),
            'first_name' => $name[0] ?? null,
            'last_name' => $name[1] ?? null,
            'email' => $socialite->getEmail(),
            'password' => $socialite->getNickname(),
            'status' => 1,
        ];

        $user = $this->registers($values);

        if ($user) {
            $service->linkAccount($provider, $user, $socialite);

            return $service->login($user->id, $this->redirectPath());
        }

        return redirect()->route('home');
    }
}
