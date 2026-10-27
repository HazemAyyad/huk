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

Artisan::command('religious-community:push', function (\App\Services\Religious\ReligiousPush $push) {
    $this->info('Accepted by provider: '.$push->dispatch());
})->purpose('Send generic public religious publication notifications through FCM');

\Illuminate\Support\Facades\Schedule::command('religious-community:push')
    ->everyMinute()->withoutOverlapping()->when(fn () => config('religious_push.enabled', false) && config('religious_community.enabled', false));
