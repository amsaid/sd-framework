<?php

declare(strict_types=1);

use SdFramework\Routing\RouteCollection;

return function (RouteCollection $routes) {
    // Public routes
    $routes->get('/', 'HomeController@index');
    $routes->get('/about', 'HomeController@about');
    
    // Contact form
    $routes->group(['prefix' => '/contact'], function (RouteCollection $routes) {
        $routes->get('/', 'ContactController@show');
        $routes->post('/', 'ContactController@submit');
        $routes->get('/success', 'ContactController@success');
    });

    // Authentication
    $routes->group([
        'prefix' => '/auth',
        'namespace' => 'App\\Controllers\\Auth'
    ], function (RouteCollection $routes) {
        $routes->get('/login', 'AuthController@showLoginForm');
        $routes->post('/login', 'AuthController@login');
        $routes->get('/register', 'AuthController@showRegistrationForm');
        $routes->post('/register', 'AuthController@register');
        $routes->post('/logout', 'AuthController@logout')
            ->middleware('auth');
    });

    // User dashboard
    $routes->group([
        'prefix' => '/dashboard',
        'middleware' => ['auth', 'verified']
    ], function (RouteCollection $routes) {
        $routes->get('/', 'DashboardController@index');
        $routes->get('/profile', 'DashboardController@profile');
        $routes->post('/profile', 'DashboardController@updateProfile');
    });
};
