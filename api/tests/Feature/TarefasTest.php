<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TarefasTest extends TestCase
{
    use RefreshDatabase;

    private User $ana;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ana = User::factory()->create();
        Sanctum::actingAs($this->ana);
    }

    private function criarTarefa(array $dados = []): array
    {
        return $this->postJson('/api/tarefas', $dados + ['titulo' => 'Estudar Laravel'])
            ->assertCreated()
            ->json();
    }

    public function test_cria_tarefa_com_status_pendente_por_padrao(): void
    {
        $this->postJson('/api/tarefas', [
            'titulo' => 'Estudar Laravel',
            'descricao' => 'Rotas e controllers',
            'data_vencimento' => '2026-11-01',
        ])
            ->assertCreated()
            ->assertJsonPath('status', 'pendente')
            ->assertJsonPath('data_vencimento', '2026-11-01')
            ->assertJsonPath('subtarefas', []);
    }

    public function test_valida_tarefa(): void
    {
        $this->postJson('/api/tarefas', ['titulo' => '', 'data_vencimento' => 'ontem', 'status' => 'talvez'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['titulo', 'data_vencimento', 'status']);
    }

    public function test_mostra_tarefa_com_subtarefas(): void
    {
        // Antes, abrir uma tarefa sempre dava "Tarefa não encontrada"
        $tarefa = $this->criarTarefa();
        $this->postJson("/api/tarefas/{$tarefa['id']}/subtarefas", ['titulo' => 'Ler a documentação'])->assertCreated();

        $this->getJson("/api/tarefas/{$tarefa['id']}")
            ->assertOk()
            ->assertJsonPath('titulo', 'Estudar Laravel')
            ->assertJsonPath('subtarefas.0.titulo', 'Ler a documentação');
    }

    public function test_atualiza_so_o_status(): void
    {
        // Antes, o status não podia ser alterado
        $tarefa = $this->criarTarefa();

        $this->patchJson("/api/tarefas/{$tarefa['id']}", ['status' => 'concluida'])
            ->assertOk()
            ->assertJsonPath('status', 'concluida')
            ->assertJsonPath('titulo', 'Estudar Laravel');
    }

    public function test_lista_com_filtro_e_pendentes_primeiro(): void
    {
        $feita = $this->criarTarefa(['titulo' => 'Feita', 'status' => 'concluida']);
        $this->criarTarefa(['titulo' => 'Pendente sem data']);
        $this->criarTarefa(['titulo' => 'Pendente com data', 'data_vencimento' => '2026-12-01']);

        $this->getJson('/api/tarefas')
            ->assertOk()
            ->assertJsonCount(3)
            ->assertJsonPath('0.titulo', 'Pendente com data')
            ->assertJsonPath('1.titulo', 'Pendente sem data')
            ->assertJsonPath('2.id', $feita['id']);

        $this->getJson('/api/tarefas?status=concluida')->assertJsonCount(1)->assertJsonPath('0.titulo', 'Feita');
    }

    public function test_apagar_tarefa_apaga_as_subtarefas(): void
    {
        // Antes, apagar uma tarefa com subtarefas falhava
        $tarefa = $this->criarTarefa();
        $this->postJson("/api/tarefas/{$tarefa['id']}/subtarefas", ['titulo' => 'Sub 1']);
        $this->postJson("/api/tarefas/{$tarefa['id']}/subtarefas", ['titulo' => 'Sub 2']);

        $this->deleteJson("/api/tarefas/{$tarefa['id']}")->assertNoContent();

        $this->assertDatabaseCount('tarefas', 0);
        $this->assertDatabaseCount('subtarefas', 0);
        $this->getJson("/api/tarefas/{$tarefa['id']}")->assertNotFound()->assertJsonPath('message', 'Não encontrado.');
    }

    public function test_crud_de_subtarefas(): void
    {
        $tarefa = $this->criarTarefa();

        $this->postJson("/api/tarefas/{$tarefa['id']}/subtarefas", [])->assertJsonValidationErrors(['titulo']);

        $sub = $this->postJson("/api/tarefas/{$tarefa['id']}/subtarefas", ['titulo' => 'Sub'])
            ->assertCreated()
            ->assertJsonPath('status', 'pendente')
            ->json();

        $this->patchJson("/api/subtarefas/{$sub['id']}", ['status' => 'concluida'])
            ->assertOk()
            ->assertJsonPath('status', 'concluida');

        $this->deleteJson("/api/subtarefas/{$sub['id']}")->assertNoContent();
        $this->assertDatabaseCount('subtarefas', 0);
    }

    public function test_ninguem_mexe_nas_tarefas_dos_outros(): void
    {
        $tarefa = $this->criarTarefa();
        $sub = $this->postJson("/api/tarefas/{$tarefa['id']}/subtarefas", ['titulo' => 'Sub'])->json();

        Sanctum::actingAs(User::factory()->create());

        $this->getJson('/api/tarefas')->assertOk()->assertJsonCount(0);
        $this->getJson("/api/tarefas/{$tarefa['id']}")->assertNotFound();
        $this->patchJson("/api/tarefas/{$tarefa['id']}", ['titulo' => 'Hackeada'])->assertNotFound();
        $this->deleteJson("/api/tarefas/{$tarefa['id']}")->assertNotFound();
        $this->postJson("/api/tarefas/{$tarefa['id']}/subtarefas", ['titulo' => 'Intrusa'])->assertNotFound();
        $this->patchJson("/api/subtarefas/{$sub['id']}", ['status' => 'concluida'])->assertNotFound();
        $this->deleteJson("/api/subtarefas/{$sub['id']}")->assertNotFound();

        $this->assertDatabaseHas('tarefas', ['id' => $tarefa['id'], 'titulo' => 'Estudar Laravel']);
        $this->assertDatabaseHas('subtarefas', ['id' => $sub['id'], 'status' => 'pendente']);
    }
}
