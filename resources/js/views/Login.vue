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
        <div class="mt-4 text-center">
            <RouterLink
                to="/register"
                class="text-blue-600 hover:text-blue-800"
            >
                Don't have an account? Register here
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref } from "vue";
import { useRouter, useRoute, RouterLink } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const toast = useToast();
const email = ref("admin@example.com");
const password = ref("password");
const loading = ref(false);

const submit = async () => {
    loading.value = true;
    try {
        // Validation
        if (!email.value || !password.value) {
            toast.add({
                title: "Validation Error",
                description: "Please fill in all required fields.",
                color: "red",
            });
            return;
        }

        const { data } = await axios.post("/api/auth/login", {
            email: email.value,
            password: password.value,
        });

        // Set authentication state using Pinia store
        authStore.setAuth({
            token: data.token,
            user: data.user,
            tenant: data.user.tenant?.subdomain || data.user.tenant_id || null,
        });

        toast.add({
            title: "Success",
            description: "Login successful!",
            color: "green",
        });

        const redirect =
            typeof route.query.redirect === "string"
                ? route.query.redirect
                : "/";
        router.replace(redirect);
    } catch (error) {
        const message =
            error.response?.data?.message ||
            "Login failed. Please check your credentials.";
        toast.add({
            title: "Error",
            description: message,
            color: "red",
        });
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.register-link {
    display: block;
    margin-top: 1rem;
}
</style>
