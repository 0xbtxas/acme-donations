import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../stores/auth";

export const routes = [
    {
        path: "/",
        name: "home",
        meta: { requiresAuth: true },
        component: () => import("../views/Home.vue"),
    },
    {
        path: "/payment-methods",
        name: "payment.methods",
        meta: { requiresAuth: true },
        component: () => import("../views/PaymentMethods.vue"),
    },
    {
        path: "/campaigns",
        name: "campaigns",
        meta: { requiresAuth: true },
        component: () => import("../views/Campaigns.vue"),
    },
    {
        path: "/campaigns/new",
        name: "campaigns.new",
        meta: { requiresAuth: true },
        component: () => import("../views/CampaignNew.vue"),
    },
    {
        path: "/campaigns/:id",
        name: "campaigns.show",
        meta: { requiresAuth: true },
        component: () => import("../views/CampaignShow.vue"),
        props: true,
    },
    {
        path: "/campaigns/:id/edit",
        name: "campaigns.edit",
        meta: { requiresAuth: true },
        component: () => import("../views/CampaignEdit.vue"),
        props: true,
    },
    {
        path: "/login",
        name: "login",
        meta: { guestOnly: true },
        component: () => import("../views/Login.vue"),
    },
    {
        path: "/register",
        name: "register",
        meta: { guestOnly: true },
        component: () => import("../views/Register.vue"),
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const authStore = useAuthStore();
    const isAuthenticated = authStore.getIsAuthenticated;

    if (to.meta?.requiresAuth && !isAuthenticated) {
        return { name: "login", query: { redirect: to.fullPath } };
    }
    if (to.meta?.guestOnly && isAuthenticated) {
        return { name: "home" };
    }
});

export default router;
