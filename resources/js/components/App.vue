<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Navbar -->
        <nav class="bg-gray-800 text-white p-4 flex justify-between items-center fixed w-full top-0 z-10">
            <div class="text-2xl font-bold">CS Knife Store</div>
            <div class="flex items-center space-x-4">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search knives..."
                    class="p-2 rounded text-black"
                />
                <div class="relative">
                    <button class="bg-blue-500 p-2 rounded">Cart ({{ cart.length }})</button>
                    <div v-if="cart.length" class="absolute top-10 right-0 bg-white text-black p-4 rounded shadow w-64">
                        <div v-for="item in cart" :key="item.id" class="mb-2">
                            {{ item.name }} - ${{ item.price }}
                        </div>
                        <button class="bg-green-500 text-white p-2 rounded w-full">Checkout</button>
                        <button @click="clearCart" class="bg-red-500 text-white p-2 rounded w-full mt-2">Clear Cart</button>
                    </div>
                </div>
                <div v-if="isLoggedIn" class="flex space-x-2">
                    <span>Welcome, {{ user.name }}</span>
                    <button @click="logout" class="bg-red-500 p-2 rounded">Logout</button>
                    <button @click="showAddProduct = true" class="bg-green-500 p-2 rounded">Add Product</button>
                </div>
                <div v-else class="flex space-x-2">
                    <button @click="showLogin = true" class="bg-blue-500 p-2 rounded">Login</button>
                    <button @click="showRegister = true" class="bg-blue-500 p-2 rounded">Register</button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="pt-16">
            <router-view
                :knives="knives"
                :filters="filters"
                :pagination="pagination"
                @update:filters="filters = $event"
                @add-to-cart="addToCart"
                @change-page="fetchKnives"
            ></router-view>
        </div>

        <!-- Login Modal -->
        <div v-if="showLogin" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4">Login</h2>
                <input v-model="loginForm.email" type="email" placeholder="Email" class="w-full p-2 border rounded mb-2" />
                <input v-model="loginForm.password" type="password" placeholder="Password" class="w-full p-2 border rounded mb-2" />
                <div class="flex space-x-2">
                    <button @click="login" class="bg-blue-500 text-white p-2 rounded">Login</button>
                    <button @click="showLogin = false" class="bg-gray-500 text-white p-2 rounded">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Register Modal -->
        <div v-if="showRegister" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow">
                <h2 class="text-xl font-bold mb-4">Register</h2>
                <input v-model="registerForm.name" type="text" placeholder="Name" class="w-full p-2 border rounded mb-2" />
                <input v-model="registerForm.email" type="email" placeholder="Email" class="w-full p-2 border rounded mb-2" />
                <input v-model="registerForm.password" type="password" placeholder="Password" class="w-full p-2 border rounded mb-2" />
                <input v-model="registerForm.password_confirmation" type="password" placeholder="Confirm Password" class="w-full p-2 border rounded mb-2" />
                <div class="flex space-x-2">
                    <button @click="register" class="bg-blue-500 text-white p-2 rounded">Register</button>
                    <button @click="showRegister = false" class="bg-gray-500 text-white p-2 rounded">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Add Product Modal -->
        <div v-if="showAddProduct" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow w-1/2">
                <h2 class="text-xl font-bold mb-4">Add New Product</h2>
                <input v-model="newKnife.name" type="text" placeholder="Name" class="w-full p-2 border rounded mb-2" />
                <select v-model="newKnife.type" class="w-full p-2 border rounded mb-2">
                    <option value="Karambit">Karambit</option>
                    <option value="Bayonet">Bayonet</option>
                    <option value="Butterfly">Butterfly</option>
                </select>
                <select v-model="newKnife.rarity" class="w-full p-2 border rounded mb-2">
                    <option value="Covert">Covert</option>
                    <option value="Classified">Classified</option>
                    <option value="Rare">Rare</option>
                </select>
                <input v-model="newKnife.float_value" type="number" step="0.01" min="0" max="1" placeholder="Float Value (0.00-1.00)" class="w-full p-2 border rounded mb-2" />
                <select v-model="newKnife.wear_level" class="w-full p-2 border rounded mb-2">
                    <option value="Factory New">Factory New</option>
                    <option value="Minimal Wear">Minimal Wear</option>
                    <option value="Field-Tested">Field-Tested</option>
                    <option value="Well-Worn">Well-Worn</option>
                    <option value="Battle-Scarred">Battle-Scarred</option>
                </select>
                <input v-model="newKnife.price" type="number" step="0.01" placeholder="Price" class="w-full p-2 border rounded mb-2" />
                <textarea v-model="newKnife.description" placeholder="Description" class="w-full p-2 border rounded mb-2"></textarea>
                <select v-model="newKnife.color" class="w-full p-2 border rounded mb-2">
                    <option value="Red">Red</option>
                    <option value="Blue">Blue</option>
                    <option value="Black">Black</option>
                    <option value="Fade">Fade</option>
                </select>
                <input v-model="newKnife.image_url" type="text" placeholder="Image URL" class="w-full p-2 border rounded mb-2" />
                <div class="flex space-x-2">
                    <button @click="addProduct" class="bg-green-500 text-white p-2 rounded">Add Product</button>
                    <button @click="showAddProduct = false" class="bg-gray-500 text-white p-2 rounded">Cancel</button>
                </div>
            </div>
        </div>

        <!-- Alert for Unauthenticated Cart Addition -->
        <div v-if="showAlert" class="fixed bottom-4 right-4 bg-red-500 text-white p-4 rounded shadow">
            Please register to add items to the cart!
            <button @click="showAlert = false" class="ml-2 underline">Close</button>
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
    },
    methods: {
        async fetchKnives(page = 1) {
            try {
                this.knives = []; // Clear to prevent duplicates
                const params = { ...this.filters, search: this.searchQuery, page };
                const response = await axios.get('/api/knives', { params });
                this.knives = response.data.data;
                this.pagination = {
                    current_page: response.data.current_page,
                    last_page: response.data.last_page,
                    total: response.data.total,
                };
            } catch (error) {
                console.error('Failed to fetch knives:', error);
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
                alert('Login failed');
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
                alert('Registration failed');
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
                alert('Failed to add product');
            }
        },
    },
    watch: {
        filters: {
            handler() {
                this.fetchKnives();
            },
            deep: true,
        },
        searchQuery() {
            this.fetchKnives();
        },
    },
};
</script>
