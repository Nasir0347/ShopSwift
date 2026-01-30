<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/composables/useApi'
import { useCartStore } from '@/stores/cart'
import { CheckIcon } from '@heroicons/vue/20/solid'

const route = useRoute()
const cart = useCartStore()

const product = ref(null)
const loading = ref(true)
const selectedVariant = ref(null)

const fetchProduct = async () => {
    loading.value = true
    try {
        const response = await api.get(`/products/${route.params.id}`)
        // API Standard: response.data.product
        product.value = response.data.product
        
        // Select first variant by default
        if (product.value.variants && product.value.variants.length > 0) {
            selectedVariant.value = product.value.variants[0]
        }
    } catch (e) {
        console.error('Failed to fetch product', e)
    } finally {
        loading.value = false
    }
}

const isOutOfStock = computed(() => {
    if (!selectedVariant.value) return true
    // If inventory quantity is defined and <= 0, it's out of stock
    return (selectedVariant.value.inventory?.quantity ?? 0) <= 0
})

const addToCart = () => {
    if (!product.value || isOutOfStock.value) return
    cart.addItem(product.value, selectedVariant.value, 1)
}

const mainImage = computed(() => {
    // Priority: 1) Selected variant's image, 2) First product image, 3) Placeholder
    if (selectedVariant.value?.image?.image_path) {
        return selectedVariant.value.image.image_path
    }
    if (product.value?.images?.length > 0) {
        return product.value.images[0].image_path
    }
    return 'https://via.placeholder.com/600'
})

onMounted(() => {
    fetchProduct()
})
</script>

<template>
    <div class="bg-white">
        <div class="pt-6 pb-16 sm:pb-24">
            <div v-if="loading" class="text-center py-20 text-gray-500">Loading...</div>
            
            <div v-else-if="product" class="mx-auto mt-8 max-w-2xl px-4 sm:px-6 lg:max-w-7xl lg:px-8">
                <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start">
                    <!-- Image Gallery -->
                    <div class="flex flex-col-reverse">
                        <div class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-lg bg-gray-100">
                            <img :src="mainImage" :alt="product.title" class="h-full w-full object-cover object-center" />
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="mt-10 px-4 sm:mt-16 sm:px-0 lg:mt-0">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ product.title }}</h1>

                        <div class="mt-3">
                            <h2 class="sr-only">Product information</h2>
                            <p class="text-3xl tracking-tight text-gray-900" v-if="selectedVariant">
                                ${{ parseFloat(selectedVariant.price).toFixed(2) }}
                                <span v-if="selectedVariant.compare_at_price && selectedVariant.compare_at_price > selectedVariant.price" 
                                    class="ml-2 text-lg text-gray-500 line-through">
                                    ${{ parseFloat(selectedVariant.compare_at_price).toFixed(2) }}
                                </span>
                            </p>
                        </div>

                        <!-- Availability & Stock -->
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center" v-if="selectedVariant">
                                <span class="text-sm font-medium text-gray-500">Availability:</span>
                                <span v-if="selectedVariant.inventory?.quantity > 0" 
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    In Stock ({{ selectedVariant.inventory?.quantity }} available)
                                </span>
                                <span v-else 
                                    class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Out of Stock
                                </span>
                            </div>
                            <div v-if="product.vendor" class="flex items-center">
                                <span class="text-sm font-medium text-gray-500">Vendor:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ product.vendor }}</span>
                            </div>
                            <div v-if="selectedVariant?.sku" class="flex items-center">
                                <span class="text-sm font-medium text-gray-500">SKU:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ selectedVariant.sku }}</span>
                            </div>
                            <div v-if="product.category" class="flex items-center">
                                <span class="text-sm font-medium text-gray-500">Category:</span>
                                <span class="ml-2 text-sm text-gray-900">{{ product.category.name }}</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <h3 class="sr-only">Description</h3>
                            <div class="space-y-6 text-base text-gray-700" v-html="product.description"></div>
                        </div>

                        <div class="mt-6">
                             <!-- Variant Selector -->
                             <div v-if="product.variants.length > 1" class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                                <div class="flex flex-wrap gap-2">
                                    <button 
                                        v-for="variant in product.variants" 
                                        :key="variant.id"
                                        @click="selectedVariant = variant"
                                        :class="[
                                            selectedVariant?.id === variant.id 
                                                ? 'bg-indigo-600 text-white border-indigo-600' 
                                                : 'bg-white text-gray-900 border-gray-200 hover:bg-gray-50',
                                            'border rounded-md px-3 py-2 text-sm font-medium uppercase sm:flex-1 cursor-pointer focus:outline-none'
                                        ]"
                                    >
                                        {{ variant.title || variant.option1 || variant.color || 'Default' }}
                                    </button>
                                </div>
                             </div>

                             <button 
                                type="button" 
                                @click="addToCart"
                                :disabled="isOutOfStock"
                                :class="[
                                    isOutOfStock 
                                        ? 'bg-gray-400 cursor-not-allowed hover:bg-gray-400' 
                                        : 'bg-indigo-600 hover:bg-indigo-700',
                                    'flex w-full items-center justify-center rounded-md border border-transparent px-8 py-3 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-50'
                                ]"
                            >
                                {{ isOutOfStock ? 'Out of Stock' : 'Add to bag' }}
                             </button>
                        </div>
                        
                        <div class="mt-8 border-t border-gray-200 pt-8" v-if="product.tags">
                             <h3 class="text-sm font-medium text-gray-900">Tags</h3>
                             <div class="mt-2 prose prose-sm text-gray-500">
                                <ul role="list">
                                    <li v-for="tag in product.tags.split(',')" :key="tag">{{ tag.trim() }}</li>
                                </ul>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-20 text-red-500">
                Product not found.
            </div>
        </div>
    </div>
</template>
