<?php

namespace App\Providers;

use App\Models\User;
use App\Models\UserLoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
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
        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                // Prevent duplicate logging within a short interval (5 seconds)
                $alreadyLogged = UserLoginLog::where('user_id', $event->user->id)
                    ->where('login_at', '>=', now()->subSeconds(5))
                    ->exists();

                if (!$alreadyLogged) {
                    UserLoginLog::recordLogin($event->user, request());
                }
            }
        });
    }
}
