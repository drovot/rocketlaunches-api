<?php

/** @var Router $router */
use Laravel\Lumen\Routing\Router;

// INDEX
$router->get('/', ['uses' => 'Controller@index', 'as' => 'index']);

// SUPPLIER
$router->post('/supplier/{supplier}', ['uses' => 'SupplierController@forcePull', 'as' => 'supplier.pull', 'middleware' => 'admin']); // admin

// SEARCH
$router->post('/advanced-search', ['uses' => 'SearchController@advancedSearch', 'as' => 'search.advanced']);

// LAUNCH
$router->get('/launch', ['uses' => 'LaunchController@getLaunches', 'as' => 'launch.list', 'middleware' => 'admin']); // admin
$router->get('/launch/upcoming', ['uses' => 'LaunchController@getUpcomingLaunches', 'as' => 'launch.upcoming']);
$router->get('/launch/previous', ['uses' => 'LaunchController@getPreviousLaunches', 'as' => 'launch.previous']);
$router->get('/launch/unpublished', ['uses' => 'LaunchController@getUnpublishedLaunches', 'as' => 'launch.unpublished', 'middleware' => 'admin']); // admin
$router->post('/launch', ['uses' => 'LaunchController@createLaunch', 'as' => 'launch.create', 'middleware' => 'admin']); // admin
$router->get('/launch/{launch}', ['uses' => 'LaunchController@getLaunch', 'as' => 'launch.get']);
$router->post('/launch/{launch}', ['uses' => 'LaunchController@updateLaunch', 'as' => 'launch.edit', 'middleware' => 'admin']); // admin
$router->delete('/launch/{launch}', ['uses' => 'LaunchController@deleteLaunch', 'as' => 'launch.delete', 'middleware' => 'admin']); // admin

$router->get('/launch/provider/{provider}', ['uses' => 'LaunchController@getLaunchesByProvider', 'as' => 'launch.provider']);
$router->get('/launch/rocket/{rocket}', ['uses' => 'LaunchController@getLaunchesByRocket', 'as' => 'launch.rocket']);
$router->get('/launch/pad/{pad}', ['uses' => 'LaunchController@getLaunchesByPad', 'as' => 'launch.pad']);

// Rocket
$router->get('/rocket', ['uses' => 'RocketController@getRockets', 'as' => 'rocket.list']);
$router->post('/rocket', ['uses' => 'RocketController@createRocket', 'as' => 'rocket.create', 'middleware' => 'admin']); // admin
$router->get('/rocket/{rocket}', ['uses' => 'RocketController@getRocket', 'as' => 'rocket.get']);
$router->post('/rocket/{rocket}', ['uses' => 'RocketController@updateRocket', 'as' => 'rocket.edit', 'middleware' => 'admin']); // admin
$router->delete('/rocket/{rocket}', ['uses' => 'RocketController@deleteRocket', 'as' => 'rocket.delete', 'middleware' => 'admin']); // admin

// Provider
$router->get('/provider', ['uses' => 'ProviderController@getProviders', 'as' => 'provider.list']);
$router->post('/provider', ['uses' => 'ProviderController@createProvider', 'as' => 'provider.create', 'middleware' => 'admin']); // admin
$router->get('/provider/{provider}', ['uses' => 'ProviderController@getProvider', 'as' => 'provider.get']);
$router->post('/provider/{provider}', ['uses' => 'ProviderController@updateProvider', 'as' => 'provider.edit', 'middleware' => 'admin']); // admin
$router->delete('/provider/{provider}', ['uses' => 'ProviderController@deleteProvider', 'as' => 'provider.delete', 'middleware' => 'admin']); // admin

// Pad
$router->get('/pad', ['uses' => 'PadController@getPads', 'as' => 'pad.list']);
$router->post('/pad', ['uses' => 'PadController@createPad', 'as' => 'pad.create', 'middleware' => 'admin']); // admin
$router->get('/pad/{pad}', ['uses' => 'PadController@getPad', 'as' => 'pad.get']);
$router->post('/pad/{pad}', ['uses' => 'PadController@updatePad', 'as' => 'pad.edit', 'middleware' => 'admin']); // admin
$router->delete('/pad/{pad}', ['uses' => 'PadController@deletePad', 'as' => 'pad.delete', 'middleware' => 'admin']); // admin
