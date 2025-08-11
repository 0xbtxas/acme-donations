import "./bootstrap";

import { createApp } from "vue";
// import { createHead } from "@vueuse/head";
import ui from "@nuxt/ui/vue-plugin";
import App from "./App.vue";
import router from "./routes";

const app = createApp(App);
app.use(router);
// app.use(createHead());
app.use(ui);
app.mount("#app");
