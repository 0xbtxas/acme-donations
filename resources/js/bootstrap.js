import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.baseURL = "/";
// Basic tenant header for multi-tenancy (defaults to 'acme' for local dev)
window.axios.defaults.headers.common["X-Tenant"] =
    localStorage.getItem("tenant") || "acme";
const token = localStorage.getItem("token");
if (token) {
    window.axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
}
