<?php

declare(strict_types=1);

use SdFramework\Routing\RouteCollection;

return function (RouteCollection $routes) {
    $routes->group([
        'prefix' => '/api',
        'namespace' => 'App\\Controllers\\Api',
        'middleware' => ['api', 'throttle']
    ], function (RouteCollection $routes) {
        // Search endpoints
        $routes->group(['prefix' => '/search'], function (RouteCollection $routes) {
            $routes->get('/', 'SearchController@index')
                ->where('q', '[a-zA-Z0-9\s]+')
                ->where('type', '[a-zA-Z]+');
                
            $routes->get('/{type}', 'SearchController@byType')
                ->where('type', '[a-zA-Z]+');
        });

        // User management
        $routes->group([
            'prefix' => '/users',
            'middleware' => ['auth:api']
        ], function (RouteCollection $routes) {
            $routes->get('/', 'UserController@index');
            $routes->post('/', 'UserController@store');
            $routes->get('/{id}', 'UserController@show')
                ->where('id', '\d+');
            $routes->put('/{id}', 'UserController@update')
                ->where('id', '\d+');
            $routes->delete('/{id}', 'UserController@delete')
                ->where('id', '\d+');
        });

        // Authentication
        $routes->group(['prefix' => '/auth'], function (RouteCollection $routes) {
            $routes->post('/login', 'AuthController@login');
            $routes->post('/register', 'AuthController@register');
            $routes->post('/logout', 'AuthController@logout')
                ->middleware('auth:api');
            $routes->post('/refresh', 'AuthController@refresh')
                ->middleware('auth:api');
        });
    });
};
