<?php

namespace Soulcodex\Keyable;

use Soulcodex\Keyable\Commands\DeleteApiKeyCommand;
use Soulcodex\Keyable\Commands\GenerateApiKeyCommand;
use Soulcodex\Keyable\Http\Middleware\AuthenticateApiKey;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class KeyableServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $this->registerMiddleware();
        $package
            ->name('keyable')
            ->hasConfigFile()
            ->hasMigration('create_api_keys_table')
            ->hasConfigFile()
            ->hasCommands([
                GenerateApiKeyCommand::class,
                DeleteApiKeyCommand::class,
            ]);
    }

    public function registerMiddleware()
    {
        $versionComparison = version_compare(app()->version(), '5.4.0');
        if ($versionComparison >= 0) {
            app('router')->aliasMiddleware('auth.apikey', AuthenticateApiKey::class);
        } else {
            app('router')->middleware('auth.apikey', AuthenticateApiKey::class);
        }
    }
}
