<template>
    <div class="fixed inset-0 bg-gray-900 bg-opacity-80 flex items-center justify-center z-50">
        <div
            class="p-6 rounded-lg shadow max-w-md w-full transition-colors duration-200"
            :class="isDark ? 'bg-gray-800 text-white' : 'bg-white text-black'"
        >
            <h2 class="text-xl font-bold mb-4">Регистрация</h2>
            <input
                v-model="form.name"
                type="text"
                placeholder="Имя"
                class="w-full p-2 border rounded mb-2"
                :class="isDark ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'"
            />
            <input
                v-model="form.email"
                type="email"
                placeholder="Email"
                class="w-full p-2 rounded mb-2 border"
                :class="isDark ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'"
            />
            <input
                v-model="form.password"
                type="password"
                placeholder="Пароль"
                class="w-full p-2 border rounded mb-2"
                :class="isDark ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'"
            />
            <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="Подтвердите пароль"
                class="w-full p-2 border rounded mb-4"
                :class="isDark ? 'bg-gray-700 text-white border-gray-600' : 'bg-white text-black border-gray-300'"
            />
            <div v-if="error" class="text-red-600 mb-4">{{ error }}</div>
            <div class="flex space-x-2">
                <button
                    @click="emit('register', form)"
                    class="cursor-pointer bg-blue-600 p-2 px-4 py-2 text-white rounded-md hover:bg-blue-700 transition-colors duration-150"
                >
                    Зарегистрироваться
                </button>
                <button
                    @click="emit('close')"
                    class="cursor-pointer bg-gray-500 text-white p-2 px-4 py-2 rounded-md hover:bg-gray-600 transition-colors duration-150"
                >
                    Отмена
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

defineProps({
    isDark: { type: Boolean, default: false },
    error: String,
});
const emit = defineEmits(['register', 'close']);

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});
</script>
