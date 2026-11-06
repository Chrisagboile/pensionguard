<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use LogicException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (app()->environment('testing')) {

            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            $database = config("database.connections.{$connection}.database");

            if ($driver !== 'sqlite' || $database !== ':memory:') {
                throw new LogicException(
                    'TEST SAFETY BLOCK: Only SQLite in-memory is permitted.'
                );
            }

            // Prevent tests from explicitly opening a MySQL connection.
            DB::extend('mysql', function ($config, $name) {
                throw new LogicException(
                    'TEST SAFETY BLOCK: MySQL connections are prohibited during testing.'
                );
            });
        }
    }
}
