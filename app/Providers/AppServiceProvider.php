<?php

namespace App\Providers;

use App\Models\Aspirasi;
use Illuminate\Support\Carbon;
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
        // Nama hari/bulan Bahasa Indonesia: "Rabu, 4 November 2026".
        Carbon::setLocale('id');

        // Data bersama sidebar: jumlah aspirasi untuk badge menu.
        View::composer('partials.sidebar', function ($view) {
            $user = auth()->user();

            $view->with('navAntrian', $user && $user->isPetugas()
                ? Aspirasi::terlihatOleh($user)->berjalan()->count()
                : 0);

            $view->with('navAspirasi', $user && $user->isMahasiswa()
                ? Aspirasi::where('pengguna_id', $user->id)->count()
                : 0);
        });
    }
}
