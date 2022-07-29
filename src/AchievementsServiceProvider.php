<?php
declare(strict_types=1);

namespace ITVOLAND\Achievements;

use ITVOLAND\Achievements\Console\AchievementChainMakeCommand;
use ITVOLAND\Achievements\Console\AchievementMakeCommand;
use ITVOLAND\Achievements\Console\LoadAchievementsCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class AchievementsServiceProvider
 *
 * @package ITVOLAND\Achievements
 */
class AchievementsServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');
        if ($this->app->runningInConsole()) {
            $this->commands(
                [
                    AchievementMakeCommand::class,
                    AchievementChainMakeCommand::class,
                    LoadAchievementsCommand::class
                ]
            );
        }
        $this->app[Achievement::class] = static function ($app) {
            return $app['itvoland.achievements.achievement'];
        };
        $this->publishes(
            [
                __DIR__ . '/config/achievements.php' => config_path('achievements.php'),
            ],
            'config'
        );
        $this->publishes(
            [
                __DIR__ . '/Migrations/2022_07_29_140012_create_achievements_tables.php' => database_path('migrations/2022_07_29_140012_create_achievements_tables.php')
            ],
            'migrations'
        );
        $this->mergeConfigFrom(__DIR__ . '/config/achievements.php', 'achievements');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
