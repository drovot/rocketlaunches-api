<?php

/** @var Router $router */
use Laravel\Lumen\Routing\Router;

// INDEX
$router->get('/', ['uses' => 'Controller@index', 'as' => 'index']);

// SUPPLIER
$router->get('/supplier/{supplier}', ['uses' => 'SupplierController@forcePull', 'as' => 'supplier.pull', 'middleware' => 'tracking']);

// REMINDERS
$router->get('/reminders/daily', ['uses' => 'ReminderController@sendReminder', 'as' => 'reminder.send', 'middleware' => 'tracking']);

// SEARCH
$router->post('/advanced-search', ['uses' => 'SearchController@advancedSearch', 'as' => 'search.advanced', 'middleware' => 'tracking']);

// USER
$router->get('/user', ['uses' => 'UserController@getUser', 'as' => 'user.get', 'middleware' => 'authentication|tracking']);
$router->post('/user', ['uses' => 'UserController@createUser', 'as' => 'user.create', 'middleware' => 'authentication|tracking']);
$router->put('/user', ['uses' => 'UserController@updateUser', 'as' => 'user.update', 'middleware' => 'authentication|tracking']);
$router->delete('/user', ['uses' => 'UserController@deleteUser', 'as' => 'user.delete', 'middleware' => 'authentication|tracking']);

// LAUNCH
$router->get('/launch', ['uses' => 'LaunchController@getLaunches', 'as' => 'launch.list', 'middleware' => 'tracking|admin']); // admin
$router->get('/launch/upcoming', ['uses' => 'LaunchController@getUpcomingLaunches', 'as' => 'launch.upcoming', 'middleware' => 'tracking']);
$router->get('/launch/previous', ['uses' => 'LaunchController@getPreviousLaunches', 'as' => 'launch.previous', 'middleware' => 'tracking']);
$router->get('/launch/unpublished', ['uses' => 'LaunchController@getUnpublishedLaunches', 'as' => 'launch.unpublished', 'middleware' => 'tracking|admin']); // admin
$router->post('/launch', ['uses' => 'LaunchController@createLaunch', 'as' => 'launch.create', 'middleware' => 'tracking|admin']); // admin
$router->get('/launch/{launch}', ['uses' => 'LaunchController@getLaunch', 'as' => 'launch.get', 'middleware' => 'tracking']);
$router->post('/launch/{launch}', ['uses' => 'LaunchController@updateLaunch', 'as' => 'launch.edit', 'middleware' => 'tracking|admin']); // admin
$router->delete('/launch/{launch}', ['uses' => 'LaunchController@deleteLaunch', 'as' => 'launch.delete', 'middleware' => 'tracking|admin']); // admin

$router->get('/launch/provider/{provider}', ['uses' => 'LaunchController@getLaunchesByProvider', 'as' => 'launch.provider', 'middleware' => 'tracking']);
$router->get('/launch/rocket/{rocket}', ['uses' => 'LaunchController@getLaunchesByRocket', 'as' => 'launch.rocket', 'middleware' => 'tracking']);
$router->get('/launch/pad/{pad}', ['uses' => 'LaunchController@getLaunchesByPad', 'as' => 'launch.pad', 'middleware' => 'tracking']);

// Rocket
$router->get('/rocket', ['uses' => 'RocketController@getRockets', 'as' => 'rocket.list', 'middleware' => 'tracking']);
$router->post('/rocket', ['uses' => 'RocketController@createRocket', 'as' => 'rocket.create', 'middleware' => 'tracking|admin']); // admin
$router->get('/rocket/{rocket}', ['uses' => 'RocketController@getRocket', 'as' => 'rocket.get', 'middleware' => 'tracking']);
$router->post('/rocket/{rocket}', ['uses' => 'RocketController@updateRocket', 'as' => 'rocket.edit', 'middleware' => 'tracking|admin']); // admin
$router->delete('/rocket/{rocket}', ['uses' => 'RocketController@deleteRocket', 'as' => 'rocket.delete', 'middleware' => 'tracking|admin']); // admin

// Provider
$router->get('/provider', ['uses' => 'ProviderController@getProviders', 'as' => 'provider.list', 'middleware' => 'tracking']);
$router->post('/provider', ['uses' => 'ProviderController@createProvider', 'as' => 'provider.create', 'middleware' => 'tracking|admin']); // admin
$router->get('/provider/{provider}', ['uses' => 'ProviderController@getProvider', 'as' => 'provider.get', 'middleware' => 'tracking']);
$router->post('/provider/{provider}', ['uses' => 'ProviderController@updateProvider', 'as' => 'provider.edit', 'middleware' => 'tracking|admin']); // admin
$router->delete('/provider/{provider}', ['uses' => 'ProviderController@deleteProvider', 'as' => 'provider.delete', 'middleware' => 'tracking|admin']); // admin

// Pad
$router->get('/pad', ['uses' => 'PadController@getPads', 'as' => 'pad.list', 'middleware' => 'tracking']);
$router->post('/pad', ['uses' => 'PadController@createPad', 'as' => 'pad.create', 'middleware' => 'tracking|admin']); // admin
$router->get('/pad/{pad}', ['uses' => 'PadController@getPad', 'as' => 'pad.get', 'middleware' => 'tracking']);
$router->post('/pad/{pad}', ['uses' => 'PadController@updatePad', 'as' => 'pad.edit', 'middleware' => 'tracking|admin']); // admin
$router->delete('/pad/{pad}', ['uses' => 'PadController@deletePad', 'as' => 'pad.delete', 'middleware' => 'tracking|admin']); // admin
