<script setup>
import { reactive } from "vue";

// Formulário usado para criar e para editar uma tarefa
const props = defineProps({
    tarefa: { type: Object, default: null },
    erros: { type: Object, default: () => ({}) },
    enviando: { type: Boolean, default: false },
});
const emit = defineEmits(["salvar", "cancelar"]);

const form = reactive({
    titulo: props.tarefa?.titulo ?? "",
    descricao: props.tarefa?.descricao ?? "",
    data_vencimento: props.tarefa?.data_vencimento ?? "",
});

function salvar() {
    emit("salvar", {
        titulo: form.titulo.trim(),
        descricao: form.descricao.trim() || null,
        data_vencimento: form.data_vencimento || null,
    });
}
</script>

<template>
    <form class="formulario form-tarefa" novalidate @submit.prevent="salvar">
        <label class="campo" :class="{ invalido: erros.titulo }">
            <span>Título</span>
            <input v-model="form.titulo" type="text" maxlength="150" placeholder="O que precisa ser feito?" required />
            <small v-if="erros.titulo" class="erro">{{ erros.titulo[0] }}</small>
        </label>

        <label class="campo" :class="{ invalido: erros.descricao }">
            <span>Descrição <em>(opcional)</em></span>
            <textarea v-model="form.descricao" rows="3" maxlength="5000"></textarea>
            <small v-if="erros.descricao" class="erro">{{ erros.descricao[0] }}</small>
        </label>

        <div class="linha-form">
            <label class="campo" :class="{ invalido: erros.data_vencimento }">
                <span>Vencimento <em>(opcional)</em></span>
                <input v-model="form.data_vencimento" type="date" />
                <small v-if="erros.data_vencimento" class="erro">{{ erros.data_vencimento[0] }}</small>
            </label>

            <div class="botoes">
                <button type="button" class="botao botao-secundario" @click="emit('cancelar')">Cancelar</button>
                <button class="botao" :disabled="enviando">
                    {{ enviando ? "Salvando..." : tarefa ? "Salvar" : "Adicionar" }}
                </button>
            </div>
        </div>
    </form>
</template>
