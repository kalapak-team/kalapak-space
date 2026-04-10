<?php

namespace App\Providers;

use App\Services\SupabaseStorage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SupabaseStorage::class);
    }

    public function boot(): void
    {
        //
    }
}
