<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Subtarefa;
use App\Models\Tarefa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class SubtarefaController extends Controller
{
    public function store(Request $request, Tarefa $tarefa): JsonResponse
    {
        abort_unless((int) $tarefa->user_id === (int) $request->user()->id, 404);

        $dados = $request->validate([
            'titulo' => ['required', 'string', 'min:2', 'max:150'],
            'status' => ['sometimes', Rule::enum(Status::class)],
        ]);

        return response()->json($tarefa->subtarefas()->create($dados), 201);
    }

    public function update(Request $request, Subtarefa $subtarefa): JsonResponse
    {
        $this->garantirDono($request, $subtarefa);

        $subtarefa->update($request->validate([
            'titulo' => ['sometimes', 'string', 'min:2', 'max:150'],
            'status' => ['sometimes', Rule::enum(Status::class)],
        ]));

        return response()->json($subtarefa);
    }

    public function destroy(Request $request, Subtarefa $subtarefa): Response
    {
        $this->garantirDono($request, $subtarefa);
        $subtarefa->delete();

        return response()->noContent();
    }

    // A subtarefa é de quem é dono da tarefa "mãe"
    private function garantirDono(Request $request, Subtarefa $subtarefa): void
    {
        abort_unless((int) $subtarefa->tarefa->user_id === (int) $request->user()->id, 404);
    }
}
