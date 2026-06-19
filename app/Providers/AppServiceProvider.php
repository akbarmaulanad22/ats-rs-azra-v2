<?php

namespace App\Providers;

use App\Logging\LogContext;
use App\Models\Application;
use App\Models\ApplicationStage;
use App\Models\Candidate;
use App\Models\DiscSubmission;
use App\Models\EmailTemplate;
use App\Models\Employee;
use App\Models\InterviewResult;
use App\Models\InterviewTemplate;
use App\Models\JobTemplate;
use App\Models\MbtiSubmission;
use App\Models\McuResult;
use App\Models\OfferingLetter;
use App\Models\OnboardingResult;
use App\Models\QuestionBankTemplate;
use App\Models\TestSubmission;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WorkflowTemplate;
use App\Observers\ModelActivityObserver;
use App\Policies\AccountPolicy;
use App\Policies\ApplicationPolicy;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->registerRateLimiters();
        $this->registerObservers();
        $this->registerAuthEventListeners();
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('public-browse', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('token-access', function (Request $request) {
            return Limit::perMinute(10)->by($request->route('token') ?? $request->ip());
        });

        RateLimiter::for('public-submit', function (Request $request) {
            return Limit::perMinute(5)->by($request->route('token') ?? $request->ip());
        });

        RateLimiter::for('signed-access', function (Request $request) {
            $offering = $request->route('offering');
            $key = $offering instanceof Model ? $offering->getKey() : $offering;

            return Limit::perMinute(10)->by('signed:'.$key);
        });
    }

    private function registerObservers(): void
    {
        $models = [
            Application::class,
            ApplicationStage::class,
            Candidate::class,
            DiscSubmission::class,
            EmailTemplate::class,
            Employee::class,
            InterviewResult::class,
            InterviewTemplate::class,
            JobTemplate::class,
            McuResult::class,
            MbtiSubmission::class,
            OfferingLetter::class,
            OnboardingResult::class,
            QuestionBankTemplate::class,
            TestSubmission::class,
            Unit::class,
            User::class,
            Vacancy::class,
            WorkflowTemplate::class,
        ];

        foreach ($models as $model) {
            $model::observe(ModelActivityObserver::class);
        }
    }

    private function registerAuthEventListeners(): void
    {
        Event::listen(Login::class, function (Login $event): void {
            Log::info('User logged in', array_merge(LogContext::make(), [
                'auth_user_id' => $event->user->getAuthIdentifier(),
                'auth_user_name' => $event->user->name,
            ]));
        });

        Event::listen(Logout::class, function (Logout $event): void {
            Log::info('User logged out', array_merge(LogContext::make(), [
                'auth_user_id' => $event->user?->getAuthIdentifier(),
                'auth_user_name' => $event->user?->name,
            ]));
        });

        Event::listen(Failed::class, function (Failed $event): void {
            Log::notice('Authentication failed', array_merge(LogContext::make(), [
                'attempted_username' => $event->credentials['username'] ?? $event->credentials['email'] ?? null,
            ]));
        });
    }
}
