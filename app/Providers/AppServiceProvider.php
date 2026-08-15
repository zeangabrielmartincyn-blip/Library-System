<?php

namespace App\Providers;

use App\Models\User;
use App\Services\LibraryRepository;
use App\Services\SmsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SmsService::class);
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::define('submit-book-review', function (User $user, string $isbn) {
            if ($user->role !== 'student') {
                return false;
            }

            $library = app(LibraryRepository::class);

            return $library->loansForUser((int) $user->id)
                ->contains(fn ($loan) => $loan->isbn === $isbn);
        });
    }
}


