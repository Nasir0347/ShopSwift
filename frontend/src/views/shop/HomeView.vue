<script setup>
import { ref, onMounted } from 'vue'
import api from '@/composables/useApi'

const products = ref([])
const loading = ref(true)

const fetchFeatured = async () => {
    try {
        const response = await api.get('/products?per_page=4')
        products.value = response.data.products.data || []
    } catch (e) {
        console.error('Failed to fetch featured', e)
    } finally {
        loading.value = false
    }
}

const getPrice = (product) => {
    if (product.variants && product.variants.length > 0) {
        return parseFloat(product.variants[0].price)
    }
    return 0
}

const getProductImage = (product) => {
    if (product.images && product.images.length > 0) {
        return product.images[0].image_path
    }
    return 'https://via.placeholder.com/300'
}

onMounted(() => {
    fetchFeatured()
})
</script>

<template>
    <div class="bg-white">
        <!-- Hero Section -->
        <div class="relative bg-gray-900">
            <!-- Background Image -->
             <div class="absolute inset-0 overflow-hidden">
                <img src="https://images.unsplash.com/photo-1521737604893-d14cc237f11d?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=2830&q=80&blend=111827&sat=-100&exp=15&blend-mode=multiply" alt="" class="h-full w-full object-cover object-center opacity-40">
             </div>
             
             <div class="relative mx-auto max-w-7xl px-6 py-24 sm:py-32 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h1 class="text-4xl font-bold tracking-tight text-white sm:text-6xl">
                        Shop the Future
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-300">
                        Discover the latest in tech, fashion, and lifestyle. Premium quality, sustainable sourcing, and fast shipping.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <router-link to="/catalog" class="rounded-md bg-indigo-500 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-400">
                            Shop Collection
                        </router-link>
                        <a href="#" class="text-sm font-semibold leading-6 text-white">Learn more <span aria-hidden="true">→</span></a>
                    </div>
                </div>
             </div>
        </div>

        <!-- Featured Products -->
        <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Trending Now</h2>
            
            <div v-if="loading" class="mt-6 text-center text-gray-500">Loading...</div>

            <div v-else class="mt-6 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                 <div v-for="product in products" :key="product.id" class="group relative">
                    <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                         <img :src="getProductImage(product)" :alt="product.title" class="h-full w-full object-cover object-center lg:h-full lg:w-full" />
                    </div>
                    <div class="mt-4 flex justify-between">
                        <div>
                        <h3 class="text-sm text-gray-700">
                            <router-link :to="`/products/${product.id}`">
                                <span aria-hidden="true" class="absolute inset-0" />
                                {{ product.title }}
                            </router-link>
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">{{ product.vendor }}</p>
                        </div>
                        <p class="text-sm font-medium text-gray-900">${{ getPrice(product).toFixed(2) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
