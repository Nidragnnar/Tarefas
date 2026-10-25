<script setup>
import { computed, ref } from "vue";
import { api } from "../api.js";

const props = defineProps({ tarefa: { type: Object, required: true } });
const emit = defineEmits(["atualizar", "remover", "editar", "erro"]);

const aberta = ref(false);
const novaSub = ref("");
const ocupado = ref(false);

const concluida = computed(() => props.tarefa.status === "concluida");
const subs = computed(() => props.tarefa.subtarefas || []);
const subsFeitas = computed(() => subs.value.filter((s) => s.status === "concluida").length);

// "2026-11-01" → "01/11/2026", sem converter fuso (a data não tem hora)
const dataBr = computed(() => props.tarefa.data_vencimento?.split("-").reverse().join("/"));
const vencida = computed(() => {
    if (!props.tarefa.data_vencimento || concluida.value) return false;
    const hoje = new Date();
    const hojeIso = `${hoje.getFullYear()}-${String(hoje.getMonth() + 1).padStart(2, "0")}-${String(hoje.getDate()).padStart(2, "0")}`;
    return props.tarefa.data_vencimento < hojeIso;
});

async function acao(funcao) {
    ocupado.value = true;
    try {
        await funcao();
    } catch (erro) {
        emit("erro", erro.erros?.titulo?.[0] || erro.message);
    } finally {
        ocupado.value = false;
    }
}

const alternarStatus = () =>
    acao(async () => {
        const status = concluida.value ? "pendente" : "concluida";
        emit("atualizar", await api(`/tarefas/${props.tarefa.id}`, { metodo: "PATCH", corpo: { status } }));
    });

const apagar = () => {
    if (!confirm(`Apagar "${props.tarefa.titulo}"${subs.value.length ? " e as subtarefas" : ""}?`)) return;
    acao(async () => {
        await api(`/tarefas/${props.tarefa.id}`, { metodo: "DELETE" });
        emit("remover", props.tarefa.id);
    });
};

const comSubs = (lista) => emit("atualizar", { ...props.tarefa, subtarefas: lista });

const adicionarSub = () =>
    acao(async () => {
        const sub = await api(`/tarefas/${props.tarefa.id}/subtarefas`, { metodo: "POST", corpo: { titulo: novaSub.value.trim() } });
        comSubs([...subs.value, sub]);
        novaSub.value = "";
    });

const alternarSub = (sub) =>
    acao(async () => {
        const status = sub.status === "concluida" ? "pendente" : "concluida";
        const atualizada = await api(`/subtarefas/${sub.id}`, { metodo: "PATCH", corpo: { status } });
        comSubs(subs.value.map((s) => (s.id === sub.id ? atualizada : s)));
    });

const apagarSub = (sub) =>
    acao(async () => {
        await api(`/subtarefas/${sub.id}`, { metodo: "DELETE" });
        comSubs(subs.value.filter((s) => s.id !== sub.id));
    });
</script>

<template>
    <li class="tarefa" :class="{ concluida, ocupado }">
        <div class="tarefa-linha">
            <input
                type="checkbox"
                class="check"
                :checked="concluida"
                :aria-label="concluida ? 'Marcar como pendente' : 'Marcar como concluída'"
                @change="alternarStatus"
            />

            <div class="tarefa-corpo">
                <p class="tarefa-titulo">{{ tarefa.titulo }}</p>
                <p v-if="tarefa.descricao" class="tarefa-desc">{{ tarefa.descricao }}</p>
                <div class="etiquetas">
                    <span v-if="dataBr" class="etiqueta" :class="{ vencida }">
                        📅 {{ vencida ? "Venceu em" : "Até" }} {{ dataBr }}
                    </span>
                    <button type="button" class="etiqueta etiqueta-botao" :aria-expanded="aberta" @click="aberta = !aberta">
                        ☑ {{ subs.length ? `${subsFeitas}/${subs.length} subtarefas` : "Subtarefas" }}
                        <span aria-hidden="true">{{ aberta ? "▴" : "▾" }}</span>
                    </button>
                </div>
            </div>

            <div class="tarefa-acoes">
                <button type="button" class="icone" title="Editar" aria-label="Editar tarefa" @click="emit('editar', tarefa)">✎</button>
                <button type="button" class="icone perigo" title="Apagar" aria-label="Apagar tarefa" @click="apagar">🗑</button>
            </div>
        </div>

        <div v-if="subs.length" class="progresso" aria-hidden="true">
            <span :style="{ width: `${(subsFeitas / subs.length) * 100}%` }"></span>
        </div>

        <div v-if="aberta" class="subtarefas">
            <ul>
                <li v-for="sub in subs" :key="sub.id" :class="{ concluida: sub.status === 'concluida' }">
                    <label>
                        <input type="checkbox" :checked="sub.status === 'concluida'" @change="alternarSub(sub)" />
                        <span>{{ sub.titulo }}</span>
                    </label>
                    <button type="button" class="icone perigo pequeno" :aria-label="`Apagar ${sub.titulo}`" @click="apagarSub(sub)">✕</button>
                </li>
            </ul>
            <form class="nova-sub" @submit.prevent="adicionarSub">
                <input v-model="novaSub" type="text" maxlength="150" placeholder="Nova subtarefa" aria-label="Nova subtarefa" />
                <button class="botao botao-pequeno" :disabled="!novaSub.trim()">Adicionar</button>
            </form>
        </div>
    </li>
</template>
