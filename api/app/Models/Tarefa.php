<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tarefa extends Model
{
    protected $fillable = [
        'titulo',
        'descricao',
        'data_vencimento',
        'status',
    ];

    protected $attributes = [
        'status' => 'pendente',
    ];

    protected function casts(): array
    {
        return [
            'data_vencimento' => 'date:Y-m-d',
            'status' => Status::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subtarefas(): HasMany
    {
        return $this->hasMany(Subtarefa::class)->orderBy('id');
    }
}
