<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // The "auth" middleware normally redirects guests to route('login'),
        // but this app has no single 'login' route — only role-specific ones
        // (login.librarian, login.instructor, login.student,
        // login.guest). Without this, every expired/unauthenticated hit on a
        // protected dashboard route throws a RouteNotFoundException instead
        // of sending the user back to the right login page.
        $middleware->redirectGuestsTo(function ($request) {
            return match (true) {
                $request->is('dashboard/librarian*') => route('login.librarian'),
                $request->is('dashboard/instructor*') => route('login.instructor'),
                $request->is('dashboard/student*') => route('login.student'),
                default => route('login.guest'),
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
    })->create();