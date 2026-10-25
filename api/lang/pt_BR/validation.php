<?php

// Mensagens de validação em português para as regras usadas na API.
// Regras que não estão aqui usam a mensagem padrão em inglês.
return [
    'confirmed' => 'A confirmação de :attribute não confere.',
    'date' => 'O campo :attribute deve ser uma data válida.',
    'email' => 'O campo :attribute deve ser um e-mail válido.',
    'enum' => 'O valor de :attribute é inválido.',
    'max' => [
        'string' => 'O campo :attribute deve ter no máximo :max caracteres.',
    ],
    'min' => [
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'password' => [
        'min' => 'A :attribute deve ter pelo menos :min caracteres.',
    ],
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'unique' => 'Já existe uma conta com este :attribute.',

    'attributes' => [
        'name' => 'nome',
        'email' => 'e-mail',
        'password' => 'senha',
        'titulo' => 'título',
        'descricao' => 'descrição',
        'data_vencimento' => 'data de vencimento',
        'status' => 'status',
    ],
];
