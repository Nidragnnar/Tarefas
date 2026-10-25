// Comunicação com a API em Laravel e estado do login
import { reactive } from "vue";

const URL_API = import.meta.env.VITE_API_URL || "http://localhost:8000/api";
const CHAVE = "tarefas.sessao";

function lerSessao() {
    try {
        return JSON.parse(localStorage.getItem(CHAVE)) || null;
    } catch {
        return null;
    }
}

// Sessão compartilhada por todas as telas: { usuario, token } ou null
export const sessao = reactive({ atual: lerSessao() });

export function salvarSessao(dados) {
    sessao.atual = dados;
    try {
        if (dados) localStorage.setItem(CHAVE, JSON.stringify(dados));
        else localStorage.removeItem(CHAVE);
    } catch {
        // Sem armazenamento (ex.: janela anônima bloqueada): a sessão vale só até recarregar
    }
}

// Erro com a mensagem e os erros de validação que a API devolveu
export class ErroApi extends Error {
    constructor(status, corpo) {
        super(corpo?.message || "Não foi possível falar com o servidor.");
        this.status = status;
        this.erros = corpo?.errors || {};
    }
}

export async function api(caminho, { metodo = "GET", corpo } = {}) {
    const cabecalhos = { Accept: "application/json" };
    if (corpo !== undefined) cabecalhos["Content-Type"] = "application/json";
    if (sessao.atual?.token) cabecalhos.Authorization = `Bearer ${sessao.atual.token}`;

    let resposta;
    try {
        resposta = await fetch(URL_API + caminho, {
            method: metodo,
            headers: cabecalhos,
            body: corpo !== undefined ? JSON.stringify(corpo) : undefined,
        });
    } catch {
        throw new ErroApi(0, { message: "Servidor fora do ar. A API está rodando?" });
    }

    const dados = resposta.status === 204 ? null : await resposta.json().catch(() => null);

    // Token vencido ou inválido: volta para o login
    if (resposta.status === 401 && sessao.atual) {
        salvarSessao(null);
        window.location.assign("/entrar");
    }
    if (!resposta.ok) throw new ErroApi(resposta.status, dados);
    return dados;
}
