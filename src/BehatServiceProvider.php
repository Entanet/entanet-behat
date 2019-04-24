<?php

namespace Entanet\Behat;

use Illuminate\Support\ServiceProvider;

class BehatServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/.env.behat' => base_path('.env.behat')
        ]);

        $this->publishes([
            __DIR__.'/behat.yml' => base_path('behat.yml')
        ]);
    }
}
