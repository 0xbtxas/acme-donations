<template>
    <div class="max-w-sm mx-auto p-6">
        <h2 class="text-xl font-semibold mb-4">Login</h2>
        <form @submit.prevent="submit">
            <UInput
                v-model="email"
                class="mb-3"
                placeholder="Email"
                type="email"
            />
            <UInput
                v-model="password"
                class="mb-4"
                placeholder="Password"
                type="password"
            />
            <UButton type="submit" :loading="loading">Login</UButton>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();
const email = ref("admin@example.com");
const password = ref("password");
const loading = ref(false);

const submit = async () => {
    loading.value = true;
    try {
        const { data } = await axios.post("/api/auth/login", {
            email: email.value,
            password: password.value,
        });
        localStorage.setItem("token", data.token);
        axios.defaults.headers.common["Authorization"] = `Bearer ${data.token}`;
        const redirect =
            typeof route.query.redirect === "string"
                ? route.query.redirect
                : "/";
        router.replace(redirect);
    } finally {
        loading.value = false;
    }
};
</script>
