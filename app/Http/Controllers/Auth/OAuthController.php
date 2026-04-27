<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class OAuthController extends Controller
{
    public function redirectToLine()
    {
        return Socialite::driver('line')->redirect();
    }

    public function handleLineCallback()
    {
        try {
            $lineUser = Socialite::driver('line')->user();
            $clinicId = currentClinicId();
            $email = $lineUser->getEmail() ?: $clinicId.'.'.$lineUser->getId().'@line.local';

            $user = User::firstOrNew([
                'clinic_id' => $clinicId,
                'line_user_id' => $lineUser->getId(),
            ]);

            $user->fill([
                'name' => $lineUser->getName() ?: $user->name ?: 'LINE User',
                'email' => $email,
                'line_avatar_url' => $lineUser->getAvatar(),
            ]);

            if (! $user->exists) {
                $user->password = Str::password(32);
            }

            $user->save();

            Auth::guard('user')->login($user);
            session([
                'line_user_id' => $lineUser->getId(),
                'clinic_id' => $user->clinic_id,
            ]);

            return redirect()->intended('/user/hub');
        } catch (Throwable) {
            return redirect()->route('login')->withErrors([
                'email' => 'LINE login failed. Please try again.',
            ]);
        }
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $admin = Admin::where('email', $googleUser->getEmail())->first();

            if (! $admin) {
                return redirect()->route('admin.login')->withErrors([
                    'email' => 'This Google account is not allowed to access the admin area.',
                ]);
            }

            $admin->update([
                'google_id' => $googleUser->getId(),
                'profile_photo_path' => $googleUser->getAvatar(),
            ]);

            Auth::guard('admin')->login($admin);
            session([
                'admin_id' => $admin->id,
                'clinic_id' => $admin->clinic_id,
            ]);

            return redirect()->intended('/admin/dashboard');
        } catch (Throwable) {
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Google login failed. Please try again.',
            ]);
        }
    }
}
