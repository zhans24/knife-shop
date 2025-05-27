<template>
    <div class="min-h-screen transition-colors duration-300" :class="isDarkMode ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900'">
        <!-- Navbar -->
        <nav class="p-4 flex justify-between items-center fixed w-full top-0 z-10 transition-colors duration-300" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-gray-800 text-white'">
            <div class="text-2xl font-bold">CS Knife Store</div>
            <div class="flex items-center space-x-4">
                <!-- Search Input -->
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search knives..."
                        class="p-2 pl-10 rounded-lg border-2 border-blue-500 bg-gray-700 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 w-64"
                    />
                    <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <!-- Theme Toggle -->
                <button @click="toggleTheme" class="cursor-pointer p-2 rounded-full hover:bg-gray-600 transition-opacity duration-200" :class="isDarkMode ? 'bg-gray-700' : 'bg-gray-600'">
                    <svg v-if="isDarkMode" class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg v-else class="h-6 w-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <div class="relative">
                    <button class="cursor-pointer bg-blue-600 p-2 rounded px-4 transition-opacity duration-150 hover:opacity-80" :class="isDarkMode ? 'text-white' : 'text-white'">Cart ({{ cart.length }})</button>
                    <div v-if="cart.length" class="absolute top-10 right-0 p-4 rounded shadow w-64 transition-colors duration-300" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black border-gray-300'">
                        <div v-for="item in cart" :key="item.id" class="flex justify-between mb-2">
                            {{ item.name }} <span>${{ item.price }}</span>
                        </div>
                        <button class="cursor-pointer bg-blue-500 text-white p-2 rounded-md w-full transition-opacity duration-150 hover:bg-blue-600">Checkout</button>
                        <button @click="clearCart" class="cursor-pointer bg-red-600 text-white p-2 rounded-md w-full mt-2 transition-opacity duration-150 hover:bg-red-700">Clear Cart</button>
                    </div>
                </div>
                <div v-if="isLoggedIn" class="flex space-x-2">
                    <span class="leading-none">Welcome, {{ user.name }}</span>
                    <button @click="logout" class="cursor-pointer bg-red-600 p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Logout</button>
                    <button v-if="user.is_admin" @click="showAddProduct = true" class="cursor-pointer bg-green-600 text-white p-2 rounded-md hover:opacity-80">Add Product</button>
                </div>
                <div v-else class="flex space-x-2">
                    <button @click="showLogin = true" class="cursor-pointer bg-blue-600 p-2 px-4 rounded-lg hover:bg-blue-700 transition-opacity duration-200">Login</button>
                    <button @click="showRegister = true" class="cursor-pointer bg-blue-600 p-2 px-4 rounded-lg hover:bg-blue-700 transition-opacity duration-200">Register</button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="pt-24">
            <div v-if="error" class="text-red-600 text-center p-4">
                {{ error }}
            </div>
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

        <!-- Login Modal -->
        <div v-if="showLogin" class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center">
            <div class="p-6 rounded-lg shadow max-w-md w-full transition-colors duration-200" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black'">
                <h2 class="text-xl font-bold mb-4">Login</h2>
                <input v-model="loginForm.email" type="email" placeholder="Email" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="loginForm.password" type="password" placeholder="Password" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <div class="flex space-x-2">
                    <button @click="login" class="cursor-pointer bg-blue-600 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Login</button>
                    <button @click="showLogin = false" class="cursor-pointer bg-gray-500 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Register Modal -->
        <div v-if="showRegister" class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center">
            <div class="p-6 rounded-lg shadow max-w-md w-full transition-colors duration-200" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black'">
                <h2 class="text-xl font-bold mb-4">Register</h2>
                <input v-model="registerForm.name" type="text" placeholder="Name" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="registerForm.email" type="email" placeholder="Email" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="registerForm.password" type="password" placeholder="Password" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model="registerForm.password_confirmation" type="password" placeholder="Confirm Password" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <div class="flex space-x-2">
                    <button @click="register" class="cursor-pointer bg-blue-600 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Register</button>
                    <button @click="showRegister = false" class="cursor-pointer bg-gray-500 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div v-if="showAddProduct" class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center">
            <div class="p-6 rounded-lg shadow w-full max-w-2xl transition-colors duration-200" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black'">
                <h2 class="text-xl font-bold mb-4">Add New Product</h2>
                <input v-model="newKnife.name" type="text" placeholder="Name" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <select v-model="newKnife.type" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="Karambit">Karambit</option>
                    <option value="Bayonet">Bayonet</option>
                    <option value="Butterfly">Butterfly</option>
                </select>
                <select v-model="newKnife.rarity" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="Covert">Covert</option>
                    <option value="Classified">Classified</option>
                    <option value="Rare">Rare</option>
                </select>
                <input v-model="newKnife.float_value" type="number" step="0.01" min="0" max="1" placeholder="Float Value (0.00-1.00)" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <select v-model="newKnife.wear_level" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="Factory New">Factory New</option>
                    <option value="Minimal Wear">Minimal Wear</option>
                    <option value="Field-Tested">Field-Tested</option>
                    <option value="Well-Worn">Well-Worn</option>
                    <option value="Battle-Scarred">Battle-Scarred</option>
                </select>
                <input v-model="newKnife.price" type="number" step="0.01" placeholder="Price" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <textarea v-model="newKnife.description" placeholder="Description" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'"></textarea>
                <select v-model="newKnife.color" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="Red">Red</option>
                    <option value="Blue">Blue</option>
                    <option value="Black">Black</option>
                    <option value="Fade">Fade</option>
                </select>
                <input v-model="newKnife.image_url" type="text" placeholder="Image URL" class="w-full p-2 border rounded mb-2" :class="isDarkMode ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <div class="flex space-x-2">
                    <button @click="addProduct" class="cursor-pointer bg-green-600 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Add Product</button>
                    <button @click="showAddProduct = false" class="cursor-pointer bg-gray-500 text-white p-2 rounded-md hover:opacity-80 transition-opacity duration-150">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Alert for Unauthenticated Cart Addition -->
        <div v-if="showAlert" class="fixed bottom-4 right-4 bg-red-600 text-white p-4 rounded-lg shadow">
            Please register to add items to the cart!
            <button @click="showAlert = false" class="cursor-pointer ml-2 underline hover:opacity-80 transition-opacity duration-150">Close</button>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
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
                rarity: '',
                wear_level: '',
                color: '',
                price_min: '',
                price_max: '',
            },
            searchQuery: '',
            cart: [],
            isLoggedIn: false,
            user: null,
            showLogin: false,
            showRegister: false,
            showAddProduct: false,
            showAlert: false,
            error: null,
            isDarkMode: false,
            loginForm: { email: '', password: '' },
            registerForm: { name: '', email: '', password: '', password_confirmation: '' },
            newKnife: {
                name: '', type: '', rarity: '', float_value: '', wear_level: '', price: '', description: '', color: '', image_url: ''
            },
        };
    },
    created() {
        this.fetchKnives();
        this.checkAuth();
        this.loadTheme();
    },
    methods: {
        async fetchKnives(page = 1) {
            try {
                this.knives = [];
                this.error = null;
                const params = { ...this.filters, search: this.searchQuery.trim(), page };
                const response = await axios.get('/api/steam-knives', { params });
                console.log('Fetched knives:', response.data);
                this.knives = response.data.data;
                this.pagination = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                };
                if (this.knives.length === 0 && this.searchQuery) {
                    this.error = 'No knives found matching your search.';
                }
            } catch (error) {
                console.error('Failed to fetch knives:', error);
                this.error = 'Failed to load knives from Steam Market. Please try again later.';
            }
        },
        async addToCart(knife) {
            if (!this.isLoggedIn) {
                this.showAlert = true;
                return;
            }
            try {
                await axios.post('/api/cart', { knife_id: knife.id }, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.cart.push(knife);
            } catch (error) {
                console.error('Failed to add to cart:', error);
                this.error = 'Failed to add to cart.';
            }
        },
        async clearCart() {
            try {
                await axios.post('/api/cart/clear', {}, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.cart = [];
            } catch (error) {
                console.error('Failed to clear cart:', error);
                this.error = 'Failed to clear cart.';
            }
        },
        async checkAuth() {
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
        },
        async fetchCart() {
            try {
                const response = await axios.get('/api/cart', {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.cart = response.data;
            } catch (error) {
                console.error('Failed to fetch cart:', error);
                this.error = 'Failed to fetch cart.';
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
            } catch (error) {
                console.error('Login failed:', error);
                alert('Login failed: ' + (error.response?.data?.message || 'Unknown error'));
            }
        },
        async register() {
            try {
                const response = await axios.post('/api/register', this.registerForm);
                localStorage.setItem('token', response.data.token);
                this.isLoggedIn = true;
                this.user = response.data.user;
                this.showRegister = false;
                this.fetchCart();
            } catch (error) {
                console.error('Registration failed:', error);
                alert('Registration failed: ' + (error.response?.data?.message || 'Unknown error'));
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
            } catch (error) {
                console.error('Logout failed:', error);
                this.error = 'Failed to logout.';
            }
        },
        async addProduct() {
            try {
                await axios.post('/api/knives', this.newKnife, {
                    headers: { Authorization: `Bearer ${localStorage.getItem('token')}` },
                });
                this.showAddProduct = false;
                this.newKnife = { name: '', type: '', rarity: '', float_value: '', wear_level: '', price: '', description: '', color: '', image_url: '' };
                this.fetchKnives();
            } catch (error) {
                console.error('Failed to add product:', error);
                alert('Failed to add product: ' + (error.response?.data?.message || 'Unknown error'));
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
            immediate: true,
        },
    },
};
</script>
