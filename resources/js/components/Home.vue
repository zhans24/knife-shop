<template>
    <div class="flex pt-4">
        <!-- Filters Sidebar -->
        <div class="w-1/4 p-4 bg-white shadow">
            <h2 class="text-xl font-bold mb-4">Filters</h2>
            <div class="mb-4">
                <label class="block mb-1">Type</label>
                <select v-model="filters.type" class="w-full p-2 border rounded">
                    <option value="">All</option>
                    <option value="Karambit">Karambit</option>
                    <option value="Bayonet">Bayonet</option>
                    <option value="Butterfly">Butterfly</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Rarity</label>
                <select v-model="filters.rarity" class="w-full p-2 border rounded">
                    <option value="">All</option>
                    <option value="Covert">Covert</option>
                    <option value="Classified">Classified</option>
                    <option value="Rare">Rare</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Wear Level</label>
                <select v-model="filters.wear_level" class="w-full p-2 border rounded">
                    <option value="">All</option>
                    <option value="Factory New">Factory New</option>
                    <option value="Minimal Wear">Minimal Wear</option>
                    <option value="Field-Tested">Field-Tested</option>
                    <option value="Well-Worn">Well-Worn</option>
                    <option value="Battle-Scarred">Battle-Scarred</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Color</label>
                <select v-model="filters.color" class="w-full p-2 border rounded">
                    <option value="">All</option>
                    <option value="Red">Red</option>
                    <option value="Blue">Blue</option>
                    <option value="Black">Black</option>
                    <option value="Fade">Fade</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block mb-1">Price Range</label>
                <input
                    v-model="filters.price_min"
                    type="number"
                    placeholder="Min Price"
                    class="w-full p-2 border rounded mb-2"
                />
                <input
                    v-model="filters.price_max"
                    type="number"
                    placeholder="Max Price"
                    class="w-full p-2 border rounded"
                />
            </div>
        </div>

        <!-- Main Content (Knife Cards) -->
        <div class="w-3/4 p-4">
            <div v-if="knives.length === 0" class="text-center p-4">
                No knives found.
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div v-for="knife in knives" :key="knife.id" class="border p-4 rounded shadow">
                    <img :src="knife.image_url" :alt="knife.name" class="w-full h-40 object-cover mb-2" />
                    <h3 class="text-lg font-bold">{{ knife.name }}</h3>
                    <p>Type: {{ knife.type }}</p>
                    <p>Rarity: {{ knife.rarity }}</p>
                    <p>Wear: {{ knife.wear_level }} ({{ knife.float_value }})</p>
                    <p>Color: {{ knife.color }}</p>
                    <p>Price: ${{ knife.price }}</p>
                    <p>{{ knife.description }}</p>
                    <button
                        @click="$emit('add-to-cart', knife)"
                        class="bg-blue-500 text-white p-2 rounded w-full mt-2"
                    >
                        Add to Cart
                    </button>
                </div>
            </div>
            <!-- Pagination Controls -->
            <div class="mt-4 flex justify-center space-x-2">
                <button
                    :disabled="pagination.current_page === 1"
                    @click="$emit('change-page', pagination.current_page - 1)"
                    class="bg-gray-500 text-white p-2 rounded disabled:opacity-50"
                >
                    Previous
                </button>
                <span>Page {{ pagination.current_page }} of {{ pagination.last_page }}</span>
                <button
                    :disabled="pagination.current_page === pagination.last_page"
                    @click="$emit('change-page', pagination.current_page + 1)"
                    class="bg-gray-500 text-white p-2 rounded disabled:opacity-50"
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['knives', 'filters', 'pagination'],
    emits: ['update:filters', 'add-to-cart', 'change-page'],
};
</script>
