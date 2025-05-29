```html
<template>
    <div
        class="min-h-screen transition-colors duration-300"
        :class="isDarkMode ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-900'"
    >
        <!-- Navbar -->
        <nav
            class="p-4 flex justify-between items-center fixed w-full top-0 z-10 transition-colors duration-300"
            :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-gray-800 text-white'"
        >
            <div class="text-2xl font-bold"><a href="/">CS Knife Store</a></div>
            <div class="flex items-center space-x-4">
                <!-- Search -->
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Поиск ножей..."
                        class="p-2 pl-10 rounded-lg border-2 border-blue-500 bg-gray-700 text-white placeholder-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-400 w-64"
                    />
                    <svg
                        class="absolute left-3 top-1/2 transform -translate-y-1/2 h-5 w-5 text-gray-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        ></path>
                    </svg>
                </div>
                <!-- Theme Toggle -->
                <button
                    @click="toggleTheme"
                    class="cursor-pointer p-2 rounded-full hover:bg-gray-600 transition-opacity duration-200"
                    :class="isDarkMode ? 'bg-gray-700' : 'bg-gray-600'"
                >
                    <svg
                        v-if="isDarkMode"
                        class="h-6 w-6 text-yellow-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
                        ></path>
                    </svg>
                    <svg
                        v-else
                        class="h-6 w-6 text-gray-300"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
                        ></path>
                    </svg>
                </button>
                <!-- Cart -->
                <div class="relative">
                    <button
                        @click="toggleCartMenu"
                        class="cursor-pointer bg-blue-600 p-2 rounded px-4 transition-opacity duration-150 hover:opacity-80 text-white"
                    >
                        Корзина ({{ cart.reduce((sum, item) => sum + item.quantity, 0) }})
                    </button>
                    <div
                        v-if="showCartMenu"
                        class="absolute top-10 right-0 p-4 rounded shadow w-96 transition-colors duration-300 z-20"
                        :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-white text-black border-gray-300'"
                    >
                        <div v-if="cart.length === 0" class="text-center py-4">
                            <p class="text-lg font-semibold">Корзина пуста</p>
                            <p class="text-sm text-gray-400">Добавьте что-то в корзину!</p>
                        </div>
                        <div v-else>
                            <div v-for="item in cart" :key="item.id" class="border p-2 rounded mb-2 flex items-center space-x-2" :class="isDarkMode ? 'bg-gray-700 border-gray-600' : 'bg-gray-100 border-gray-200'">
                                <img :src="item.image_url || '/placeholder.jpg'" :alt="item.steam_name || 'Knife'" class="w-16 h-16 object-contain rounded" loading="lazy" />
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold truncate">{{ item.steam_name || 'Без названия' }}</h4>
                                    <p class="text-xs">Цена: {{ typeof item.price === 'number' ? '$' + item.price.toFixed(2) : 'N/A' }}</p>
                                    <p class="text-xs">Итого: {{ typeof item.price === 'number' ? '$' + (item.price * item.quantity).toFixed(2) : 'N/A' }}</p>
                                    <div class="flex items-center space-x-2 mt-1">
                                        <button
                                            @click="updateQuantity(item.id, item.quantity - 1)"
                                            class="bg-gray-600 text-white p-1 rounded w-6 h-6 flex items-center justify-center text-xs"
                                            :disabled="item.quantity <= 1"
                                        >-</button>
                                        <span class="text-sm">{{ item.quantity }}</span>
                                        <button
                                            @click="updateQuantity(item.id, item.quantity + 1)"
                                            class="bg-gray-600 text-white p-1 rounded w-6 h-6 flex items-center justify-center text-xs"
                                        >+</button>
                                        <button
                                            @click="removeFromCart(item.id)"
                                            class="bg-red-600 text-white p-1 rounded w-6 h-6 flex items-center justify-center"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="text-lg font-bold mt-4">
                                Общая цена: ${{ totalPrice }}
                            </div>
                            <button
                                @click="checkout"
                                class="cursor-pointer bg-blue-500 text-white p-2 rounded-md w-full transition-opacity duration-150 hover:bg-blue-600"
                            >
                                Оформить
                            </button>
                            <button
                                @click="clearCart"
                                class="cursor-pointer bg-red-600 text-white p-2 rounded-md w-full mt-2 transition-opacity duration-150 hover:bg-red-700"
                            >
                                Очистить корзину
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Auth -->
                <div v-if="authLoading" class="text-gray-300">Загрузка...</div>
                <div v-else-if="isLoggedIn" class="relative">
                    <button
                        @click="toggleUserMenu"
                        class="flex items-center space-x-2 cursor-pointer bg-blue-600 p-2 px-4 rounded-lg hover:bg-blue-700 transition-opacity duration-200"
                    >
                        <svg
                            class="h-6 w-6 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                            ></path>
                        </svg>
                        <span>{{ user?.name || 'Профиль' }}</span>
                    </button>
                    <div
                        v-if="showUserMenu"
                        class="absolute top-10 right-0 bg-gray-800 text-white rounded shadow w-48 p-2 transition-opacity duration-200"
                    >
                        <router-link
                            to="/profile"
                            class="block p-2 hover:bg-gray-700 rounded"
                        >Профиль</router-link>
                        <button
                            @click="logout"
                            class="block w-full text-left p-2 hover:bg-red-600 rounded transition-colors duration-200"
                        >
                            Выйти
                        </button>
                    </div>
                </div>
                <div v-else class="flex space-x-2">
                    <button
                        @click="showLogin = true"
                        class="cursor-pointer bg-blue-600 p-2 px-4 rounded-lg hover:bg-blue-700 transition-opacity duration-200"
                    >
                        Войти
                    </button>
                    <button
                        @click="showRegister = true"
                        class="cursor-pointer bg-blue-600 p-2 px-4 rounded-lg hover:bg-blue-700 transition-opacity duration-200"
                    >
                        Регистрация
                    </button>
                </div>
            </div>
        </nav>
        <div class="pt-20">
            <div v-if="error" class="text-red-600 text-center p-4">{{ error }}</div>
            <router-view
                :knives="knives"
                v-model:filters="filters"
                :pagination="pagination"
                :is-dark-mode="isDarkMode"
                :loading="loading"
                @add-to-cart="addToCart"
                @change-page="fetchKnives"
            />
        </div>
        <!-- Modals -->
        <Login v-if="showLogin || showAlert" :is-dark="isDarkMode" :error="error" @login="login" @close="showLogin = false; showAlert = false; error = null" />
        <Register
            v-if="showRegister"
            :is-dark="isDarkMode"
            :error="error"
            @register="register"
            @close="showRegister = false; error = null"
        />
        <!-- Alert -->
        <div
            v-if="showAlert"
            class="fixed bottom-4 right-4 bg-red-600 text-white p-4 rounded-lg shadow"
        >
            Зарегистрируйтесь, чтобы добавить товары в корзину!
            <button
                @click="showAlert = false; showLogin = true"
                class="ml-2 underline hover:opacity-80 transition-opacity"
            >
                Войти
            </button>
        </div>
    </div>
</template>

<script>
import Login from '@/components/Login.vue';
import Register from '@/components/Register.vue';
import { useApp } from '@/scripts/useApp';

export default {
    components: { Register, Login },
    setup() {
        return useApp();
    },
};
</script>
```
