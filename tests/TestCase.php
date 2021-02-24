<?php

namespace Soulcodex\Keyable\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Soulcodex\Keyable\KeyableServiceProvider;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function(string $modelName){
                return 'Soulcodex\\Keyable\\Database\\Factories\\'.class_basename($modelName).'Factory';
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            KeyableServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
