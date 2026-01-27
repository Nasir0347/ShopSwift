<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/composables/useApi'
import BaseButton from '@/components/ui/BaseButton.vue'

const route = useRoute()
const loading = ref(true)
const product = ref(null)
const selectedVariant = ref(null)
const quantity = ref(1)

onMounted(async () => {
    // Note: Backend doesn't support get-by-slug specifically in standard resource usually, 
    // but the ID route works, or we might need to filter.
    // Ideally we add a get-by-slug endpoint.
    // For now, let's try finding it via list if we can't find by slug directly
    // Or, assume route param IS id if we changed the link? No, Shopify uses slug.
    
    // Actually, backend ProductController show($id) uses ID. We need show($slug) or a filter.
    // Let's implement a quick store-front fetch. 
    // Wait, the Admin link I just made is `/products/${product.slug}`. 
    // We need an endpoint for this.
    try {
        // Temporary: Fetch all products and find match (inefficient but works for prototype)
        // Better: Update backend show() to accept slug.
        const response = await api.get('/products') // This is paginated, risky.
        // Let's rely on backend update next step. For now, assume we have a way.
        // Let's try to query by slug if possible.
        // Actually, let's just make the backend handle string ID as slug.
        
        // For development speed, I will use ID in the link for now if I can't update backend yet.
        // BUT user asked for "Shopify like".
        
        // Let's assume we will pass ID for now in the admin link? 
        // No, I wrote slug in the template relative to user request.
        // I will update backend to support slug lookup in a moment.
        
        const slug = route.params.slug
        // Call special endpoint or just list?
        // Let's try to find it in the list for now to avoid 404 if backend isn't ready.
        const res = await api.get('/products')
        const all = res.data.data.data
        product.value = all.find(p => p.slug === slug)
        
        if (product.value) {
            selectedVariant.value = product.value.variants[0]
        }
    } catch (e) {
        console.error(e)
    } finally {
        loading.value = false
    }
})

const addToCart = () => {
    alert(`Added ${quantity.value} - ${product.value.title} to cart!`)
}
</script>

<template>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div v-if="loading" class="text-center py-20">Loading...</div>
        <div v-else-if="!product" class="text-center py-20">Product not found.</div>
        
        <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Image Gallery -->
            <div class="space-y-4">
                <div class="aspect-square rounded-lg overflow-hidden bg-gray-100 border border-gray-200">
                    <img v-if="product.images && product.images.length" :src="product.images[0].image_path" class="w-full h-full object-cover">
                    <div v-else class="w-full h-full flex items-center justify-center text-gray-400">No Image</div>
                </div>
            </div>
            
            <!-- Details -->
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ product.title }}</h1>
                <div class="mt-4 flex items-center justify-between">
                    <p class="text-2xl font-medium text-gray-900">${{ selectedVariant?.price }}</p>
                </div>
                
                <div class="mt-8">
                     <div class="prose prose-sm text-gray-500" v-html="product.description || 'No description'"></div>
                </div>
                
                <div class="mt-8 border-t border-gray-200 pt-8">
                    <div class="flex items-center gap-4 mb-6">
                         <div class="w-24">
                              <label class="sr-only">Quantity</label>
                              <input v-model="quantity" type="number" min="1" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 text-center">
                         </div>
                         <BaseButton class="flex-1 justify-center py-3" @click="addToCart">Add to Cart</BaseButton>
                    </div>
                     <BaseButton variant="secondary" class="w-full justify-center py-3">Buy it now</BaseButton>
                </div>
                
                 <div class="mt-8 border-t border-gray-200 pt-8" v-if="product.tags">
                    <p class="text-sm text-gray-500">Tags: {{ product.tags }}</p>
                 </div>
            </div>
        </div>
    </div>
</template>
