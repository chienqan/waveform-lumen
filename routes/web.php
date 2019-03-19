<?php

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

$router->get('/', function () use ($router) {
    $result = new StdClass;
    $result->message = "not found";
    return response()->json($result);
});

$router->group(['prefix' => 'transforms'], function () use ($router) {
    $router->post('/wav2png', 'TransformController@wav2png');
    $router->post('/primitive', 'TransformController@primitive');
});

$router->group(['prefix' => 'samples'], function () use ($router) {
    $router->get('/wav2png', 'SampleController@wav2png');
    $router->get('/primitive', 'SampleController@primitive');
});

$router->get('/s3/presigned', 'S3Controller@presigned');
$router->get('/downloads', 'DownloadController@process');
