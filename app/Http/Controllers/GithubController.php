<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GithubController extends Controller
{
    public function handleRedirect()
    {
        return Socialite::driver('github')->redirect();
    }

    public function handleCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            $findUser = User::where('email', $githubUser->getEmail())->first();

            if ($findUser) {
                // Jika pengguna dengan email yang sama sudah ada dalam database
                $findUser->github_id = $githubUser->getId();
                $findUser->type_login = 'github';
                $findUser->save();
            } else {
                // Jika pengguna dengan email yang sama belum ada dalam database
                $findUser = User::create([
                    'name' => $githubUser->getName(),
                    'email' => $githubUser->getEmail(),
                    'github_id' => $githubUser->getId(),
                    'type_login' => 'github',
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
