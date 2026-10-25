<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function cadastrar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $dados['email'] = mb_strtolower($dados['email']);

        $usuario = User::create($dados);

        return response()->json($this->sessao($usuario), 201);
    }

    public function entrar(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $usuario = User::where('email', mb_strtolower($dados['email']))->first();

        // Mesma mensagem para e-mail ou senha errados, para não revelar quais e-mails existem
        if (! $usuario || ! Hash::check($dados['password'], $usuario->password)) {
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.']);
        }

        return response()->json($this->sessao($usuario));
    }

    public function sair(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Você saiu da sua conta.']);
    }

    public function eu(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    // Cria um token de acesso que o front-end envia no cabeçalho Authorization
    private function sessao(User $usuario): array
    {
        return [
            'usuario' => $usuario,
            'token' => $usuario->createToken('web')->plainTextToken,
        ];
    }
}
