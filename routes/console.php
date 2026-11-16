<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Penjadwalan eskalasi
|--------------------------------------------------------------------------
| Dijalankan tiap jam. Aman diulang karena EskalasiService idempoten: level
| hanya boleh naik, minimum 60 menit antar eskalasi, dan UNIQUE
| (aspirasi_id, level) menolak baris ganda di tingkat basis data.
|
| Di produksi pastikan cron memanggil `php artisan schedule:run` tiap menit.
*/
Schedule::command('aspirasi:eskalasi')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
