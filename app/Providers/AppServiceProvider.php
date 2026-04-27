<?php

namespace App\Providers;

use App\Services\IdentityQrCode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            [\SocialiteProviders\Line\LineExtendSocialite::class, 'handle']
        );

        View::composer('layouts.user', function ($view) {
            $user = Auth::guard('user')->user();

            $view->with('identityQrSvg', $user ? app(IdentityQrCode::class)->svg($user, 180) : null);
        });
    }
}
