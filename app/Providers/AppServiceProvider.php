<?php declare(strict_types = 1);

namespace App\Providers;

use FilesystemIterator;
use Hashids\Hashids;
use Illuminate\Support\ServiceProvider;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Class AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrations();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $adminAuthImpl = \App\Repositories\StarCitizenWiki\Auth\AuthRepository::class;

        switch ($this->app->environment()) {
            case 'local':
                $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
                $this->app->register(\Hesto\MultiAuth\MultiAuthServiceProvider::class);
                $adminAuthImpl = \App\Repositories\StarCitizenWiki\Auth\AuthRepositoryStub::class;
                break;

            case 'testing':
                $adminAuthImpl = \App\Repositories\StarCitizenWiki\Auth\AuthRepositoryStub::class;
                break;

            case 'production':
                break;

            default:
                break;
        }

        $this->app->bind(
            \App\Repositories\StarCitizenWiki\Interfaces\AuthRepositoryInterface::class,
            $adminAuthImpl
        );

        $this->app->singleton(
            Hashids::class,
            function () {
                return new Hashids(config('api.admin_password'), 8);
            }
        );

        /**
         * Star Citizen Api Interfaces
         */
        $this->app->bind(
            \App\Repositories\StarCitizen\Interfaces\Stats\StatsRepositoryInterface::class,
            \App\Repositories\StarCitizen\ApiV1\StatsRepository::class
        );
        $this->app->bind(
            \App\Repositories\StarCitizen\Interfaces\StarmapRepositoryInterface::class,
            \App\Repositories\StarCitizen\ApiV1\StarmapRepository::class
        );

        /**
         * Star Citizen Wiki Api Interfaces
         */
        $this->app->bind(
            \App\Repositories\StarCitizenWiki\Interfaces\ShipsRepositoryInterface::class,
            \App\Repositories\StarCitizenWiki\ApiV1\ShipsRepository::class
        );
    }

    /**
     * Loads migrations in Sub-folders
     */
    private function loadMigrations()
    {
        $dirs = [];
        $directoryIterator = new RecursiveDirectoryIterator(database_path('migrations'), FilesystemIterator::SKIP_DOTS);
        $iteratorIterator = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iteratorIterator as $filename) {
            if ($filename->isDir()) {
                $dirs[] = $filename;
            }
        }

        $this->loadMigrationsFrom($dirs);
    }
}
