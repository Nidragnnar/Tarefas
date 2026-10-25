<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_cria_conta_e_devolve_token(): void
    {
        $this->postJson('/api/cadastrar', [
            'name' => 'Ana',
            'email' => 'Ana@Exemplo.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ])
            ->assertCreated()
            ->assertJsonStructure(['usuario' => ['id', 'name', 'email'], 'token'])
            ->assertJsonMissingPath('usuario.password');

        $this->assertDatabaseHas('users', ['email' => 'ana@exemplo.com']);
    }

    public function test_cadastro_valida_os_campos_em_portugues(): void
    {
        $this->postJson('/api/cadastrar', ['name' => 'A', 'email' => 'invalido', 'password' => '123'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'password'])
            ->assertJsonPath('errors.email.0', 'O campo e-mail deve ser um e-mail válido.');
    }

    public function test_cadastro_recusa_email_repetido(): void
    {
        User::factory()->create(['email' => 'ana@exemplo.com']);

        $this->postJson('/api/cadastrar', [
            'name' => 'Ana',
            'email' => 'ana@exemplo.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ])->assertJsonValidationErrors(['email']);
    }

    public function test_login_com_senha_certa_e_errada(): void
    {
        User::factory()->create(['email' => 'ana@exemplo.com', 'password' => 'senha-segura']);

        $this->postJson('/api/entrar', ['email' => 'ANA@exemplo.com', 'password' => 'senha-segura'])
            ->assertOk()
            ->assertJsonStructure(['token']);

        $this->postJson('/api/entrar', ['email' => 'ana@exemplo.com', 'password' => 'errada'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'E-mail ou senha incorretos.');
    }

    public function test_rotas_protegidas_exigem_login(): void
    {
        $this->getJson('/api/tarefas')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Faça login para continuar.');
    }

    public function test_sair_invalida_o_token(): void
    {
        User::factory()->create(['email' => 'ana@exemplo.com', 'password' => 'senha-segura']);
        $token = $this->postJson('/api/entrar', ['email' => 'ana@exemplo.com', 'password' => 'senha-segura'])
            ->json('token');

        $this->withToken($token)->getJson('/api/eu')->assertOk()->assertJsonPath('email', 'ana@exemplo.com');
        $this->withToken($token)->postJson('/api/sair')->assertOk();

        // Limpa o usuário guardado em memória entre as requisições do teste
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/eu')->assertUnauthorized();
    }
}
