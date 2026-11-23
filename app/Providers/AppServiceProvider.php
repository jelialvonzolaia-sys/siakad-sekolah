<?php

namespace App\Providers;

use App\Models\Notifikasi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
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
        date_default_timezone_set('Asia/Jakarta');
        Carbon::setLocale('id');

        View::composer('layouts.siakad', function ($view): void {
            $unreadNotifikasis = collect();
            $readNotifikasis = collect();
            $unreadNotifCount = 0;

            if (auth()->check() && Schema::hasTable('notifikasis')) {
                $userId = auth()->id();

                $unreadNotifCount = Notifikasi::where('user_id', $userId)
                    ->where('is_read', false)
                    ->count();

                $unreadNotifikasis = Notifikasi::where('user_id', $userId)
                    ->where('is_read', false)
                    ->latest()
                    ->take(15)
                    ->get();

                $readNotifikasis = Notifikasi::where('user_id', $userId)
                    ->where('is_read', true)
                    ->latest()
                    ->take(10)
                    ->get();
            }

            $view->with(compact('unreadNotifikasis', 'readNotifikasis', 'unreadNotifCount'));
        });
    }
}
