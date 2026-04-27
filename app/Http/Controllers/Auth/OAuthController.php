<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class OAuthController extends Controller
{
    /**
     * Redirect the user to the LINE authentication page.
     */
    public function redirectToLine()
    {
        return Socialite::driver('line')->redirect();
    }

    /**
     * Obtain the user information from LINE.
     */
    public function handleLineCallback()
    {
        try {
            $lineUser = Socialite::driver('line')->user();
            
            // ค้นหาหรือสร้าง User ใหม่
            $user = User::where('email', $lineUser->getEmail())->first();
            
            if (!$user) {
                // ถ้าไม่มีในระบบ ให้สร้างใหม่ (หรือจัดการตาม Business Logic)
                // หมายเหตุ: ในระบบเดิมอาจจะมีฟิลด์ line_id เฉพาะ
                $user = User::create([
                    'name' => $lineUser->getName(),
                    'email' => $lineUser->getEmail(),
                    'password' => bcrypt(\Illuminate\Support\Str::random(16)), // Dummy password
                ]);
            }

            Auth::guard('user')->login($user);

            return redirect()->intended('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'LINE Login Failed: ' . $e->getMessage());
        }
    }

    /**
     * Redirect the user to the Google authentication page (Admin Only).
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google for Admin login.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // ค้นหา Admin จาก Email
            $admin = Admin::where('email', $googleUser->getEmail())->first();
            
            if (!$admin) {
                return redirect('/login')->with('error', 'คุณไม่ได้รับอนุญาตให้เข้าใช้งานในฐานะ Admin');
            }

            // อัปเดต Google ID
            $admin->update(['google_id' => $googleUser->getId()]);

            Auth::guard('admin')->login($admin);

            return redirect()->intended('/admin/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Google Login Failed');
        }
    }
}
