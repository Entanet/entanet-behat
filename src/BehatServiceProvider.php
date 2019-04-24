<?php

namespace Entanet\Behat;

use Illuminate\Support\ServiceProvider;

class BehatServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/files/.env.behat' => base_path('.env.behat')
        ]);

        $this->publishes([
            __DIR__.'/files/behat.yml' => base_path('behat.yml')
        ]);

        $this->publishes([
            __DIR__.'/files/api.feature' => base_path('features/pipeline/api.feature')
        ]);

        $this->publishes([
            __DIR__.'/files/ui.feature' => base_path('features/ui/ui.feature')
        ]);
    }
}
