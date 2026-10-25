import { createRouter, createWebHistory } from "vue-router";
import { sessao } from "./api.js";
import TarefasView from "./views/TarefasView.vue";

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: "/", name: "tarefas", component: TarefasView, meta: { exigeLogin: true } },
        { path: "/entrar", name: "entrar", component: () => import("./views/EntrarView.vue") },
        { path: "/cadastrar", name: "cadastrar", component: () => import("./views/CadastroView.vue") },
        { path: "/:qualquer(.*)", redirect: "/" },
    ],
});

// Sem login só dá para ver as telas de entrar e cadastrar; logado, elas levam às tarefas
router.beforeEach((destino) => {
    const logado = Boolean(sessao.atual?.token);
    if (destino.meta.exigeLogin && !logado) return { name: "entrar" };
    if (!destino.meta.exigeLogin && logado) return { name: "tarefas" };
});

export default router;
