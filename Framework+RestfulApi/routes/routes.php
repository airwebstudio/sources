<?php
use Libs\Router\Router;

//routes for app
Router::get('', 'Items@index');
Router::get('items', 'Items@items');
Router::post('items', 'Items@add');
Router::get('items/{id}', 'Items@item');
Router::delete('items/{id}', 'Items@delete');
Router::put('items/{id}', 'Items@done');

?>