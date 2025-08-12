import { createRouter, createWebHistory } from "vue-router";

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
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to) => {
    const token = localStorage.getItem("token");
    if (to.meta?.requiresAuth && !token) {
        return { name: "login", query: { redirect: to.fullPath } };
    }
    if (to.meta?.guestOnly && token) {
        return { name: "home" };
    }
});

export default router;
