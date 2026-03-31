<?php

namespace App\Providers;

use App\Models\AppSetting;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Observers\EntityActivityObserver;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        User::observe(EntityActivityObserver::class);
        SchoolClass::observe(EntityActivityObserver::class);
        Subject::observe(EntityActivityObserver::class);
        Course::observe(EntityActivityObserver::class);
        QuestionBank::observe(EntityActivityObserver::class);
        Question::observe(EntityActivityObserver::class);
        Exam::observe(EntityActivityObserver::class);
        AppSetting::observe(EntityActivityObserver::class);

        Paginator::defaultView('vendor.pagination.teramia');
        Paginator::defaultSimpleView('vendor.pagination.teramia-simple');

        View::composer('*', function ($view): void {
            static $appSetting = null;

            if ($appSetting === null) {
                $appSetting = Schema::hasTable('app_settings')
                    ? AppSetting::query()->first()
                    : null;
            }

            $view->with('teraApp', [
                'app_name' => $appSetting->app_name ?? config('app.name', 'Teramia E-Learning'),
                'school_name' => $appSetting->school_name ?? 'SMP Teramia',
                'logo' => $appSetting->school_logo ?? null,
                'favicon' => $appSetting->school_favicon ?? null,
                'footer_text' => $appSetting->footer_text ?? null,
                'primary_color' => $appSetting->primary_color ?? '#1D4ED8',
                'secondary_color' => $appSetting->secondary_color ?? '#1E3A8A',
                'accent_color' => $appSetting->accent_color ?? '#FACC15',
            ]);
        });
    }
}
