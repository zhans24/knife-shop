<template>
    <div class="min-h-screen transition-colors duration-300" :class="isDarkMode ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900'">
        <!-- Navbar -->
        <nav class="p-4 flex justify-between items-center fixed w-full top-0 z-10 transition-colors duration-300" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-gray-800 text-white'">
            <div class="text-2xl font-bold"><a href="/">CS Knife Store</a></div>
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <input v-model="searchQuery" type="text" placeholder="Поиск ножей..." class="p-2 pl-10 rounded-lg border-2 border-blue-500 bg-gray-700 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 w-64" />
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button @click="toggleTheme" class="cursor-pointer p-2 rounded-full hover:bg-gray-600 transition-opacity duration-200" :class="isDarkMode ? 'bg-gray-700' : 'bg-gray-600'">
                    <svg v-if="isDarkMode" class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg v-else class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <div class="relative">
                    <button class="cursor-pointer bg-blue-600 p-2 rounded px-4 transition-opacity duration-150 hover:opacity-80" :class="isDarkMode ? 'text-white' : 'text-white'">Корзина ({{ cart.length }})</button>
                    <div v-if="cart.length" class="absolute top-10 right-0 p-4 rounded shadow w-64 transition-colors duration-300" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black border-gray-300'">
                        <div v-for="item in cart" :key="item.id" class="flex justify-between mb-2">
                            {{ item.name }} <span>${{ item.price.toFixed(2) }}</span>
                        </div>
                        <button class="cursor-pointer bg-blue-500 text-white p-2 rounded-md w-full transition-opacity duration-150 hover:bg-blue-600">Оформить</button>
                        <button @click="clearCart" class="cursor-pointer bg-red-600 text-white p-2 rounded-md w-full mt-2 transition-opacity duration-150 hover:bg-red-700">Очистить корзину</button>
                    </div>
                </div>
                <div v-if="authLoading" class="text-gray-300">Загрузка...</div>
                <div v-else-if="isLoggedIn" class="relative">
                    <button @click="toggleUserMenu" class="flex items-center space-x-2 cursor-pointer">
                        <img :src="user.avatar || 'https://via.placeholder.com/32'" class="w-8 h-8 rounded-full" alt="User Avatar" />
                        <span>{{ user.name }}</span>
                    </button>
                    <div v-if="showUserMenu" class="absolute top-10 right-0 bg-gray-800 text-white rounded shadow w-48 p-2 transition-opacity duration-200">
                        <router-link to="/profile" class="block p-2 hover:bg-gray-700 rounded">Профиль</router-link>
                        <button @click="logout" class="block w-full text-left p-2 hover:bg-gray-700 rounded">Выйти</button>
                    </div>
                </div>
                <div v-else class="flex space-x-2">
                    <button @click="showLogin = true" class="cursor-pointer bg-blue-600 p-2 px-4 rounded-lg hover:bg-blue-700 transition-opacity duration-200">Войти</button>
                    <button @click="showRegister = true" class="cursor-pointer bg-blue-600 p-2 px-4 rounded-lg hover:bg-blue-700 transition-opacity duration-200">Регистрация</button>
                </div>
            </div>
        </nav>
        <div class="pt-24">
            <div v-if="error" class="text-red-600 text-center p-4">{{ error }}</div>
            <router-view
                :knives="knives"
                :filters="filters"
                :pagination="pagination"
                :is-dark-mode="isDarkMode"
                @update:filters="filters = $event"
                @add-to-cart="addToCart"
                @change-page="fetchKnives"
            ></router-view>
        </div>
        <div v-if="showLogin" class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center">
            <div class="p-6 rounded-lg shadow max-w-md w-full transition-colors duration-200" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black'">
                <h2 class="text-xl font-bold mb-4">Вход</h2>
                <input v-model="loginForm.email" type="email" placeholder="Email" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="loginForm.password" type="password" placeholder="Пароль" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <div class="flex space-x-2">
                    <button @click="login" class="cursor-pointer bg-blue-600 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Войти</button>
                    <button @click="showLogin = false" class="cursor-pointer bg-gray-500 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Отмена</button>
                </div>
            </div>
        </div>
        <div v-if="showRegister" class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center">
            <div class="p-6 rounded-lg shadow max-w-md w-full transition-colors duration-200" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black'">
                <h2 class="text-xl font-bold mb-4">Регистрация</h2>
                <input v-model="registerForm.name" type="text" placeholder="Имя" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="registerForm.email" type="email" placeholder="Email" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="registerForm.password" type="password" placeholder="Пароль" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="registerForm.password_confirmation" type="password" placeholder="Подтвердите пароль" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <div class="flex space-x-2">
                    <button @click="register" class="cursor-pointer bg-blue-600 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Зарегистрироваться</button>
                    <button @click="showRegister = false" class="cursor-pointer bg-gray-500 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Отмена</button>
                </div>
            </div>
        </div>
        <div v-if="showAddProduct" class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center">
            <div class="p-6 rounded-lg shadow w-full max-w-2xl transition-colors duration-200" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black'">
                <h2 class="text-xl font-bold mb-4">Добавить нож</h2>
                <input v-model="newKnife.market_hash_name" type="text" placeholder="Название ножа (например, ★ Karambit | Doppler (Factory New))" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <select v-model="newKnife.type" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="Karambit">Karambit</option>
                    <option value="Bayonet">Bayonet</option>
                    <option value="Butterfly">Butterfly</option>
                    <option value="Skeleton Knife">Skeleton Knife</option>
                    <option value="Bowie Knife">Bowie Knife</option>
                    <option value="Falchion Knife">Falchion Knife</option>
                    <option value="Paracord Knife">Paracord Knife</option>
                    <option value="Navaja Knife">Navaja Knife</option>
                    <option value="Huntsman Knife">Huntsman Knife</option>
                    <option value="Gut Knife">Gut Knife</option>
                </select>
                <div class="flex space-x-2">
                    <button @click="addProduct" class="cursor-pointer bg-green-600 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Добавить</button>
                    <button @click="showAddProduct = false" class="cursor-pointer bg-gray-600 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Отмена</button>
                </div>
            </div>
        </div>
        <div v-if="showAlert" class="fixed bottom-4 right-4 bg-red-600 text-white p-4 rounded-lg shadow">
            Зарегистрируйтесь, чтобы добавить товары в корзину!
            <button @click="showAlert = false" class="cursor-pointer ml-2 underline hover:opacity-80 transition-opacity">Закрыть</button>
        </div>
    </div>
