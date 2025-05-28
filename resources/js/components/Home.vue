<template>
    <div class="flex pt-4 relative">
        <div class="w-1/4 p-4 shadow transition-colors duration-200" :class="isDarkMode ? 'bg-gray-700 text-white' : 'bg-gray-200 text-black'">
            <h2 class="text-xl font-bold mb-4">Фильтры</h2>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Тип ножа</label>
                <select v-model="filters.type" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="">Все</option>
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
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Износ</label>
                <select v-model="filters.wear_level" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="">Все</option>
                    <option value="Factory New">Factory New</option>
                    <option value="Minimal Wear">Minimal Wear</option>
                    <option value="Field-Tested">Field-Tested</option>
                    <option value="Well-Worn">Well-Worn</option>
                    <option value="Battle-Scarred">Battle-Scarred</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Цена</label>
                <input v-model.number="filters.price_min" type="number" placeholder="Мин. цена" min="0" step="0.01" class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
                <input v-model.number="filters.price_max" type="number" placeholder="Макс. цена" min="0" step="0.01" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'" />
            </div>
        </div>
        <div class="w-3/4 p-4">
            <div v-if="loading" class="text-center p-4" :class="isDarkMode ? 'text-gray-300' : 'text-gray-600'">Загрузка...</div>
            <div v-else-if="!knives.length" class="text-center p-4" :class="isDarkMode ? 'text-gray-300' : 'text-gray-600'">Ножи не найдены</div>
            <transition-group name="knife-list" tag="div" class="grid grid-cols-3 gap-4 mb-20">
                <div v-for="knife in knives" :key="knife.id" class="border p-4 rounded shadow" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <img :src="knife.image_url" :alt="knife.name" class="w-full h-40 object-contain mb-2 rounded" loading="lazy" />
                    <h3 class="text-lg font-bold">{{ knife.name }}</h3>
                    <p>Тип: {{ knife.type || 'Н/Д' }}</p>
                    <p>Редкость: {{ knife.rarity || 'Н/Д' }}</p>
                    <p>Износ: {{ knife.wear_level || 'Н/Д' }}</p>
                    <p>Цена: ${{ knife.price.toFixed(2) }}</p>
                    <p>{{ knife.description || 'Без описания' }}</p>
                    <button @click="$emit('add-to-cart', knife)" class="cursor-pointer bg-blue-600 text-white p-2 rounded-md w-full mt-2 hover:bg-blue-700">В корзину</button>
                </div>
            </transition-group>
            <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 flex justify-center space-x-2 bg-opacity-90 p-4 rounded-lg" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-gray-200 text-black'">
                <button :disabled="pagination.current_page === 1 || loading" @click="$emit('change-page', pagination.current_page - 1)" class="cursor-pointer bg-gray-600 text-white p-2 px-4 rounded-md disabled:opacity-50 hover:bg-gray-700">Назад</button>
                <span class="flex items-center" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Страница {{ pagination.current_page }} из {{ pagination.last_page }}</span>
                <button :disabled="pagination.current_page === pagination.last_page || loading" @click="$emit('change-page', pagination.current_page + 1)" class="cursor-pointer bg-gray-600 text-white p-2 px-4 rounded-md disabled:opacity-50 hover:bg-gray-700">Вперед</button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['knives', 'filters', 'pagination', 'isDarkMode'],
    emits: ['update:filters', 'add-to-cart', 'change-page'],
    data() {
        return {
            loading: false,
        };
    },
    watch: {
        knives() {
            this.loading = false;
        },
    },
};
</script>

<style scoped>
.knife-list-enter-active,
.knife-list-leave-active {
    transition: all 0.3s ease;
}
.knife-list-enter-from,
.knife-list-leave-to {
    opacity: 0;
    transform: translateY(20px);
}
</style>
