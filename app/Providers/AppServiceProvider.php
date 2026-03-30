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
    }
}
