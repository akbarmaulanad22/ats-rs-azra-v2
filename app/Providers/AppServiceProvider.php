<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\User;
use App\Policies\AccountPolicy;
use App\Policies\ApplicationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(User::class, AccountPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
    }
}
