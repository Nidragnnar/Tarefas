<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_tarefas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tarefa')->constrained('tarefas');
            $table->string('titulo');
            $table->longtext('descricao');
            $table->ENUM('status', ['Pendente', 'Completa'])->default('Pendente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_tarefas');
    }
};
