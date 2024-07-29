<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// cors problem....
$router->options(
    '/{any:.*}',
    [
        'middleware' => ['cors'],
        function () {
            return response(['status' => 'success']);
        }
    ]
);

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->post('/users/login', 'UserController@login');

$router->group(['prefix' => 'api', 'middleware' => 'auth'], function () use ($router) {
    $router->get('/users/me', 'UserController@me');
    $router->get('/users/logout', 'UserController@logout');

    $router->group(['prefix' => 'pengaturan_jadwal'], function () use ($router) {
        $router->get('/', 'PengaturanJadwalController@index');
    });

    $router->group(['prefix' => 'absensi'], function () use ($router) {
        $router->get('/', 'AbsensiController@index');
        $router->get('/{id}', 'AbsensiController@show');
        $router->get('/export/{id}', 'AbsensiController@exportSingle');
        $router->post('/', 'AbsensiController@store');
        $router->delete('/{id}', 'AbsensiController@destroy');
    });

    $router->group(['prefix' => 'detail_absensi'], function () use ($router) {
        $router->get('/{id}', 'DetailAbsensiController@index');
        $router->post('/', 'DetailAbsensiController@store');
        $router->put('/update_libur/{id}', 'DetailAbsensiController@updateLibur');
    });
});

// Route untuk tes koneksi database
$router->get('/cobaini', 'AbsensiController@index');
$router->get('/test-db-connection', function () {
    try {
        \DB::connection()->getPdo();
        return response()->json(['status' => 'success', 'message' => 'Database connection is successful']);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'Could not connect to the database. Please check your configuration.'], 500);
    }
});
