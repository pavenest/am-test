<?php

$router->get('/welcome', 'WelcomeController@index');

$router->prefix('products')->withPolicy('ProductPolicy')->group(function ($router){

    $router->get('/', 'ProductController@index');
    $router->post('/', 'ProductController@create');
    $router->delete('/details/{id}', 'ProductController@deleteVariant')->int('id');

});


