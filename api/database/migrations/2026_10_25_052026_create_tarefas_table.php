<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->id();
            // Cada tarefa pertence a um usuário; apagar o usuário apaga as tarefas dele
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('titulo', 150);
            $table->text('descricao')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->string('status', 20)->default('pendente');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas');
    }
};
