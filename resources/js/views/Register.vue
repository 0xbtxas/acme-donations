<template>
    <div class="max-w-md mx-auto p-6 space-y-4">
        <h2 class="text-xl font-semibold">Register</h2>
        <form @submit.prevent="submit" class="space-y-3">
            <UInput v-model="name" placeholder="Name" />
            <UInput v-model="email" type="email" placeholder="Email" />
            <UInput v-model="password" type="password" placeholder="Password" />
            <div class="border rounded p-3 space-y-2">
                <div class="font-medium">Tenant</div>
                <URadioGroup v-model="mode" :items="modeOptions" />
                <div v-if="mode === 'join'" class="space-y-2">
                    <USelect
                        v-model="tenantId"
                        :options="tenantOptions"
                        placeholder="Select tenant"
                    />
                </div>
                <div v-else class="grid grid-cols-2 gap-2">
                    <UInput v-model="tenantName" placeholder="Tenant name" />
                    <UInput v-model="tenantSub" placeholder="Subdomain" />
                </div>
            </div>
            <UButton type="submit" :loading="loading">Create account</UButton>
        </form>
    </div>
</template>

<script setup>
import axios from "axios";
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";

const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();
const name = ref("");
const email = ref("");
const password = ref("");
const mode = ref("join");
const modeOptions = [
    { label: "Join existing", value: "join" },
    { label: "Create new", value: "create" },
];
const tenantOptions = ref([]);
const tenantId = ref(null);
const tenantName = ref("");
const tenantSub = ref("");
const loading = ref(false);

const loadTenants = async () => {
    try {
        const { data } = await axios.get("/api/tenants");
        tenantOptions.value = data.data.map((t) => ({
            label: `${t.name} (${t.subdomain})`,
            value: t.id,
        }));
    } catch (error) {
        toast.add({
            title: "Error",
            description: "Failed to load tenants. Please try again.",
            color: "red",
        });
    }
};

const submit = async () => {
    loading.value = true;
    try {
        // Validation
        if (!name.value || !email.value || !password.value) {
            toast.add({
                title: "Validation Error",
                description: "Please fill in all required fields.",
                color: "red",
            });
            return;
        }

        if (mode.value === "join" && !tenantId.value) {
            toast.add({
                title: "Validation Error",
                description: "Please select a tenant to join.",
                color: "red",
            });
            return;
        }

        if (
            mode.value === "create" &&
            (!tenantName.value || !tenantSub.value)
        ) {
            toast.add({
                title: "Validation Error",
                description: "Please provide both tenant name and subdomain.",
                color: "red",
            });
            return;
        }

        const payload = {
            name: name.value,
            email: email.value,
            password: password.value,
        };
        if (mode.value === "join") {
            payload.tenant_id = tenantId.value;
        } else {
            payload.tenant_name = tenantName.value;
            payload.tenant_subdomain = tenantSub.value;
        }

        const { data } = await axios.post("/api/auth/register", payload);

        // Set authentication state using Pinia store
        // The backend now returns the user with tenant information loaded
        authStore.setAuth({
            token: data.token,
            user: data.user,
            tenant: data.user.tenant,
        });

        toast.add({
            title: "Success",
            description: "Account created successfully!",
            color: "green",
        });

        router.replace({ name: "home" });
    } catch (error) {
        const message =
            error.response?.data?.message ||
            "Registration failed. Please try again.";
        toast.add({
            title: "Error",
            description: message,
            color: "red",
        });
    } finally {
        loading.value = false;
    }
};

onMounted(loadTenants);
</script>
