<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\Tarefa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class TarefaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate(['status' => ['nullable', Rule::enum(Status::class)]]);

        // with('subtarefas') busca todas as subtarefas numa consulta só (antes era uma por tarefa)
        $tarefas = $request->user()->tarefas()
            ->with('subtarefas')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'pendente' THEN 0 ELSE 1 END")
            ->orderByRaw('data_vencimento IS NULL, data_vencimento')
            ->latest('id')
            ->get();

        return response()->json($tarefas);
    }

    public function store(Request $request): JsonResponse
    {
        $tarefa = $request->user()->tarefas()->create($this->validar($request));

        return response()->json($tarefa->load('subtarefas'), 201);
    }

    public function show(Request $request, Tarefa $tarefa): JsonResponse
    {
        $this->garantirDono($request, $tarefa);

        return response()->json($tarefa->load('subtarefas'));
    }

    public function update(Request $request, Tarefa $tarefa): JsonResponse
    {
        $this->garantirDono($request, $tarefa);
        $tarefa->update($this->validar($request, atualizando: true));

        return response()->json($tarefa->load('subtarefas'));
    }

    public function destroy(Request $request, Tarefa $tarefa): Response
    {
        $this->garantirDono($request, $tarefa);
        $tarefa->delete();

        return response()->noContent();
    }

    // Ao atualizar, todos os campos são opcionais (dá para mudar só o status, por exemplo)
    private function validar(Request $request, bool $atualizando = false): array
    {
        $obrigatorio = $atualizando ? 'sometimes' : 'required';

        return $request->validate([
            'titulo' => [$obrigatorio, 'string', 'min:2', 'max:150'],
            'descricao' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'data_vencimento' => ['sometimes', 'nullable', 'date'],
            'status' => ['sometimes', Rule::enum(Status::class)],
        ]);
    }

    // Tarefa de outra pessoa responde 404, como se não existisse
    private function garantirDono(Request $request, Tarefa $tarefa): void
    {
        abort_unless((int) $tarefa->user_id === (int) $request->user()->id, 404);
    }
}
