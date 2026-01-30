<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '@/composables/useApi'

const products = ref([])
const loading = ref(true)

const fetchProducts = async () => {
  loading.value = true
  try {
    const response = await api.get('/products?per_page=50') // Fetch more for catalog
    // API Standard: response.data.products.data
    products.value = response.data.products.data || []
  } catch (e) {
    console.error('Failed to fetch products', e)
  } finally {
    loading.value = false
  }
}

const getPrice = (product) => {
    // If variants exist, use first variant price, else 0
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
  fetchProducts()
})
</script>

<template>
  <div class="bg-white">
    <div class="mx-auto max-w-2xl px-4 py-16 sm:px-6 sm:py-24 lg:max-w-7xl lg:px-8">
      <h2 class="text-2xl font-bold tracking-tight text-gray-900">Catalog</h2>

      <div v-if="loading" class="mt-6 text-center text-gray-500">Loading products...</div>
      
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
