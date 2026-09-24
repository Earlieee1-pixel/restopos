<?php

namespace App\Providers;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tangtangon ang 'data' wrapper sa tanan JsonResource responses
        // Para consistent ang format sa frontend — { id, name, ... } dili { data: { id, name, ... } }
        JsonResource::withoutWrapping();
    }
}
