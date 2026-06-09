<?php

namespace App\Providers;

use App\Models\Fixture;
use App\Models\Prediction;
use App\Models\Setting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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
        // Nederlandse reset-mail (gebruikt door zowel self-service als de admin-knop).
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Wachtwoord resetten – FIFA 2026 Pool')
                ->greeting('Hoi ' . $notifiable->name . ',')
                ->line('Er is een verzoek gedaan om het wachtwoord van je account te resetten.')
                ->action('Wachtwoord resetten', $url)
                ->line('Deze link verloopt over 60 minuten.')
                ->line('Heb je dit niet aangevraagd? Dan kun je deze e-mail negeren — er verandert niets.')
                ->salutation('Groet, FIFA 2026 Pool ⚽');
        });

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

        // WhatsApp-groepslink beschikbaar in de hoofdlayout (elke pagina).
        View::composer('layouts.app', function ($view) {
            $view->with('whatsappGroupUrl', auth()->check() ? Setting::whatsappGroupUrl() : null);
        });
    }
}
