<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Batas permintaan per IP.
        RateLimiter::for('lacak', fn (Request $r) => Limit::perMinute(60)->by($r->ip()));
        RateLimiter::for('ongkir', fn (Request $r) => Limit::perMinute(30)->by($r->ip()));

        // Deteksi N+1 query: dicatat ke log (tidak melempar error).
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::handleLazyLoadingViolationUsing(function (Model $model, string $relation): void {
            logger()->warning('Lazy loading terdeteksi', ['model' => $model::class, 'relation' => $relation]);
        });

        // Cegah perintah destruktif (migrate:fresh, db:wipe, dst.) di production.
        DB::prohibitDestructiveCommands($this->app->isProduction());
    }
}
