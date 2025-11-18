<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ],

        'pgsql20' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_wcd',
        ],
        'pgsql_ifsc' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'ifsc',
        ],
        'pgsql_ifms' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'ifms',
        ],
        'pgsql_appwrite' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme',
        ],
        'pgsql_appread_local' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme',
        ],
        'pgsql_appread_local1' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme',
        ],
       
        'pgsql_appread_server1' => [
            'driver' => 'pgsql',
            'host' => '172.24.12.12',
            'port' => env('DB_PORT', '5432'),
            'database' => 'lakshmir_bhandar',
            'username' => 'postgres',
            'password' => 'LB@post#123$',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme',
        ],
        'pgsql_appread' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme',
        ],
        // 'pgsql_encwrite' => [
        //     'driver' => 'pgsql',
        //     'host' => 'localhost',
        //     'port' => env('DB_PORT', '5432'),
        //     'database' => env('DB_DATABASE', 'lakshmir_bhandar_image'),
        //     'username' => env('DB_USERNAME', 'postgres'),
        //     'password' => env('DB_PASSWORD', 'root'),
        //     'charset' => 'utf8',
        //     'prefix' => '',
        //     'schema' => 'lb_scheme',
        // ],
        'pgsql_encwrite' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'lakshmir_bhandar_image',
            'username' => 'postgres',
            'password' => 'root',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme'
        ],
        'pgsql_encread' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'lakshmir_bhandar_image',
            'username' => 'postgres',
            'password' => 'root',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme'
        ],
        /*'pgsql_encread' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lakshmir_bhandar_image'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_scheme',
        ],*/
        'pgsql_mis' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
        ],
        'pgsql_payment_local' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_wcd',
        ],
        'pgsql_payment_local1' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'lb_main_prod'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', 'root'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_wcd',
        ],
        'pgsql_payment' => [
            'driver' => 'pgsql',
            'host' => '127.0.0.1',
            'port' => '5432',
            'database' => 'lb_payment_prod',
            'username' => 'postgres',
            'password' => 'root',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'trx_mgmt_2122'
        ],
        'pgsql_payment_server1' => [
            'driver' => 'pgsql',
            'host' => '172.24.12.5',
            'port' => '5432',
            'database' => 'lb_payment_prod',
            'username' => 'postgres',
            'password' => 'postgres',
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'lb_wcd',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