</template>
<script>
import axios from 'axios';
import { useRouter } from 'vue-router';

export default {
    setup() {
        const router = useRouter();
        return { router };
    },
    data() {
        return {
            knives: [],
            pagination: {
                current_page: 1,
                last_page: 1,
                total: 0,
            },
            filters: {
                type: '',
                wear_level: '',
                price_min: '',
                price_max: ''
            },
            searchQuery: '',
            cart: [],
            isLoggedIn: false,
            user: null,
            authLoading: true,
            showLogin: false,
            showRegister: false,
            showAddProduct: false,
            showAlert: false,
            showUserMenu: false,
            error: null,
            isDarkMode: false,
            loginForm: { email: '', password: '' },
            registerForm: { name: '', email: '', password: '', password_confirmation: '' },
            newKnife: { market_hash_name: '', type: '' }
        };
    },
    created() {
        this.loadTheme();
        this.checkAuth();
        this.fetchKnives();
    },
    methods: {
        async fetchKnives(page = 1) {
            try {
                this.error = null;
                this.knives = [];
                const params = { ...this.filters, search: this.searchQuery.trim(), page };
                const response = await axios.get('/api/knives', { params });
                this.knives = response.data.data;
                this.pagination = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                };
                if (this.knives.length === 0 && (this.searchQuery || Object.values(this.filters).some(v => v))) {
                    this.error = 'Ножи не найдены.';
                }
            } catch (error) {
                console.error('Ошибка загрузки ножей:', error);
                this.error = 'Не удалось загрузить ножи. Попробуйте позже.';
            }
        },
        async addToCart(knife) {
            if (!this.isLoggedIn) {
                this.showAlert = true;
                return;
            }
            try {
                const response = await axios.post('/api/cart', { knife_id: knife.id }, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.cart = response.data.cart;
            } catch (error) {
                console.error('Ошибка добавления в корзину:', error);
                this.error = 'Не удалось добавить в корзину.';
            }
        },
        async clearCart() {
            try {
                await axios.post('/api/cart/clear', {}, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.cart = [];
            } catch (error) {
                console.error('Ошибка очистки корзины:', error);
                this.error = 'Не удалось очистить корзину.';
            }
        },
        async checkAuth() {
            this.authLoading = true;
            const token = localStorage.getItem('token');
            if (token) {
                try {
                    const response = await axios.get('/api/user', {
                        headers: { Authorization: `Bearer ${token}` },
                    });
                    this.isLoggedIn = true;
                    this.user = response.data;
                    this.fetchCart();
                } catch {
                    localStorage.removeItem('token');
                    this.isLoggedIn = false;
                    this.user = null;
                }
            }
            this.authLoading = false;
        },
        async fetchCart() {
            try {
                const response = await axios.get('/api/cart', {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.cart = response.data;
            } catch (error) {
                console.error('Ошибка загрузки корзины:', error);
                this.error = 'Не удалось загрузить корзину.';
            }
        },
        async login() {
            try {
                const response = await axios.post('/api/login', this.loginForm);
                localStorage.setItem('token', response.data.token);
                this.isLoggedIn = true;
                this.user = response.data.user;
                this.showLogin = false;
                this.fetchCart();
                this.router.push('/');
            } catch (error) {
                console.error('Ошибка входа:', error);
                alert('Ошибка входа: ' + (error.response?.data?.message || 'Неизвестная ошибка'));
            }
        },
        async register() {
            try {
                await axios.post('/api/register', this.registerForm);
                this.showRegister = false;
                this.showLogin = true;
                this.registerForm = { name: '', email: '', password: '', password_confirmation: '' };
            } catch (error) {
                console.error('Ошибка регистрации:', error);
                alert('Ошибка регистрации: ' + (error.response?.data?.message || 'Неизвестная ошибка'));
            }
        },
        async logout() {
            try {
                await axios.post('/api/logout', {}, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                localStorage.removeItem('token');
                this.isLoggedIn = false;
                this.user = null;
                this.cart = [];
                this.showUserMenu = false;
                this.router.push('/');
            } catch (error) {
                console.error('Ошибка выхода:', error);
                this.error = 'Не удалось выйти.';
            }
        },
        async addProduct() {
            try {
                await axios.post('/api/knives', this.newKnife, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.showAddProduct = false;
                this.newKnife = { market_hash_name: '', type: '' };
                this.fetchKnives();
            } catch (error) {
                console.error('Ошибка добавления ножа:', error);
                alert('Ошибка добавления: ' + (error.response?.data?.message || 'Неизвестная ошибка'));
            }
        },
        toggleTheme() {
            this.isDarkMode = !this.isDarkMode;
            localStorage.setItem('theme', this.isDarkMode ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', this.isDarkMode);
        },
        loadTheme() {
            const theme = localStorage.getItem('theme');
            this.isDarkMode = theme === 'dark';
            document.documentElement.classList.toggle('dark', this.isDarkMode);
        },
        toggleUserMenu() {
            this.showUserMenu = !this.showUserMenu;
        },
    },
    watch: {
        filters: {
            handler() {
                this.fetchKnives();
            },
            deep: true,
        },
        searchQuery: {
            handler() {
                this.fetchKnives();
            },
        },
    },
};
</script>
