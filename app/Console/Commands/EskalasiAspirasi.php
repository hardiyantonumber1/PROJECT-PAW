<?php

namespace App\Console\Commands;

use App\Services\EskalasiService;
use Illuminate\Console\Command;

class EskalasiAspirasi extends Command
{
    protected $signature = 'aspirasi:eskalasi
                            {--uji : Pindai dan tampilkan hasil tanpa menulis perubahan}';

    protected $description = 'Naikkan level eskalasi aspirasi yang melewati SLA respons, tenggat, atau masa tenggang';

    public function handle(EskalasiService $service): int
    {
        $uji = (bool) $this->option('uji');
        $hasil = $service->jalankan($uji);

        if ($uji) {
            $this->warn('Mode uji: tidak ada perubahan yang disimpan.');
        }

        $this->table(
            ['Dipindai', 'Naik level', 'Dilewati', 'L1', 'L2', 'L3'],
            [[
                $hasil['dipindai'],
                $hasil['naik'],
                $hasil['dilewati'],
                $hasil['per_level'][1],
                $hasil['per_level'][2],
                $hasil['per_level'][3],
            ]]
        );

        $this->info($hasil['naik'] > 0
            ? $hasil['naik'].' aspirasi dieskalasi pada '.now()->translatedFormat('j M Y H.i').'.'
            : 'Tidak ada aspirasi yang perlu dieskalasi.');

        return self::SUCCESS;
    }
}
