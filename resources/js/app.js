import { createApp } from 'vue';
import App from './components/App.vue';
import router from './router';
import './bootstrap.js'

createApp(App).use(router).mount('#app');
axios.get('/sanctum/csrf-cookie').then(() => {
    createApp(App).use(router).mount('#app');
});
