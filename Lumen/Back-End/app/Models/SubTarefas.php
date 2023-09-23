<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SubTarefas extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'id_tarefa', 'titulo', 'descricao',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
}
