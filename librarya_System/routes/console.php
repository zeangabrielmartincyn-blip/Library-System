<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    app(\App\Services\LibraryRepository::class)->calculateAndStoreFines();
})->dailyAt('01:00')->name('recalculate-fines')->withoutOverlapping();