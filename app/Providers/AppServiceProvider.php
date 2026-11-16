<?php

namespace App\Providers;

use App\Models\Aspirasi;
use App\Models\Kategori;
use App\Models\Pengguna;
use App\Models\TindakLanjut;
use App\Models\Unit;
use App\Observers\AuditObserver;
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

        // Jejak audit: setiap perubahan data penting tercatat di riwayat_perubahan.
        Aspirasi::observe(AuditObserver::class);
        TindakLanjut::observe(AuditObserver::class);
        Kategori::observe(AuditObserver::class);
        Unit::observe(AuditObserver::class);
        Pengguna::observe(AuditObserver::class);

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
