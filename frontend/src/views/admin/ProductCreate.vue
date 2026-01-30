<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import api from '@/composables/useApi'
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const isEditMode = computed(() => !!route.params.id)

// ========== PRODUCT STATE ==========
const product = ref({
  title: '',
  description: '',
  status: 'active',
  vendor: '',
  category: '',
  tags: '',
  images: [], 
  variants: []
})

// ========== VARIANT MODE ==========
const hasVariants = ref(false)

// Options for variant generation (e.g., Size, Color)
const options = ref([
    { name: 'Size', values: [] }
])
const variantInput = ref({ 0: '', 1: '', 2: '' })

// ========== SIMPLE PRODUCT PRICING/INVENTORY ==========
const simpleProduct = ref({
    price: '',
    compareAtPrice: '',
    costPerItem: '',
    sku: '',
    barcode: '',
    quantity: 0,
    trackQuantity: true
})

// ========== IMAGE HANDLING ==========
const imageUrls = computed(() => {
    return product.value.images.map(img => {
        if (typeof img === 'string') return img
        return img.image_path || ''
    })
})

const handleImageUpload = async (event) => {
    const files = Array.from(event.target.files)
    if (files.length === 0) return

    loading.value = true
    try {
        for (const file of files) {
            const formData = new FormData()
            formData.append('file', file)
            
            const response = await api.post('/upload', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            })
            
            if (response.data?.data?.url) {
                product.value.images.push({ image_path: response.data.data.url })
            }
        }
    } catch (e) {
        console.error('Upload failed', e)
        alert('Failed to upload image')
    } finally {
        loading.value = false
        event.target.value = ''
    }
}

const removeImage = (index) => {
    product.value.images.splice(index, 1)
}

