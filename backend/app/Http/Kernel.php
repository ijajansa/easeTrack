<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middlewareAliases = [
        'device.token' => \App\Http\Middleware\EnsureDeviceToken::class,
        'admin.auth' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
        'admin.guest' => \App\Http\Middleware\EnsureAdminGuest::class,
    ];
}

