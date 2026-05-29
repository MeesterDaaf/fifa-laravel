<?php

namespace App\Providers;

use App\Models\Fixture;
use App\Models\Prediction;
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
        // Reminder-data voor de header: de eerstvolgende 3 wedstrijden + of de
        // ingelogde gebruiker ze al heeft voorspeld.
        View::composer('partials.reminder', function ($view) {
            $next = collect();
            $predictedIds = collect();
            $todoCount = 0;

            if (auth()->check()) {
                $next = Fixture::where('status', 'SCHEDULED')
                    ->where('scheduled_at', '>=', now())
                    ->orderBy('scheduled_at')
                    ->limit(3)
                    ->get();

                $predictedIds = Prediction::where('user_id', auth()->id())
                    ->whereIn('fixture_id', $next->pluck('id'))
                    ->pluck('fixture_id')
                    ->flip();

                // Alleen nog-open wedstrijden (niet binnen 15 min vóór aftrap) tellen als "te doen".
                $todoCount = $next
                    ->filter(fn ($m) => $m->isOpen())
                    ->reject(fn ($m) => isset($predictedIds[$m->id]))
                    ->count();
            }

            $view->with(compact('next', 'predictedIds', 'todoCount'));
        });
    }
}
