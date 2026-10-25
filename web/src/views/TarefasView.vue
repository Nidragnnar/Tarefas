<script setup>
import { computed, onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import { api, salvarSessao, sessao } from "../api.js";
import TarefaForm from "../components/TarefaForm.vue";
import TarefaItem from "../components/TarefaItem.vue";

const router = useRouter();
const tarefas = ref([]);
const carregando = ref(true);
const filtro = ref("todas");
const aviso = ref("");

const formAberto = ref(false);
const editando = ref(null);
const errosForm = ref({});
const salvando = ref(false);

const FILTROS = [
    { valor: "todas", nome: "Todas" },
    { valor: "pendente", nome: "Pendentes" },
    { valor: "concluida", nome: "Concluídas" },
];

const contagem = computed(() => ({
    todas: tarefas.value.length,
    pendente: tarefas.value.filter((t) => t.status === "pendente").length,
    concluida: tarefas.value.filter((t) => t.status === "concluida").length,
}));
const visiveis = computed(() =>
    filtro.value === "todas" ? tarefas.value : tarefas.value.filter((t) => t.status === filtro.value)
);

let temporizador;
function mostrarAviso(texto) {
    aviso.value = texto;
    clearTimeout(temporizador);
    temporizador = setTimeout(() => (aviso.value = ""), 3000);
}

async function carregar() {
    try {
        tarefas.value = await api("/tarefas");
    } catch (erro) {
        mostrarAviso(erro.message);
    } finally {
        carregando.value = false;
    }
}

function abrirForm(tarefa = null) {
    editando.value = tarefa;
    errosForm.value = {};
    formAberto.value = true;
}

async function salvar(dados) {
    salvando.value = true;
    errosForm.value = {};
    try {
        if (editando.value) {
            atualizar(await api(`/tarefas/${editando.value.id}`, { metodo: "PATCH", corpo: dados }));
            mostrarAviso("Tarefa atualizada.");
        } else {
            tarefas.value.unshift(await api("/tarefas", { metodo: "POST", corpo: dados }));
            mostrarAviso("Tarefa adicionada.");
        }
        formAberto.value = false;
    } catch (erro) {
        errosForm.value = erro.erros;
        if (!Object.keys(erro.erros).length) mostrarAviso(erro.message);
    } finally {
        salvando.value = false;
    }
}

function atualizar(tarefa) {
    tarefas.value = tarefas.value.map((t) => (t.id === tarefa.id ? tarefa : t));
}

function remover(id) {
    tarefas.value = tarefas.value.filter((t) => t.id !== id);
    mostrarAviso("Tarefa apagada.");
}

async function sair() {
    try {
        await api("/sair", { metodo: "POST" });
    } finally {
        salvarSessao(null);
        router.push({ name: "entrar" });
    }
}

onMounted(carregar);
</script>

<template>
    <div class="app">
        <header class="topo">
            <p class="marca">✓ Tarefas</p>
            <div class="usuario">
                <span>Olá, {{ sessao.atual?.usuario?.name }}</span>
                <button type="button" class="botao botao-secundario botao-pequeno" @click="sair">Sair</button>
            </div>
        </header>

        <main class="conteudo">
            <div class="cabecalho-lista">
                <div>
                    <h1>Minhas tarefas</h1>
                    <p class="suave">{{ contagem.pendente }} pendente(s) · {{ contagem.concluida }} concluída(s)</p>
                </div>
                <button v-if="!formAberto" type="button" class="botao" @click="abrirForm()">+ Nova tarefa</button>
            </div>

            <section v-if="formAberto" class="cartao">
                <h2>{{ editando ? "Editar tarefa" : "Nova tarefa" }}</h2>
                <TarefaForm
                    :key="editando?.id ?? 'nova'"
                    :tarefa="editando"
                    :erros="errosForm"
                    :enviando="salvando"
                    @salvar="salvar"
                    @cancelar="formAberto = false"
                />
            </section>

            <nav class="filtros" aria-label="Filtrar tarefas">
                <button
                    v-for="f in FILTROS"
                    :key="f.valor"
                    type="button"
                    :class="{ ativo: filtro === f.valor }"
                    :aria-pressed="filtro === f.valor"
                    @click="filtro = f.valor"
                >
                    {{ f.nome }} <span>{{ contagem[f.valor] }}</span>
                </button>
            </nav>

            <p v-if="carregando" class="vazio">Carregando...</p>
            <p v-else-if="!visiveis.length" class="vazio">
                {{ tarefas.length ? "Nenhuma tarefa neste filtro." : "Nenhuma tarefa ainda. Que tal criar a primeira?" }}
            </p>
            <ul v-else class="lista">
                <TarefaItem
                    v-for="tarefa in visiveis"
                    :key="tarefa.id"
                    :tarefa="tarefa"
                    @atualizar="atualizar"
                    @remover="remover"
                    @editar="abrirForm"
                    @erro="mostrarAviso"
                />
            </ul>
        </main>

        <p class="toast" :class="{ visivel: aviso }" role="status">{{ aviso }}</p>
    </div>
</template>