// ========== VARIANT IMAGE UPLOAD ==========
const handleVariantImageUpload = async (event, variant) => {
    const file = event.target.files[0]
    if (!file) return
    
    const formData = new FormData()
    formData.append('file', file)  // Backend expects 'file' not 'image'
    
    try {
        const response = await api.post('/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        
        // Backend returns { url, path } - use url for display
        const imagePath = response.data.data?.url || response.data.url || response.data.path
        
        // Logic to remove OLD image if it exists and is unused
        const oldUrl = variant.image_url || variant.image?.image_path
        if (oldUrl) {
            // Check if any OTHER variant uses this image
            const isUsedByOthers = product.value.variants.some(v => 
                v !== variant && (v.image_url === oldUrl || v.image?.image_path === oldUrl)
            )
            
            // If not used by others, remove it from the main images list
            if (!isUsedByOthers) {
                product.value.images = product.value.images.filter(img => img.image_path !== oldUrl)
            }
        }

        variant.image_url = imagePath
        
        // Clear explicit image_id and image object to force backend to resolve by path
        // This handles the case where we replace an existing image
        variant.image_id = null
        variant.image = null
        
        // Also add to product images for backend linking
        const newImage = { image_path: imagePath }
        product.value.images.push(newImage)
        
        // Note: image_id will be resolved by backend using image_path matching
    } catch (error) {
        console.error('Failed to upload variant image:', error)
        alert('Failed to upload image: ' + (error.response?.data?.message || error.message))
    }
}

const removeVariantImage = (variant) => {
    // Logic to remove image from gallery if unused by others
    const oldUrl = variant.image_url || variant.image?.image_path
    if (oldUrl) {
        const isUsedByOthers = product.value.variants.some(v => 
            v !== variant && (v.image_url === oldUrl || v.image?.image_path === oldUrl)
        )
        if (!isUsedByOthers) {
            product.value.images = product.value.images.filter(img => img.image_path !== oldUrl)
        }
    }

    variant.image_url = null
    variant.image_id = null
    variant.image = null
}

// ========== OPTIONS & VARIANT GENERATION ==========
const addOption = () => {
    if (options.value.length < 3) {
        options.value.push({ name: '', values: [] })
    }
}

const removeOption = (index) => {
    options.value.splice(index, 1)
    generateVariants()
}

const addOptionValue = (index) => {
    const val = variantInput.value[index]?.trim()
    if (val && !options.value[index].values.includes(val)) {
        options.value[index].values.push(val)
        variantInput.value[index] = ''
        generateVariants()
    }
}

const removeOptionValue = (optIndex, valIndex) => {
    options.value[optIndex].values.splice(valIndex, 1)
    generateVariants()
}

const generateVariants = () => {
    // Only generate if in variant mode and have options with values
    if (!hasVariants.value) return
    
    const optionsWithValues = options.value.filter(o => o.values.length > 0)
    if (optionsWithValues.length === 0) {
        product.value.variants = []
        return
    }

    // Cartesian product of all option values
    const combine = (opts) => {
        if (opts.length === 0) return [[]]
        const [first, ...rest] = opts
        const restCombos = combine(rest)
        const result = []
        for (const val of first.values) {
            for (const combo of restCombos) {
                result.push([val, ...combo])
            }
        }
        return result
    }

    const combinations = combine(optionsWithValues)
    
    // Preserve existing variant data if titles match
    const existingVariantMap = {}
    product.value.variants.forEach(v => {
        existingVariantMap[v.title] = v
    })
    
    product.value.variants = combinations.map(combo => {
        const title = combo.join(' / ')
        const existing = existingVariantMap[title]
        return {
            id: existing?.id || null,
            title: title,
            option1: combo[0] || null,
            option2: combo[1] || null,
            option3: combo[2] || null,
            price: existing?.price || '',
            sku: existing?.sku || '',
            inventory: existing?.inventory || 0,
            image_id: existing?.image_id || null,
            image_url: existing?.image_url || null,
            image: existing?.image || null
        }
    })
}

const clearVariants = () => {
    hasVariants.value = false
    product.value.variants = []
    options.value = [{ name: 'Size', values: [] }]
    variantInput.value = { 0: '', 1: '', 2: '' }
}

// ========== SAVE PRODUCT ==========
const saveProduct = async () => {
    // Validate title
    if (!product.value.title.trim()) {
        alert('Please enter a product title')
        return
    }

    // If variant mode, ensure we have variants
    if (hasVariants.value) {
        // Auto-add any pending input values
        let optionsUpdated = false
        options.value.forEach((opt, idx) => {
            const pendingVal = variantInput.value[idx]?.trim()
            if (pendingVal && !opt.values.includes(pendingVal)) {
                opt.values.push(pendingVal)
                optionsUpdated = true
            }
            variantInput.value[idx] = ''
        })
        
        // Only regenerate variants if we added new option values
        if (optionsUpdated) {
            generateVariants()
        }
        
        if (product.value.variants.length === 0) {
            alert('Please add at least one option value to generate variants.')
            return
        }
        
        // Validate all variants have price
        const missingPrice = product.value.variants.find(v => !v.price && v.price !== 0)
        if (missingPrice) {
            alert(`Please enter a price for variant: ${missingPrice.title}`)
            return
        }
    } else {
        // Simple product - validate price
        if (!simpleProduct.value.price && simpleProduct.value.price !== 0) {
            alert('Please enter a price')
            return
        }
    }

    loading.value = true
    try {
        // Debug: Log variant data before save
        console.log('=== SAVE DEBUG ===')
        console.log('Variants before mapping:', JSON.stringify(product.value.variants, null, 2))
        
        const payload = {
            title: product.value.title,
            description: product.value.description,
            status: product.value.status,
            vendor: product.value.vendor,
            category: product.value.category,
            tags: product.value.tags,
            images: product.value.images,
            options: hasVariants.value ? options.value : null,
            variants: hasVariants.value 
                ? product.value.variants.map(v => {
                    // Build image_path from multiple sources - priority: new upload > existing image
                    const imagePath = v.image_url || v.image?.image_path || null
                    // Also preserve existing image_id if no new upload
                    const imageId = v.image?.id || v.image_id || null
                    
                    const variantPayload = {
                        id: v.id || null,
                        title: v.title,
                        price: parseFloat(v.price) || 0,
                        sku: v.sku || null,
                        inventory_quantity: parseInt(v.inventory) || 0,
                        option1: v.option1,
                        option2: v.option2,
                        option3: v.option3,
                        // Send existing image_id if available (will be overridden by backend if image_path resolves)
                        image_id: imageId,
                        // Always send image_path for backend resolution
                        image_path: imagePath
                    }
                    return variantPayload
                })
                : [{
                    title: 'Default Title',
                    price: parseFloat(simpleProduct.value.price) || 0,
                    compare_at_price: parseFloat(simpleProduct.value.compareAtPrice) || null,
                    cost_per_item: parseFloat(simpleProduct.value.costPerItem) || null,
                    sku: simpleProduct.value.sku || null,
                    barcode: simpleProduct.value.barcode || null,
                    inventory_quantity: parseInt(simpleProduct.value.quantity) || 0,
                    option1: 'Default Title'
                }]
        }
        
        console.log('Full payload:', JSON.stringify(payload, null, 2))

        if (isEditMode.value) {
            await api.put(`/products/${route.params.id}`, payload)
        } else {
            await api.post('/products', payload)
        }
        router.push('/admin/products')
    } catch (error) {
        console.error('Failed to save', error)
        const msg = error.response?.data?.message || error.response?.data?.errors || error.message
        alert('Failed to save product: ' + JSON.stringify(msg))
    } finally {
        loading.value = false
    }
}

// ========== FETCH FOR EDIT MODE ==========
const fetchProduct = async () => {
    loading.value = true
    try {
        const response = await api.get(`/products/${route.params.id}`)
        const data = response.data.product || response.data.data

        // Basic info
        product.value.title = data.title || ''
        product.value.description = data.description || ''
        product.value.status = data.status || 'active'
        product.value.vendor = data.vendor || ''
        product.value.category = data.category?.name || ''
        product.value.tags = data.tags || ''
        
        // Images
        if (data.images?.length) {
            product.value.images = data.images.map(img => ({
                id: img.id,
                image_path: img.image_path
            }))
        }
        
        // Variants
        if (data.variants?.length > 0) {
            const firstVar = data.variants[0]
            
            // Check if simple product (single "Default Title" variant)
            if (data.variants.length === 1 && firstVar.title === 'Default Title') {
                hasVariants.value = false
                simpleProduct.value = {
                    price: firstVar.price || '',
                    compareAtPrice: firstVar.compare_at_price || '',
                    costPerItem: firstVar.cost_per_item || '',
                    sku: firstVar.sku || '',
                    barcode: firstVar.barcode || '',
                    quantity: firstVar.inventory?.quantity || 0,
                    trackQuantity: true
                }
            } else {
                // Multi-variant product
                hasVariants.value = true
                
                // Load options
                if (data.options) {
                    options.value = typeof data.options === 'string' 
                        ? JSON.parse(data.options) 
                        : data.options
                } else {
                    // Reconstruct options from variants
                    const opt1Vals = new Set()
                    const opt2Vals = new Set()
                    const opt3Vals = new Set()
                    data.variants.forEach(v => {
                        if (v.option1) opt1Vals.add(v.option1)
                        if (v.option2) opt2Vals.add(v.option2)
                        if (v.option3) opt3Vals.add(v.option3)
                    })
                    options.value = []
                    if (opt1Vals.size) options.value.push({ name: 'Option 1', values: [...opt1Vals] })
                    if (opt2Vals.size) options.value.push({ name: 'Option 2', values: [...opt2Vals] })
                    if (opt3Vals.size) options.value.push({ name: 'Option 3', values: [...opt3Vals] })
                    if (options.value.length === 0) options.value.push({ name: 'Size', values: [] })
                }

                // Load variants
                product.value.variants = data.variants.map(v => ({
                    id: v.id,
                    title: v.title,
                    price: v.price || '',
                    sku: v.sku || '',
                    inventory: v.inventory?.quantity || 0,
                    option1: v.option1,
                    option2: v.option2,
                    option3: v.option3,
                    image_id: v.image_id || null,
                    image_url: v.image?.image_path || null,
                    image: v.image || null
                }))
            }
        }

    } catch (e) {
        console.error('Failed to fetch product', e)
        alert('Failed to load product details')
        router.push('/admin/products')
    } finally {
        loading.value = false
    }
}

onMounted(async () => {
    if (isEditMode.value) {
        await fetchProduct()
    }
})

// ========== HELPERS ==========
const getPlaceholder = (name) => {
    if (!name) return 'Add value'
    const n = name.toLowerCase()
    if (n === 'size') return 'e.g. S, M, L, XL'
    if (n === 'color') return 'e.g. Red, Blue, Green'
    return `e.g. ${name} value`
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 min-h-screen bg-gray-50 pb-20">
    <div class="max-w-5xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <BaseButton variant="secondary" @click="router.back()">
                    <span class="text-gray-500">←</span>
                </BaseButton>
                <h1 class="text-xl font-bold leading-6 text-gray-900">
                    {{ isEditMode ? 'Edit Product' : 'Add Product' }}
                </h1>
            </div>
            <div class="flex items-center gap-3">
                <BaseButton variant="secondary" @click="router.back()">Discard</BaseButton>
                <BaseButton variant="primary" :disabled="loading" @click="saveProduct">
                    {{ loading ? 'Saving...' : 'Save' }}
                </BaseButton>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Title & Description -->
                <BaseCard>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Title</label>
                            <div class="mt-2">
                                <input v-model="product.title" type="text" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                    placeholder="Short sleeve t-shirt" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                            <div class="mt-2">
                                <textarea v-model="product.description" rows="6" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                            </div>
                        </div>
                    </div>
                </BaseCard>

                <!-- Media -->
                <BaseCard>
                    <template #header>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Media</h3>
                    </template>
                    <div class="mt-2">
                        <div v-if="imageUrls.length > 0" class="grid grid-cols-3 gap-4 mb-4">
                            <div v-for="(url, index) in imageUrls" :key="index" 
                                class="relative group aspect-square rounded-lg border border-gray-200 overflow-hidden bg-gray-100">
                                <img :src="url" class="object-cover w-full h-full" />
                                <button @click="removeImage(index)" 
                                    class="absolute top-1 right-1 bg-white rounded-full p-1 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
                                    <XMarkIcon class="h-4 w-4 text-gray-500" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 hover:bg-gray-50 transition-colors cursor-pointer relative">
                            <div class="text-center">
                                <PhotoIcon class="mx-auto h-12 w-12 text-gray-300" aria-hidden="true" />
                                <div class="mt-4 flex text-sm leading-6 text-gray-600 justify-center">
                                    <label class="relative cursor-pointer rounded-md bg-transparent font-semibold text-indigo-600 focus-within:outline-none hover:text-indigo-500">
                                        <span>Upload new</span>
                                        <input type="file" multiple class="sr-only" @change="handleImageUpload" accept="image/*" />
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </BaseCard>

                <!-- Pricing (Simple Product Only) -->
                <BaseCard v-if="!hasVariants">
                    <template #header>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Pricing</h3>
                    </template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Price</label>
                            <div class="relative mt-2 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input v-model="simpleProduct.price" type="number" step="0.01" 
                                    class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                    placeholder="0.00" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Compare-at price</label>
                            <div class="relative mt-2 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input v-model="simpleProduct.compareAtPrice" type="number" step="0.01" 
                                    class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                    placeholder="0.00" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Cost per item</label>
                            <div class="relative mt-2 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-gray-500 sm:text-sm">$</span>
                                </div>
                                <input v-model="simpleProduct.costPerItem" type="number" step="0.01" 
                                    class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                    placeholder="0.00" />
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Customers won't see this</p>
                        </div>
                    </div>
                </BaseCard>

                <!-- Inventory (Simple Product Only) -->
                <BaseCard v-if="!hasVariants">
                    <template #header>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Inventory</h3>
                    </template>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">SKU (Stock Keeping Unit)</label>
                            <input v-model="simpleProduct.sku" type="text" 
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Barcode</label>
                            <input v-model="simpleProduct.barcode" type="text" 
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Quantity</label>
                            <input v-model="simpleProduct.quantity" type="number" 
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                        </div>
                        <div class="flex items-center gap-2 pt-6">
                            <input v-model="simpleProduct.trackQuantity" type="checkbox" 
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                            <label class="text-sm font-medium leading-6 text-gray-900">Track quantity</label>
                        </div>
                    </div>
                </BaseCard>

                <!-- Variants Section -->
                <BaseCard>
                    <template #header>
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-semibold leading-6 text-gray-900">Variants</h3>
                            <div v-if="!hasVariants">
                                <span class="text-sm text-indigo-600 cursor-pointer hover:underline" 
                                    @click="hasVariants = true">
                                    + Add options like size or color
                                </span>
                            </div>
                            <div v-else>
                                <span class="text-xs text-red-600 cursor-pointer hover:underline" 
                                    @click="clearVariants">
                                    Cancel variants
                                </span>
                            </div>
                        </div>
                    </template>
                    
                    <div v-if="hasVariants" class="space-y-6">
                        <!-- Option Inputs -->
                        <div v-for="(opt, idx) in options" :key="idx" class="p-4 border rounded-md bg-gray-50">
                            <div class="mb-2 flex items-center justify-between">
                                <label class="block text-sm font-medium text-gray-700">Option name</label>
                                <button v-if="options.length > 1" @click="removeOption(idx)" 
                                    class="text-xs text-red-600 hover:text-red-800">Remove</button>
                            </div>
                            <input v-model="opt.name" type="text" 
                                class="mb-3 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                                placeholder="e.g. Size, Color, Material">

                            <!-- Values Display -->
                            <div class="flex gap-2 mb-2 flex-wrap">
                                <span v-for="(val, vIdx) in opt.values" :key="vIdx" 
                                    class="inline-flex items-center rounded-md bg-indigo-100 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                    {{ val }}
                                    <button @click="removeOptionValue(idx, vIdx)" class="ml-1 text-indigo-400 hover:text-indigo-600">×</button>
                                </span>
                            </div>
                            
                            <!-- Add Value Input -->
                            <div class="flex gap-2">
                                <input v-model="variantInput[idx]" 
                                    @keydown.enter.prevent="addOptionValue(idx)" 
                                    type="text" 
                                    :placeholder="getPlaceholder(opt.name)" 
                                    class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                <button @click="addOptionValue(idx)" 
                                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                    Add
                                </button>
                            </div>
                        </div>
                        
                        <button v-if="options.length < 3" @click="addOption" 
                            class="text-sm text-indigo-600 font-medium hover:underline">
                            + Add another option
                        </button>

                        <!-- Generated Variants Table -->
                        <div v-if="product.variants.length > 0" class="overflow-x-auto border rounded-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Variant</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Image</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Price</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Inventory</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="v in product.variants" :key="v.title">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ v.title }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <!-- Image Preview -->
                                                <div v-if="v.image_url || v.image?.image_path" 
                                                    class="w-10 h-10 rounded border overflow-hidden bg-gray-100 flex-shrink-0">
                                                    <img :src="v.image_url || v.image?.image_path" class="w-full h-full object-cover" />
                                                </div>
                                                <div v-else class="w-10 h-10 rounded border border-dashed border-gray-300 flex items-center justify-center text-gray-400 flex-shrink-0">
                                                    <PhotoIcon class="w-5 h-5" />
                                                </div>
                                                <!-- Upload Button -->
                                                <label class="cursor-pointer text-xs text-indigo-600 hover:text-indigo-500 font-medium whitespace-nowrap">
                                                    {{ v.image_url || v.image?.image_path ? 'Change' : 'Add' }}
                                                    <input type="file" class="sr-only" accept="image/*" 
                                                        @change="handleVariantImageUpload($event, v)" />
                                                </label>
                                                <button v-if="v.image_url || v.image?.image_path" 
                                                    @click="removeVariantImage(v)"
                                                    class="text-xs text-red-500 hover:text-red-700">×</button>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="relative">
                                                <span class="absolute left-2 top-1.5 text-gray-400 text-sm">$</span>
                                                <input v-model="v.price" type="number" step="0.01"
                                                    class="w-28 pl-6 rounded border-gray-300 py-1.5 text-sm focus:ring-indigo-500" 
                                                    placeholder="0.00" />
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <input v-model="v.sku" type="text" 
                                                class="w-28 rounded border-gray-300 py-1.5 text-sm focus:ring-indigo-500" />
                                        </td>
                                        <td class="px-4 py-3">
                                            <input v-model="v.inventory" type="number" 
                                                class="w-20 rounded border-gray-300 py-1.5 text-sm focus:ring-indigo-500" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div v-else class="text-sm text-gray-500 text-center py-4">
                            Add option values above to generate variants
                        </div>
                    </div>
                    <div v-else>
                        <p class="text-sm text-gray-500">This product has no variants. Add options to create variants.</p>
                    </div>
                </BaseCard>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Status -->
                <BaseCard>
                    <template #header>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Status</h3>
                    </template>
                    <select v-model="product.status" 
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <option value="active">Active</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                </BaseCard>

                <!-- Product Organization -->
                <BaseCard>
                    <template #header>
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Product organization</h3>
                    </template>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                            <input v-model="product.vendor" type="text" 
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Category</label>
                            <input v-model="product.category" type="text" 
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium leading-6 text-gray-900">Tags</label>
                            <input v-model="product.tags" type="text" 
                                class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                placeholder="Vintage, Cotton, Summer" />
                        </div>
                    </div>
                </BaseCard>
            </div>
        </div>
    </div>
  </div>
</template>
