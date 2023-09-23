<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('tarefas/', 'TarefasController@index');
$router->get('tarefas/{id}', 'TarefasController@list');
$router->post('tarefas/create', 'TarefasController@create');
$router->put('tarefas/{id}', 'TarefasController@update');
$router->delete('tarefas/{id}', 'TarefasController@delete');


$router->get('subtarefas/', 'SubTarefasController@index');
$router->get('subtarefas/{id}', 'SubTarefasController@list');
$router->post('subtarefas/create', 'SubTarefasController@create');
$router->put('subtarefas/{id}', 'SubTarefasController@update');
$router->delete('subtarefas/{id}', 'SubTarefasController@delete');
