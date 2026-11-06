<?php

use Illuminate\Support\Facades\DB;
use LogicException;

test('MySQL connections are blocked during automated testing', function () {

    config([
        'database.connections.mysql_safety_probe' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => 1,
            'database' => 'safety_probe_only',
            'username' => 'invalid',
            'password' => 'invalid',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => [],
        ],
    ]);

    expect(fn () => DB::connection('mysql_safety_probe'))
        ->toThrow(
            LogicException::class,
            'TEST SAFETY BLOCK: MySQL connections are prohibited during testing.'
        );
});