<template>
    <div class="flex pt-4 relative">
        <!-- Filters Sidebar -->
        <div class="w-1/4 p-4 shadow transition-colors duration-200" :class="isDarkMode ? 'bg-gray-700 text-white' : 'bg-gray-200 text-black'">
            <h2 class="text-xl font-bold mb-4">Filters</h2>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Type</label>
                <select v-model="filters.type" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="">All</option>
                    <option value="Karambit">Karambit</option>
                    <option value="Bayonet">Bayonet</option>
                    <option value="Butterfly">Butterfly</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Rarity</label>
                <select v-model="filters.rarity" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="">All</option>
                    <option value="Covert">Covert</option>
                    <option value="Classified">Classified</option>
                    <option value="Rare">Rare</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Wear Level</label>
                <select v-model="filters.wear_level" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="">All</option>
                    <option value="Factory New">Factory New</option>
                    <option value="Minimal Wear">Minimal Wear</option>
                    <option value="Field-Tested">Field-Tested</option>
                    <option value="Well-Worn">Well-Worn</option>
                    <option value="Battle-Scarred">Battle-Scarred</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Color</label>
                <select v-model="filters.color" class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'">
                    <option value="">All</option>
                    <option value="Red">Red</option>
                    <option value="Blue">Blue</option>
                    <option value="Black">Black</option>
                    <option value="Fade">Fade</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1 font-medium" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Price Range</label>
                <input
                    v-model="filters.price_min"
                    type="number"
                    placeholder="Min Price"
                    class="w-full p-2 border rounded mb-2 focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'"
                />
                <input
                    v-model="filters.price_max"
                    type="number"
                    placeholder="Max Price"
                    class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400"
                    :class="isDarkMode ? 'bg-gray-800 text-white border-gray-600' : 'bg-white text-black border-gray-300'"
                />
            </div>
        </div>

        <!-- Main Content (Knife Cards) -->
        <div class="w-3/4 p-4">
            <div v-if="knives.length === 0" class="text-center p-4" :class="isDarkMode ? 'text-gray-300' : 'text-gray-600'">
                No knives found.
            </div>
            <div class="grid grid-cols-3 gap-4 mb-20">
                <div v-for="knife in knives" :key="knife.id" class="border p-4 rounded shadow transition-colors duration-200" :class="isDarkMode ? 'bg-gray-800 text-white border-gray-700' : 'bg-white text-black border-gray-300'">
                    <img :src="knife.image_url" :alt="knife.name" class="w-full h-40 object-cover mb-2 rounded" />
                    <h3 class="text-lg font-bold">{{ knife.name }}</h3>
                    <p>Type: {{ knife.type }}</p>
                    <p>Rarity: {{ knife.rarity }}</p>
                    <p>Wear: {{ knife.wear_level }} ({{ knife.float_value }})</p>
                    <p>Color: {{ knife.color }}</p>
                    <p>Price: ${{ knife.price }}</p>
                    <p>{{ knife.description }}</p>
                    <button
                        @click="$emit('add-to-cart', knife)"
                        class="cursor-pointer bg-blue-600 text-white p-2 rounded-md w-full mt-2 hover:opacity-80 transition-opacity duration-150"
                    >
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- Pagination Controls -->
            <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 flex justify-center space-x-2 bg-opacity-90 p-4 rounded-lg" :class="isDarkMode ? 'bg-gray-800 text-white' : 'bg-gray-200 text-black'">
                <button
                    :disabled="pagination.current_page === 1"
                    @click="$emit('change-page', pagination.current_page - 1)"
                    class="cursor-pointer bg-gray-600 text-white p-2 px-4 rounded-md disabled:opacity-50 hover:opacity-80 transition-opacity duration-150"
                >
                    Previous
                </button>
                <span class="flex items-center" :class="isDarkMode ? 'text-gray-200' : 'text-gray-800'">Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                <button
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="$emit('change-page', pagination.current_page + 1)"
                    class="cursor-pointer bg-gray-600 text-white p-2 px-4 rounded-md disabled:opacity-50 hover:opacity-80 transition-opacity duration-150"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['knives', 'filters', 'pagination', 'isDarkMode'],
    emits: ['update:filters', 'add-to-cart', 'change-page'],
};
</script>
