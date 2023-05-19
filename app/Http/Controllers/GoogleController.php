<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function handleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $findUser = User::where('email', $googleUser->getEmail())->first();

            if ($findUser) {
                // Jika pengguna dengan email yang sama sudah ada dalam database
                $findUser->google_id = $googleUser->getId();
                $findUser->type_login = 'google';
                $findUser->save();
            } else {
                // Jika pengguna dengan email yang sama belum ada dalam database
                $findUser = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'type_login' => 'google',
                    'password' => bcrypt('pass123')
                ]);
            }

            Auth::login($findUser);

            return redirect()->intended(route('dashboard'));
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
