<script setup>
import { ref, onMounted } from 'vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import api from '@/composables/useApi'

// Reusing Home view logic but for a dedicated page
const products = ref([])

onMounted(async () => {
  try {
    const response = await api.get('/products')
    // Handle API structure { data: { data: [...] } }
    const productData = response.data.data.data || []
    products.value = productData.map(p => ({
      id: p.id,
      name: p.title,
      href: '#',
      price: '$' + (p.variants?.[0]?.price || '0.00'),
      imageSrc: p.images?.[0]?.image_path || 'https://placehold.co/600x400',
      imageAlt: p.title,
      color: p.variants?.[0]?.color || 'Standard',
      variants: p.variants // Critical for checkout
    }))
  } catch (error) {
    console.error('Failed to fetch products', error)
  }
})
</script>

<template>
  <div class="bg-white">
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 sm:py-12 lg:max-w-7xl lg:px-8">
      <h2 class="text-2xl font-bold tracking-tight text-gray-900">All Products</h2>

      <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
        <div v-for="product in products" :key="product.id" class="group relative">
          <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
            <img :src="product.imageSrc" :alt="product.imageAlt" class="h-full w-full object-cover object-center lg:h-full lg:w-full" />
          </div>
          <div class="mt-4 flex justify-between">
            <div>
              <h3 class="text-sm text-gray-700">
                <a :href="product.href">
                  <span aria-hidden="true" class="absolute inset-0" />
                  {{ product.name }}
                </a>
              </h3>
              <p class="mt-1 text-sm text-gray-500">{{ product.color }}</p>
            </div>
            <p class="text-sm font-medium text-gray-900">{{ product.price }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
