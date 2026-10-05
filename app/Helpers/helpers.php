<?php

use Illuminate\Support\Facades\Route;

if (!function_exists('isRoute')) {
    function isRoute($routeName)
    {
        return Route::currentRouteName() === $routeName;
    }
}
