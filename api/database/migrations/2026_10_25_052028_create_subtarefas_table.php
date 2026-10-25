<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subtarefas', function (Blueprint $table) {
            $table->id();
            // Apagar a tarefa apaga as subtarefas junto (antes isso travava o DELETE)
            $table->foreignId('tarefa_id')->constrained('tarefas')->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->string('status', 20)->default('pendente');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subtarefas');
    }
};
