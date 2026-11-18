<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('association:uploads {--limit=10}', function (\App\Services\Association\AssociationTransfers $transfers) {
    if (! \Illuminate\Support\Facades\Schema::hasTable('association_upload_transfers')) {
        $this->warn('Association migration is required.');
        return;
    }
    $ids = \Illuminate\Support\Facades\DB::table('association_upload_transfers')->where('status', 'assembling')
        ->orderBy('updated_at')->limit(max(1, min(100, (int) $this->option('limit'))))->pluck('id');
    foreach ($ids as $id) { $transfers->assemble($id); }
    \Illuminate\Support\Facades\DB::table('association_preferences')->updateOrInsert(['key' => 'runtime.upload_worker'], ['value' => now()->toIso8601String(), 'updated_at' => now()]);
    $this->info('Processed uploads: '.$ids->count());
})->purpose('Stream and verify completed association attachment transfers without request time limits');

\Illuminate\Support\Facades\Schedule::command('association:uploads')->everyMinute()->withoutOverlapping()
    ->when(fn () => config('association.enabled'));

Artisan::command('association:push {--limit=20}', function (\App\Services\Association\AssociationPush $push) {
    $this->info('Provider accepted attempts: '.$push->dispatch((int) $this->option('limit')));
    if (\Illuminate\Support\Facades\Schema::hasTable('association_preferences')) {
        \Illuminate\Support\Facades\DB::table('association_preferences')->updateOrInsert(['key' => 'runtime.push_worker'], ['value' => now()->toIso8601String(), 'updated_at' => now()]);
    }
})->purpose('Dispatch targeted account notifications and stop acknowledged reminders');

\Illuminate\Support\Facades\Schedule::command('association:push')->everyMinute()->withoutOverlapping()
    ->when(fn () => config('association.enabled') && config('religious_push.enabled'));

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
