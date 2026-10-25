<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SubtarefaController;
use App\Http\Controllers\Api\TarefaController;
use Illuminate\Support\Facades\Route;

// Conta (limitado a 10 tentativas por minuto para dificultar ataques de força bruta)
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/cadastrar', [AuthController::class, 'cadastrar']);
    Route::post('/entrar', [AuthController::class, 'entrar']);
});

// Tudo abaixo exige o token de login
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/eu', [AuthController::class, 'eu']);
    Route::post('/sair', [AuthController::class, 'sair']);

    Route::apiResource('tarefas', TarefaController::class)
        ->parameters(['tarefas' => 'tarefa']);

    Route::post('/tarefas/{tarefa}/subtarefas', [SubtarefaController::class, 'store']);
    Route::patch('/subtarefas/{subtarefa}', [SubtarefaController::class, 'update']);
    Route::delete('/subtarefas/{subtarefa}', [SubtarefaController::class, 'destroy']);
});
