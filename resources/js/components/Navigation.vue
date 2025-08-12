<template>
    <nav
        v-if="authStore.getIsAuthenticated"
        class="bg-white shadow-sm border-b"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <RouterLink
                        to="/"
                        class="text-xl font-semibold text-gray-900"
                    >
                        ACME Donations
                    </RouterLink>
                    <div class="ml-10 flex items-baseline space-x-4">
                        <RouterLink
                            to="/campaigns"
                            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            Campaigns
                        </RouterLink>
                        <RouterLink
                            to="/payment-methods"
                            class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium"
                        >
                            Payment Methods
                        </RouterLink>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        <span class="font-medium">{{
                            authStore.getTenantDisplayName
                        }}</span>
                    </div>
                    <div
                        v-if="authStore.getUser?.name"
                        class="text-sm text-gray-500"
                    >
                        <span class="font-medium">{{
                            authStore.getUser.name
                        }}</span>
                    </div>
                    <div class="border-l border-gray-300 h-6"></div>
                    <UButton
                        variant="soft"
                        color="red"
                        @click="logout"
                        :loading="loggingOut"
                        size="sm"
                    >
                        Logout
                    </UButton>
                </div>
            </div>
        </div>
    </nav>
</template>

<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "../stores/auth";
import axios from "axios";

const router = useRouter();
const authStore = useAuthStore();
const loggingOut = ref(false);
const toast = useToast();

const logout = async () => {
    loggingOut.value = true;
    try {
        console.log("Starting logout process...");

        // Call backend logout to invalidate token
        await axios.post("/api/auth/logout");

        console.log("LocalStorage cleared");

        // Clear auth state using Pinia store
        authStore.clearAuth();

        console.log("Auth state cleared");

        // Show success message
        toast.add({
            title: "Success",
            description: "Logged out successfully",
            color: "green",
        });

        // Redirect to login
        router.replace("/login");
    } catch (error) {
        console.error("Logout error:", error);
        // Even if API call fails, clear auth state and redirect
        authStore.clearAuth();

        toast.add({
            title: "Warning",
            description: "Logged out (some cleanup may have failed)",
            color: "yellow",
        });

        router.replace("/login");
    } finally {
        loggingOut.value = false;
    }
};
</script>
