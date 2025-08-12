import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "axios";

export const useAuthStore = defineStore("auth", () => {
    // State
    const token = ref(localStorage.getItem("token") || null);
    const user = ref(JSON.parse(localStorage.getItem("user") || "null"));
    const tenant = ref(localStorage.getItem("tenant") || null);
    const isAuthenticated = ref(!!token.value);

    // Getters
    const getToken = computed(() => token.value);
    const getUser = computed(() => user.value);
    const getTenant = computed(() => tenant.value);
    const getIsAuthenticated = computed(() => isAuthenticated.value);

    // Actions
    const setAuth = (authData) => {
        if (!authData.token || !authData.user) {
            console.error("Invalid auth data provided to setAuth");
            return;
        }

        token.value = authData.token;
        user.value = authData.user;
        tenant.value = authData.tenant || null;
        isAuthenticated.value = true;

        // Store in localStorage
        localStorage.setItem("token", authData.token);
        localStorage.setItem("user", JSON.stringify(authData.user));
        if (authData.tenant) {
            localStorage.setItem("tenant", authData.tenant);
        }

        // Set axios default headers
        axios.defaults.headers.common[
            "Authorization"
        ] = `Bearer ${authData.token}`;
        if (authData.tenant) {
            axios.defaults.headers.common["X-Tenant"] = authData.tenant;
        }
    };

    const clearAuth = () => {
        token.value = null;
        user.value = null;
        tenant.value = null;
        isAuthenticated.value = false;

        // Clear localStorage
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        localStorage.removeItem("tenant");

        // Clear axios default headers
        delete axios.defaults.headers.common["Authorization"];
        delete axios.defaults.headers.common["X-Tenant"];
    };

    const updateUser = (userData) => {
        user.value = userData;
        localStorage.setItem("user", JSON.stringify(userData));
    };

    const updateTenant = (tenantData) => {
        tenant.value = tenantData;
        localStorage.setItem("tenant", tenantData);
    };

    // Initialize axios headers if they exist
    if (token.value) {
        axios.defaults.headers.common[
            "Authorization"
        ] = `Bearer ${token.value}`;
    }
    if (tenant.value) {
        axios.defaults.headers.common["X-Tenant"] = tenant.value;
    }

    return {
        // State
        token,
        user,
        tenant,
        isAuthenticated,

        // Getters
        getToken,
        getUser,
        getTenant,
        getIsAuthenticated,

        // Actions
        setAuth,
        clearAuth,
        updateUser,
        updateTenant,
    };
});
