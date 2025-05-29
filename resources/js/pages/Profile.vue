<template>
    <div class="p-4">
        <h2 class="text-2xl font-bold mb-4">Профиль</h2>
        <p v-if="user">Имя: {{ user.name }}</p>
        <p v-if="user">Email: {{ user.email }}</p>
        <button @click="logout" class="cursor-pointer bg-red-600 text-white p-2 rounded-md hover:bg-red-700">Выйти</button>
    </div>
</template>

<script>
export default {
    props: ['user'],
    emits: ['logout'],
    methods: {
        logout() {
            this.$emit('logout');
        },
    },
};
</script>
