<?php

namespace App\Providers;

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
        $managed = app(\App\Services\Religious\AppManagedContent::class);
        if (is_file($managed->path())) {
            try {
                $settings = $managed->state()['settings'] ?? [];
            } catch (\Throwable $error) {
                report($error);

                return; // Keep the rest of the site available with its configured limits.
            }
            foreach (['daily_limit', 'monthly_limit'] as $key) {
                if (isset($settings[$key])) {
                    config(['religious_assistant.'.$key => $settings[$key]]);
                }
            }
            if (isset($settings['budget_usd'])) {
                config(['religious_assistant.budget_micro_usd' => $settings['budget_usd'] * 1000000]);
            }
        }
    }
}
