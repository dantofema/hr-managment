import { createApp } from "vue";
import App from "./App.vue";

console.log("Vue.js main.js is executing...");
console.log("Looking for #app element:", document.querySelector("#app"));

const app = createApp(App);
console.log("Vue app created:", app);

app.mount("#app");
console.log("Vue app mounted to #app");
