import { createRouter, createWebHistory } from 'vue-router';
import Home from '../components/Home.vue';
import Profile from '../components/Profile.vue';

const routes = [
    { path: '/', component: Home },
    { path: '/profile', component: Profile },
];

export default createRouter({
    history: createWebHistory(),
    routes,
});
