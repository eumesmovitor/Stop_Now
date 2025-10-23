<?php
/**
 * StopNow - Main Entry Point
 * Sistema de aluguel de vagas de estacionamento
 */

// Start session and load configuration
require_once 'config/config.php';
require_once 'config/utils.php';
require_once 'config/router.php';
require_once 'controllers/AuthController.php';
require_once 'controllers/SpotController.php';
require_once 'controllers/DashboardController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/BookingController.php';
require_once 'controllers/SearchController.php';
require_once 'controllers/NotificationController.php';
require_once 'controllers/MessageController.php';
require_once 'controllers/FavoriteController.php';

// Create advanced router instance
$router = new AdvancedRouter();

// Public routes (no authentication required)
$router->get('/', 'SpotController@index');
$router->get('/about', function() {
    $pageTitle = 'Sobre Nós - StopNow';
    require_once 'views/includes/header.php';
    require 'views/about.php';
    require_once 'views/includes/footer.php';
});

// Auth routes (guest middleware)
$router->group('/auth', function($router) {
    $router->get('/login', 'AuthController@showLogin', ['guest']);
    $router->post('/login', 'AuthController@login', ['guest']);
    $router->get('/register', 'AuthController@showRegister', ['guest']);
    $router->post('/register', 'AuthController@register', ['guest']);
    $router->get('/logout', 'AuthController@logout');
});

// Spot routes
$router->get('/spot/{id}', 'SpotController@show');
$router->get('/spots/{id}', 'SpotController@show');
$router->get('/list-spot', 'SpotController@create', ['auth']);
$router->post('/list-spot', 'SpotController@create', ['auth']);
$router->get('/spot/edit/{id}', 'SpotController@edit', ['auth']);
$router->post('/spot/update', 'SpotController@update', ['auth']);
$router->post('/spot/delete', 'SpotController@delete', ['auth']);

// Booking routes (authenticated)
$router->post('/booking/create', 'BookingController@create', ['auth']);
$router->post('/booking/unlock/{id}', 'BookingController@unlockRemote', ['auth']);
$router->post('/booking/complete/{id}', 'BookingController@complete', ['auth']);
$router->post('/booking/cancel/{id}', 'BookingController@cancel', ['auth']);

// Dashboard routes (authenticated)
$router->get('/dashboard', 'DashboardController@index', ['auth']);

// User profile routes (authenticated)
$router->get('/profile', 'UserController@show', ['auth']);
$router->post('/profile', 'UserController@update', ['auth']);
$router->get('/profile/edit', 'UserController@edit', ['auth']);

// API routes with CORS
$router->group('/api', function($router) {
    $router->get('/spots', 'SpotController@apiGetAll', ['cors']);
    $router->get('/spots/{id}', 'SpotController@apiGetById', ['cors']);
    $router->post('/spots', 'SpotController@apiCreate', ['auth', 'cors']);
    $router->put('/spots/{id}', 'SpotController@apiUpdate', ['auth', 'cors']);
    $router->delete('/spots/{id}', 'SpotController@apiDelete', ['auth', 'cors']);
    
    $router->get('/bookings', 'BookingController@apiGetAll', ['auth', 'cors']);
    $router->get('/bookings/{id}', 'BookingController@apiGetById', ['auth', 'cors']);
    $router->post('/bookings', 'BookingController@apiCreate', ['auth', 'cors']);
    
    $router->get('/user/stats', 'UserController@apiGetStats', ['auth', 'cors']);
});

// Admin routes (admin middleware)
$router->group('/admin', function($router) {
    $router->get('/', 'AdminController@index', ['admin']);
    $router->get('/users', 'AdminController@users', ['admin']);
    $router->get('/spots', 'AdminController@spots', ['admin']);
    $router->get('/bookings', 'AdminController@bookings', ['admin']);
    $router->get('/reports', 'AdminController@reports', ['admin']);
});

// Search and filter routes
$router->get('/search', 'SearchController@index');
$router->get('/search/advanced', 'SearchController@advanced', ['auth']);
$router->get('/search/map', 'SearchController@map');
$router->get('/search/suggestions', 'SearchController@suggestions');
$router->get('/search/autocomplete', 'SearchController@autocomplete');
$router->get('/search/filters', 'SearchController@filters');
$router->get('/search/{city}', 'SpotController@searchByCity');
$router->get('/filter', 'SpotController@filter');
$router->post('/search', 'SearchController@index');

// Availability check routes
$router->get('/spot/check-availability', 'SpotController@checkAvailability');
$router->get('/spot/unavailable-dates', 'SpotController@getUnavailableDates');
$router->get('/spot/check-date', 'SpotController@checkDateAvailability');

// Notification routes (authenticated)
$router->get('/notifications', 'NotificationController@index', ['auth']);
$router->get('/notifications/unread', 'NotificationController@getUnread', ['auth']);
$router->post('/notifications/mark-read', 'NotificationController@markAsRead', ['auth']);
$router->post('/notifications/mark-all-read', 'NotificationController@markAllAsRead', ['auth']);
$router->post('/notifications/delete', 'NotificationController@delete', ['auth']);
$router->get('/notifications/recent', 'NotificationController@getRecent', ['auth']);

// Message routes (authenticated)
$router->get('/messages', 'MessageController@index', ['auth']);
$router->get('/messages/conversation', 'MessageController@conversation', ['auth']);
$router->post('/messages/send', 'MessageController@send', ['auth']);
$router->post('/messages/mark-read', 'MessageController@markAsRead', ['auth']);
$router->post('/messages/mark-all-read', 'MessageController@markAllAsRead', ['auth']);
$router->post('/messages/delete', 'MessageController@delete', ['auth']);
$router->get('/messages/unread-count', 'MessageController@getUnreadCount', ['auth']);

// Favorite routes (authenticated)
$router->get('/favorites', 'FavoriteController@index', ['auth']);
$router->post('/favorites/toggle', 'FavoriteController@toggle', ['auth']);
$router->post('/favorites/add', 'FavoriteController@add', ['auth']);
$router->post('/favorites/remove', 'FavoriteController@remove', ['auth']);
$router->get('/favorites/check', 'FavoriteController@check', ['auth']);

// Legacy routes for backward compatibility
$router->get('/login', 'AuthController@showLogin', ['guest']);
$router->post('/login', 'AuthController@login', ['guest']);
$router->get('/register', 'AuthController@showRegister', ['guest']);
$router->post('/register', 'AuthController@register', ['guest']);
$router->get('/logout', 'AuthController@logout');

// Debug route (only in development)
if (APP_ENV === 'development') {
    $router->get('/debug/routes', function() use ($router) {
        $router->listRoutes();
    });
}

// Dispatch the request
$router->dispatch();
?>
