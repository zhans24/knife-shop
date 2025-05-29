<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div
            class="p-6 rounded-lg shadow-lg w-full max-w-md"
            :class="isDark ? 'bg-gray-800 text-white' : 'bg-white text-black'"
        >
            <h2 class="text-2xl font-bold mb-4">Вход</h2>
            <div v-if="error" class="text-red-600 mb-4">{{ error }}</div>
            <form @submit.prevent="submit">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        id="email"
                        class="w-full p-2 mt-1 rounded border"
                        :class="isDark ? 'bg-gray-700 text-white border-gray-600' : 'bg-gray-100 text-black border-gray-300'"
                        required
                    />
                </div>
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium">Пароль</label>
                    <input
                        v-model="form.password"
                        type="password"
                        id="password"
                        class="w-full p-2 mt-1 rounded border"
                        :class="isDark ? 'bg-gray-700 text-white border-gray-600' : 'bg-gray-100 text-black border-gray-300'"
                        required
                    />
                </div>
                <div class="flex justify-between">
                    <button
                        type="submit"
                        class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700"
                    >
                        Войти
                    </button>
                    <button
                        type="button"
                        @click="$emit('close')"
                        class="bg-gray-600 text-white p-2 rounded hover:bg-gray-700"
                    >
                        Закрыть
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        isDark: Boolean,
        error: String,
    },
    data() {
        return {
            form: {
                email: '',
                password: '',
            },
        };
    },
    methods: {
        submit() {
            this.$emit('login', this.form);
        },
    },
};
</script>
