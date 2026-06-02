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
            $parseQueryString = parse_url($backendUrl, PHP_URL_QUERY);
            $parsedUrl = array_map('trim', explode('/' , $backendUrl));

            // Route to your frontend landing page (e.g., Next.js, Vue, Nuxt)
            $prefixHashUrl = '?id=' . $parsedUrl[8] . '&hash=' . $parsedUrl[9] . '&' . $parseQueryString;
            $frontendTargetUrl = config('app.frontend_url') . '/verify-email' . $prefixHashUrl;

            return $frontendTargetUrl;
        }));
    }
}
