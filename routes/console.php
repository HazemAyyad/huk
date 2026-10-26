<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('religious-community:publish', function (\App\Services\Religious\ReligiousCommunity $community) {
    $this->info('Published: '.$community->publishDue());
})->purpose('Idempotently mark approved scheduled religious publications as published');

\Illuminate\Support\Facades\Schedule::command('religious-community:publish')
    ->everyMinute()->withoutOverlapping()->when(fn () => config('religious_community.enabled', false));
