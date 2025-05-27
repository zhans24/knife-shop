<template>
    <div class="min-h-screen bg-gray-100 text-gray-800 font-sans">
        <header class="bg-white shadow">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-blue-600">KnifeShop</h1>
                <nav class="space-x-4">
                    <RouterLink to="/" class="text-gray-600 hover:text-blue-600 font-medium">Home</RouterLink>
                    <RouterLink to="/about" class="text-gray-600 hover:text-blue-600 font-medium">About</RouterLink>
                </nav>
            </div>
        </header>

        <main class="container mx-auto px-4 py-10">
            <RouterView />
        </main>
    </div>
</template>

<script setup>
</script>
