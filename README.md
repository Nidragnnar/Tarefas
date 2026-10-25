# Tarefas

Lista de tarefas com **subtarefas** e **login**, feita com uma **API em Laravel** e um **front-end em Vue 3**. Cada pessoa cria uma conta e vê só as próprias tarefas.

Este projeto junta e corrige duas versões antigas: o [CRUD_Tarefas](https://github.com/Nidragnnar/CRUD_Tarefas) (API em Lumen + telas em Vue 2) e a primeira versão deste repositório (API em Lumen com subtarefas).

## Recursos

- Criar conta, entrar e sair
- Criar, editar, concluir e apagar tarefas, com descrição e data de vencimento opcionais
- Subtarefas em cada tarefa, com barra de progresso
- Filtros: todas, pendentes e concluídas
- Tarefas vencidas destacadas em vermelho
- Pendentes primeiro, ordenadas pela data de vencimento
- Tema claro e escuro automático e layout responsivo

## Estrutura

```
Tarefas/
├── api/   # Laravel 12 + Sanctum (API REST em JSON)
└── web/   # Vue 3 + Vite (telas)
```

## Como rodar

Precisa de **PHP 8.2+**, **Composer** e **Node.js 18+**. O banco é **SQLite**, então não precisa instalar servidor de banco.

### 1. API

```bash
cd api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

A API fica em http://localhost:8000/api. O banco é criado em `api/database/database.sqlite`.

### 2. Front-end

Em outro terminal:

```bash
cd web
npm install
npm run dev
```

Acesse http://localhost:5173 e crie sua conta.

> Se a API estiver em outro endereço, copie `web/.env.example` para `web/.env` e ajuste `VITE_API_URL`.

### Testes da API

```bash
cd api
php artisan test
```

## API

Todas as rotas começam com `/api`. As marcadas com 🔒 exigem o cabeçalho `Authorization: Bearer <token>`, que vem no cadastro e no login.

| Método | Rota | O que faz |
| --- | --- | --- |
| POST | `/cadastrar` | Cria conta (`name`, `email`, `password`, `password_confirmation`) e devolve o token |
| POST | `/entrar` | Login (`email`, `password`) e devolve o token |
| POST | `/sair` 🔒 | Invalida o token atual |
| GET | `/eu` 🔒 | Dados do usuário logado |
| GET | `/tarefas` 🔒 | Lista as tarefas com as subtarefas (filtro opcional `?status=pendente` ou `concluida`) |
| POST | `/tarefas` 🔒 | Cria tarefa (`titulo`, `descricao`, `data_vencimento`, `status`) |
| GET | `/tarefas/{id}` 🔒 | Mostra uma tarefa com as subtarefas |
| PATCH | `/tarefas/{id}` 🔒 | Atualiza qualquer campo, por exemplo só o `status` |
| DELETE | `/tarefas/{id}` 🔒 | Apaga a tarefa e as subtarefas dela |
| POST | `/tarefas/{id}/subtarefas` 🔒 | Cria subtarefa (`titulo`, `status`) |
| PATCH | `/subtarefas/{id}` 🔒 | Atualiza a subtarefa |
| DELETE | `/subtarefas/{id}` 🔒 | Apaga a subtarefa |

O `status` aceita `pendente` ou `concluida`. Erros de validação voltam com código **422** e mensagens em português; tarefas de outra pessoa respondem **404**.

## O que foi corrigido em relação às versões antigas

| Problema antigo | Agora |
| --- | --- |
| Abrir uma tarefa sempre respondia "Tarefa não encontrada" (classe `SubTarefa` com nome errado) | Mostra a tarefa com as subtarefas |
| O status não podia ser alterado (faltava no `$fillable`) | Status editável em tarefas e subtarefas |
| Apagar uma tarefa com subtarefas falhava | Subtarefas são apagadas junto (`cascadeOnDelete`) |
| Subtarefas criadas sem validação (erro 500) | Validação com mensagens em português |
| Uma consulta ao banco por tarefa na listagem | Tudo em uma consulta com `with('subtarefas')` |
| Erros respondiam com código 200 | 404, 401 e 422 corretos |
| Login e cadastro do Vue eram só visuais | Contas de verdade com Laravel Sanctum |
| Qualquer pessoa via e editava todas as tarefas | Cada usuário só acessa as próprias |
| Front-end listava errado e não compilava (Bootstrap fora do `package.json`) | Vue 3 + Vite, sem dependências extras |
| Lumen (não é mais recomendado para projetos novos) | Laravel 12 |

## Segurança

- Senhas guardadas com hash (bcrypt) pelo Laravel
- Login e cadastro limitados a 10 tentativas por minuto
- Mesma mensagem para e-mail ou senha errados, para não revelar quais e-mails têm conta
- Toda consulta é feita pelo usuário logado, e tarefas de outra pessoa respondem 404
- O front-end mostra tudo como texto (o Vue escapa o HTML), então não há XSS
- O token fica no `localStorage` do navegador. É simples e funciona bem para estudo; num sistema de produção, o mais seguro é usar o modo SPA do Sanctum, com cookies

## Banco de dados

```
users       (id, name, email, password, ...)
tarefas     (id, user_id → users, titulo, descricao, data_vencimento, status, timestamps)
subtarefas  (id, tarefa_id → tarefas, titulo, status, timestamps)
```

Para usar **MySQL** no lugar do SQLite, mude `DB_CONNECTION` e os dados de conexão em `api/.env` e rode `php artisan migrate`.
