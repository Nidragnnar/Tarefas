<script setup>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { api, salvarSessao } from "../api.js";

const router = useRouter();
const form = reactive({ email: "", password: "" });
const erros = ref({});
const enviando = ref(false);

async function entrar() {
    enviando.value = true;
    erros.value = {};
    try {
        salvarSessao(await api("/entrar", { metodo: "POST", corpo: form }));
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
            <h1>Entrar</h1>
            <p class="suave">Acesse sua conta para ver suas tarefas.</p>

            <p v-if="erros.geral || erros.email" class="alerta" role="alert">
                {{ (erros.geral || erros.email)[0] }}
            </p>

            <form class="formulario" novalidate @submit.prevent="entrar">
                <label class="campo">
                    <span>E-mail</span>
                    <input v-model.trim="form.email" type="email" autocomplete="email" required />
                </label>
                <label class="campo">
                    <span>Senha</span>
                    <input v-model="form.password" type="password" autocomplete="current-password" required />
                </label>
                <button class="botao" :disabled="enviando">{{ enviando ? "Entrando..." : "Entrar" }}</button>
            </form>

            <p class="troca">Não tem conta? <RouterLink to="/cadastrar">Criar conta</RouterLink></p>
        </section>
    </main>
</template>
