import { defineStore } from "pinia";
import { ref, computed } from "vue";
import axios from "axios";

export const useAuthStore = defineStore("auth", () => {
    // State
    const token = ref(localStorage.getItem("token") || null);
    const user = ref(JSON.parse(localStorage.getItem("user") || "null"));
    const tenant = ref(JSON.parse(localStorage.getItem("tenant") || "null"));
    const isAuthenticated = ref(!!token.value);

    // Getters
    const getToken = computed(() => token.value);
    const getUser = computed(() => user.value);
    const getTenant = computed(() => tenant.value);
    const getIsAuthenticated = computed(() => isAuthenticated.value);

    const getTenantDisplayName = computed(() => {
        if (!tenant.value) return "Unknown";

        if (typeof tenant.value === "string") {
            return tenant.value;
        }

        if (typeof tenant.value === "object") {
            return tenant.value.name || tenant.value.subdomain || "Unknown";
        }

        return "Unknown";
    });

    const setAuth = (authData) => {
        if (!authData.token || !authData.user) {
            console.error("Invalid auth data provided to setAuth");
            return;
        }

        console.log("Setting auth with data:", authData);

        token.value = authData.token;
        user.value = authData.user;
        tenant.value = authData.tenant || null;
        isAuthenticated.value = true;

        localStorage.setItem("token", authData.token);
        localStorage.setItem("user", JSON.stringify(authData.user));
        if (authData.tenant) {
            localStorage.setItem("tenant", JSON.stringify(authData.tenant));
        }

        axios.defaults.headers.common[
            "Authorization"
        ] = `Bearer ${authData.token}`;
        if (authData.tenant?.subdomain) {
            axios.defaults.headers.common["X-Tenant"] =
                authData.tenant.subdomain;
        }
    };

    const clearAuth = () => {
        token.value = null;
        user.value = null;
        tenant.value = null;
        isAuthenticated.value = false;

        localStorage.removeItem("token");
        localStorage.removeItem("user");
        localStorage.removeItem("tenant");

        delete axios.defaults.headers.common["Authorization"];
        delete axios.defaults.headers.common["X-Tenant"];
    };

    const updateUser = (userData) => {
        user.value = userData;
        localStorage.setItem("user", JSON.stringify(userData));
    };

    const updateTenant = (tenantData) => {
        tenant.value = tenantData;
        localStorage.setItem("tenant", JSON.stringify(tenantData));
    };

    if (token.value) {
        axios.defaults.headers.common[
            "Authorization"
        ] = `Bearer ${token.value}`;
    }
    if (tenant.value?.subdomain) {
        axios.defaults.headers.common["X-Tenant"] = tenant.value.subdomain;
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
        getTenantDisplayName,

        // Actions
        setAuth,
        clearAuth,
        updateUser,
        updateTenant,
    };
});
