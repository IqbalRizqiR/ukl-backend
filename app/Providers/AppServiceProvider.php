<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;
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
        VerifyEmail::createUrlUsing((function ($notifiable) {
            $backendUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
            // Extract query parameters (expires, signature)
            $parsedUrl = parse_url($backendUrl);
            $queryString = $parsedUrl['query'] ?? '';

            // Route to your frontend landing page (e.g., Next.js, Vue, Nuxt)
            $frontendTargetUrl = config('app.frontend_url') . '/verify-email?' . $queryString;

            return $frontendTargetUrl;
        }));
    }
}
