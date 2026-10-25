<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { api, salvarSessao } from "../api.js";

const router = useRouter();
const form = reactive({ name: "", email: "", password: "", password_confirmation: "" });
const erros = ref({});
const enviando = ref(false);

async function cadastrar() {
    enviando.value = true;
    erros.value = {};
    try {
        salvarSessao(await api("/cadastrar", { metodo: "POST", corpo: form }));
        router.push({ name: "tarefas" });
    } catch (erro) {
        erros.value = Object.keys(erro.erros).length ? erro.erros : { geral: [erro.message] };
    } finally {
        enviando.value = false;
    }
}
</script>

<template>
    <main class="pagina-conta">
        <section class="cartao-conta">
            <p class="marca">✓ Tarefas</p>
            <h1>Criar conta</h1>
            <p class="suave">Cada conta tem a sua própria lista de tarefas.</p>

            <p v-if="erros.geral" class="alerta" role="alert">{{ erros.geral[0] }}</p>

            <form class="formulario" novalidate @submit.prevent="cadastrar">
                <label class="campo" :class="{ invalido: erros.name }">
                    <span>Nome</span>
                    <input v-model.trim="form.name" type="text" autocomplete="name" maxlength="80" required />
                    <small v-if="erros.name" class="erro">{{ erros.name[0] }}</small>
                </label>
                <label class="campo" :class="{ invalido: erros.email }">
                    <span>E-mail</span>
                    <input v-model.trim="form.email" type="email" autocomplete="email" required />
                    <small v-if="erros.email" class="erro">{{ erros.email[0] }}</small>
                </label>
                <label class="campo" :class="{ invalido: erros.password }">
                    <span>Senha</span>
                    <input v-model="form.password" type="password" autocomplete="new-password" minlength="8" required />
                    <small v-if="erros.password" class="erro">{{ erros.password[0] }}</small>
                </label>
                <label class="campo">
                    <span>Confirme a senha</span>
                    <input v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
                </label>
                <button class="botao" :disabled="enviando">{{ enviando ? "Criando..." : "Criar conta" }}</button>
            </form>

            <p class="troca">Já tem conta? <RouterLink to="/entrar">Entrar</RouterLink></p>
        </section>
    </main>
</template>
