<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Team;
use App\Enums\UserRoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Auth\Events\Registered;
// use App\Providers\RouteServiceProvider;
use Laravel\Socialite\Facades\Socialite;
// use Illuminate\Auth\AuthenticationException;

class SocialLoginController extends Controller
{
    public function googleRedirect()
    {
        return redirect('/');
        // Cookie::queue('my_intented_url', url()->previous(), 5);
        // return Socialite::driver('google')->redirect();
    }

    public function googleCallback(Request $request)
    {
        return redirect('/');
        /*
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $finduser = User::where('email', $googleUser->email)->first();
            $intendedUrl = Cookie::get('my_intented_url');
            Cookie::forget('my_intented_url');
            if ($finduser) {
                $finduser->profile_photo_path = $googleUser->avatar;
                $finduser->save();
                Auth::loginUsingId($finduser->id);
                return redirect()->intended($intendedUrl);
            } else {
                $adminEmails = config('app.admin_emails');
                $adminEmails = !empty($adminEmails) ? explode(',', $adminEmails) : [];
                $role = UserRoleEnum::USER;
                if(!empty($adminEmails) && is_array($adminEmails) && in_array($googleUser->email, $adminEmails)){
                    $role = UserRoleEnum::SUPERADMIN;
                }

                $newUser = User::updateOrCreate([
                    'email' => $googleUser->email,
                ], [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'role' => $role,
                    'email_verified_at' => now(),
                    'profile_photo_path' => $googleUser->avatar,
                ]);
                event(new Registered($newUser));

                $newTeam = Team::forceCreate([
                    'user_id' => $newUser->id,
                    'name' => explode(' ', $newUser->name, 2)[0]."'s Team",
                    'personal_team' => true,
                ]);
                $newUser->password = Hash::make('');
                $newTeam->save();
                $newUser->current_team_id = $newTeam->id;
                $newUser->save();

                Auth::loginUsingId($newUser->id, true);
                return redirect()->intended($intendedUrl);
            }
        } catch (Exception $e) {
            return response()->json([
                'errors' => [],
                'message' => $e->getMessage(),
            ], 500);
        }
        */
    }
}