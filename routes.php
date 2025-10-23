<?php
/**
 * StopNow Routes Documentation
 * 
 * This file documents all available routes in the application
 */

return [
    // Public Routes
    'GET /' => 'SpotController@index',
    'GET /about' => 'About page',
    
    // Authentication Routes
    'GET /login' => 'AuthController@showLogin (guest middleware)',
    'POST /login' => 'AuthController@login (guest middleware)',
    'GET /register' => 'AuthController@showRegister (guest middleware)',
    'POST /register' => 'AuthController@register (guest middleware)',
    'GET /logout' => 'AuthController@logout',
    
    // Auth Group Routes
    'GET /auth/login' => 'AuthController@showLogin (guest middleware)',
    'POST /auth/login' => 'AuthController@login (guest middleware)',
    'GET /auth/register' => 'AuthController@showRegister (guest middleware)',
    'POST /auth/register' => 'AuthController@register (guest middleware)',
    'GET /auth/logout' => 'AuthController@logout',
    
    // Spot Routes
    'GET /spot/{id}' => 'SpotController@show',
    'GET /list-spot' => 'SpotController@create (auth middleware)',
    'POST /list-spot' => 'SpotController@create (auth middleware)',
    'GET /search' => 'SpotController@search',
    'GET /search/{city}' => 'SpotController@searchByCity',
    'GET /filter' => 'SpotController@filter',
    
    // Booking Routes
    'POST /booking/create' => 'BookingController@create (auth middleware)',
    'POST /booking/unlock/{id}' => 'BookingController@unlockRemote (auth middleware)',
    
    // Dashboard Routes
    'GET /dashboard' => 'DashboardController@index (auth middleware)',
    
    // User Profile Routes
    'GET /profile' => 'UserController@show (auth middleware)',
    'POST /profile' => 'UserController@update (auth middleware)',
    'GET /profile/edit' => 'UserController@edit (auth middleware)',
    
    // API Routes (with CORS)
    'GET /api/spots' => 'SpotController@apiGetAll (cors middleware)',
    'GET /api/spots/{id}' => 'SpotController@apiGetById (cors middleware)',
    'POST /api/spots' => 'SpotController@apiCreate (auth, cors middleware)',
    'PUT /api/spots/{id}' => 'SpotController@apiUpdate (auth, cors middleware)',
    'DELETE /api/spots/{id}' => 'SpotController@apiDelete (auth, cors middleware)',
    
    'GET /api/bookings' => 'BookingController@apiGetAll (auth, cors middleware)',
    'GET /api/bookings/{id}' => 'BookingController@apiGetById (auth, cors middleware)',
    'POST /api/bookings' => 'BookingController@apiCreate (auth, cors middleware)',
    
    'GET /api/user/stats' => 'UserController@apiGetStats (auth, cors middleware)',
    
    // Admin Routes (admin middleware)
    'GET /admin' => 'AdminController@index (auth middleware)',
    'GET /admin/users' => 'AdminController@users (admin middleware)',
    'GET /admin/spots' => 'AdminController@spots (admin middleware)',
    'GET /admin/bookings' => 'AdminController@bookings (admin middleware)',
    'GET /admin/reports' => 'AdminController@reports (admin middleware)',
    
    // Debug Routes (development only)
    'GET /debug/routes' => 'List all registered routes (development only)',
];

/*
Middleware Types:
- auth: Requires user to be logged in
- guest: Requires user to NOT be logged in
- admin: Requires admin privileges
- cors: Adds CORS headers
- api: Sets JSON content type

Route Parameters:
- {id}: Numeric ID parameter
- {city}: String parameter for city name

Example Usage:
- GET /spot/123 -> SpotController@show with params['id'] = '123'
- GET /search/São Paulo -> SpotController@searchByCity with params['city'] = 'São Paulo'
- POST /booking/create -> BookingController@create (requires authentication)

API Endpoints:
All API routes return JSON responses and include CORS headers.
Authentication required for POST, PUT, DELETE operations.

Admin Endpoints:
All admin routes require authentication and admin privileges.
Provides dashboard, user management, spot management, and reports.
*/
?>





