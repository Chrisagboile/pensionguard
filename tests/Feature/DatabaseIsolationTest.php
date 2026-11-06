<?php

use Illuminate\Support\Facades\DB;

test('automated tests use an isolated SQLite in-memory database', function () {

    expect(app()->environment())->toBe('testing');

    expect(config('database.default'))->toBe('sqlite');

    expect(config('database.connections.sqlite.driver'))->toBe('sqlite');

    expect(config('database.connections.sqlite.database'))->toBe(':memory:');

    expect(DB::connection()->getDriverName())->toBe('sqlite');

    expect(DB::connection()->getDatabaseName())->toBe(':memory:');

});