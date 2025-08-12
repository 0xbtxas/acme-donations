import axios from "axios";
window.axios = axios;

window.axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
window.axios.defaults.baseURL = "/";

// Note: Authentication headers are now managed by the Pinia auth store
// This file only sets up basic axios configuration
