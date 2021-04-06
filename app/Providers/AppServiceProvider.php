<?php

namespace App\Providers;

use Dotenv\Dotenv;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $dotenv = Dotenv::createImmutable(base_path(), '.env.' . $this->app->environment());
        $dotenv->load();
    }
}
