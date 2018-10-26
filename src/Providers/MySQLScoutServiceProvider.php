<?php

namespace ComdexxSolutionsLLC\MySQLScout\Providers;

use Laravel\Scout\EngineManager;
use Illuminate\Support\ServiceProvider;
use ComdexxSolutionsLLC\MySQLScout\Engines\MySQLEngine;
use ComdexxSolutionsLLC\MySQLScout\Services\IndexService;
use ComdexxSolutionsLLC\MySQLScout\Services\ModelService;
use ComdexxSolutionsLLC\MySQLScout\Commands\ManageIndexes;
use ComdexxSolutionsLLC\MySQLScout\Engines\Modes\ModeContainer;

class MySQLScoutServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ManageIndexes::class,
            ]);
        }

        $this->app->make(EngineManager::class)->extend('mysql', function () {
            return new MySQLEngine(resolve(ModeContainer::class));
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(ModelService::class, function ($app) {
            return new ModelService();
        });

        $this->app->singleton(IndexService::class, function ($app) {
            return new IndexService($app->make(ModelService::class));
        });

        $this->app->singleton(ModeContainer::class, function ($app) {
            $engineNamespace = 'ComdexxSolutionsLLC\\MySQLScout\\Engines\\Modes\\';
            $mode = $engineNamespace.studly_case(strtolower(config('scout.mysql.mode')));
            $fallbackMode = $engineNamespace.studly_case(strtolower(config('scout.mysql.min_fulltext_search_fallback')));

            return new ModeContainer(new $mode(), new $fallbackMode());
        });
    }
}
