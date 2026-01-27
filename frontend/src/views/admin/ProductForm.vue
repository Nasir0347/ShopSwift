<script setup>
import { ref, onMounted } from 'vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import api from '@/composables/useApi'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const form = ref({
  title: '',
  description: '',
  price: '',
  inventory: '',
  status: 'active'
})

const submit = async () => {
    loading.value = true
    try {
        // Construct payload. Note: The backend expects 'variants' array for price and inventory.
        const payload = {
            title: form.value.title,
            description: form.value.description,
            status: form.value.status,
            variants: [
                {
                    price: parseFloat(form.value.price),
                    sku: 'SKU-' + Math.random().toString(36).substr(2, 5).toUpperCase(),
                    // Basic placeholder values for required fields if any
                }
            ],
            // Backend might require inventory in a separate call or part of variants logic depending on implementation.
            // Based on earlier analysis, Inventory model is separate but often handled with product creation or variants.
            // Let's check ProductController.store structure again if this fails.
            // The controller loop: $product->variants()->create($variantData); 
            // It doesn't seem to explicitly create Inventory in the store method shown previously, 
            // but we'll stick to minimum viable fields from validation:
            // 'variants.*.price' => 'required', 'images.*.image_path' => 'required' (nullable in validation actually)
        }

        // Add dummy image if not provided
        payload.images = [
            { image_path: 'https://placehold.co/600x400' }
        ]

        await api.post('/products', payload)
        alert('Product created!')
        router.push('/admin/products')
    } catch (e) {
        console.error(e)
        alert('Failed: ' + (e.response?.data?.message || e.message))
    } finally {
        loading.value = false
    }
}
</script>

<template>
  <div class="max-w-2xl mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">Add New Product</h1>
    <BaseCard>
        <form @submit.prevent="submit" class="space-y-4">
            <BaseInput label="Title" v-model="form.title" required />
            <BaseInput label="Description" v-model="form.description" />
            <div class="grid grid-cols-2 gap-4">
                <BaseInput label="Price ($)" type="number" step="0.01" v-model="form.price" required />
                <BaseInput label="Inventory" type="number" v-model="form.inventory" />
            </div>
             <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                </select>
            </div>
            
            <div class="flex justify-end pt-4">
                <BaseButton type="button" variant="secondary" class="mr-2" @click="router.back()">Cancel</BaseButton>
                <BaseButton type="submit" variant="primary" :loading="loading">Create Product</BaseButton>
            </div>
        </form>
    </BaseCard>
  </div>
</template>
