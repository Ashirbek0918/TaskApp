<?php

namespace App\Providers;

use App\Models\Message;
use App\Models\Obligation;
use App\Models\Task;
use App\Policies\MessagePolicy;
use App\Policies\ObligationPolicy;
use App\Policies\TaskPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Task::class => TaskPolicy::class,
        Message::class => MessagePolicy::class,
        Obligation::class => ObligationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Gate::define('delete-task',[TaskPolicy::class,'delete']);
        Gate::define('update-message',[MessagePolicy::class,'update']);
        Gate::define('delete-message',[MessagePolicy::class,'delete']);
        Gate::define('obligation-control',[ObligationPolicy::class,'create']);
    }
}
