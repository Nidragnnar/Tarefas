<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarefas extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'titulo', 'descricao', 'data_vencimento'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */    public function subs (){
     return $this->hasMany(SubTarefa::class, 'id_tarefa');
     }
    }
