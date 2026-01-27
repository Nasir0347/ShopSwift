<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import api from '@/composables/useApi'
import { PhotoIcon, XMarkIcon } from '@heroicons/vue/24/outline'

const router = useRouter()
const route = useRoute()
const loading = ref(false)
const isEditMode = computed(() => !!route.params.id)

const product = ref({
  title: '',
  description: '',
  status: 'active',
  vendor: '',
  category: '',
  collections: '',
  tags: '',
  images: [], 
  variants: []
})

// Variant Generation State
const hasVariants = ref(false)
const options = ref([
    { name: 'Size', values: [] },
    { name: 'Color', values: [] }
])
const variantInput = ref({ 0: '', 1: '' }) // Input models for options

// Default / Simple Product Pricing & Inventory
const simpleProduct = ref({
    price: '',
    compareAtPrice: '',
    costPerItem: '',
    sku: '',
    barcode: '',
    trackQuantity: true,
    quantity: 0
})

const imageUrls = ref([]) 

onMounted(async () => {
    if (isEditMode.value) {
        loading.value = true
        try {
            const response = await api.get(`/products/${route.params.id}`)
            const p = response.data.data
            
            product.value.title = p.title
            product.value.description = p.description
            product.value.status = p.status
            product.value.vendor = p.vendor
            product.value.category = p.category?.name // Simplified mapping
            product.value.images = p.images || []
            imageUrls.value = p.images?.map(img => img.image_path) || []

            // Check if multiple variants exist
            if (p.variants && p.variants.length > 1) {
                hasVariants.value = true
                product.value.variants = p.variants
                // Reconstruct options logic would complicate this, so we load as-is for editing simply for now
            } else if (p.variants && p.variants.length === 1) {
                // Simple product
                const v = p.variants[0]
                simpleProduct.value = {
                    price: v.price,
                    compareAtPrice: v.compare_at_price,
                    sku: v.sku,
                    quantity: v.inventory_quantity || 0,
                    trackQuantity: true
                }
            } else {
                 // No variants?
                 simpleProduct.value.price = p.price
            }
            
        } catch (error) {
            console.error('Failed to load product', error)
        } finally {
            loading.value = false
        }
    }
})

const handleImageUpload = async (event) => {
  const files = event.target.files
  if (!files) return

  for (let i = 0; i < files.length; i++) {
    const file = files[i]
    const formData = new FormData()
    formData.append('file', file)

    try {
        const response = await api.post('/upload', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        const url = response.data.data.url
        imageUrls.value.push(url)
        product.value.images.push({ image_path: url }) 
    } catch (e) {
        console.error('Upload failed', e)
    }
  }
}

const removeImage = (index) => {
  imageUrls.value.splice(index, 1)
  product.value.images.splice(index, 1)
}

const addOptionValue = (index) => {
    const val = variantInput.value[index]
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
    // Cartesian product of options
    // For now, simplify to just creating rows based on provided options
    // If Size [S, M] and Color [Red], we get S/Red, M/Red
    
    // Simple logic: if option 1 has values, iterate. If option 2 has values, nest iterate.
    const opt1 = options.value[0].values
    const opt2 = options.value[1].values
    
    const newVariants = []
    
    if (opt1.length > 0) {
        opt1.forEach(v1 => {
             if (opt2.length > 0) {
                 opt2.forEach(v2 => {
                     newVariants.push(createVariantRow(`${v1} / ${v2}`, v1, v2))
                 })
             } else {
                 newVariants.push(createVariantRow(v1, v1, null))
             }
        })
    } else if (opt2.length > 0) {
          opt2.forEach(v2 => {
             newVariants.push(createVariantRow(v2, null, v2))
          })
    }
    
    product.value.variants = newVariants
}

const createVariantRow = (title, v1, v2) => ({
    title: title,
    option1: v1,
    option2: v2,
    price: simpleProduct.value.price || '',
    sku: '',
    inventory: 0
})

const saveProduct = async () => {
  // 1. Auto-add pending variant inputs (if user typed but didn't press Enter)
  if (hasVariants.value) {
      options.value.forEach((opt, idx) => {
          const pendingVal = variantInput.value[idx]
          if (pendingVal && pendingVal.trim() !== '') {
              // Add the value programmatically
              if (!opt.values.includes(pendingVal)) {
                  opt.values.push(pendingVal)
              }
              variantInput.value[idx] = '' // Clear input
          }
      })
      // Regenerate variants with these new values
      generateVariants()
  }

  // 2. Validate Variants (now that we've added pending ones)
  if (hasVariants.value && product.value.variants.length === 0) {
      alert("Please ensure you have added option values (e.g. Size '10', Color 'Blue') to generate variants.")
      return
  }

  loading.value = true
  try {
    // Construct Payload
    const payload = {
      title: product.value.title,
      description: product.value.description,
      status: product.value.status,
      vendor: product.value.vendor,
      category: product.value.category,
      tags: product.value.tags,
      images: product.value.images,
      variants: hasVariants.value ? product.value.variants.map(v => ({
          price: v.price,
          sku: v.sku,
          inventory_quantity: v.inventory,
          option1: v.option1 || v.title, // Fallback
          option2: v.option2 
      })) : [{ 
          // Simple Product Variant
          price: simpleProduct.value.price,
          compare_at_price: simpleProduct.value.compareAtPrice,
          cost_per_item: simpleProduct.value.costPerItem,
          sku: simpleProduct.value.sku,
          barcode: simpleProduct.value.barcode,
          inventory_quantity: simpleProduct.value.quantity,
          option1: 'Default Title'
      }]
    }

    if (isEditMode.value) {
        await api.put(`/products/${route.params.id}`, payload)
    } else {
        await api.post('/products', payload)
    }
    router.push('/admin/products')
  } catch (error) {
    console.error('Failed to save', error)
    alert('Failed to save product: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const clearVariants = () => {
    hasVariants.value = false
    product.value.variants = []
    options.value.forEach(o => o.values = [])
}

const getPlaceholder = (name) => {
    const n = name.toLowerCase()
    if (n === 'size') return 'Add value (e.g. S, M, 42)'
    if (n === 'color') return 'Add value (e.g. Red, Blue)'
    if (n === 'material') return 'Add value (e.g. Cotton, Wool)'
    return `Add value (e.g. ${name} 1)`
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 min-h-screen bg-gray-50 pb-20"> <!-- Added padding bottom -->
    <div class="max-w-5xl mx-auto">
        <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-4">
            <BaseButton variant="secondary" @click="router.back()">
                <span class="text-gray-500">←</span>
            </BaseButton>
            <h1 class="text-xl font-bold leading-6 text-gray-900">{{ isEditMode ? 'Edit Product' : 'Add Product' }}</h1>
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
            
            <!-- Title & Desc -->
            <BaseCard>
            <div class="space-y-4">
                <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Title</label>
                <div class="mt-2">
                    <input v-model="product.title" type="text" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Short sleeve t-shirt" />
                </div>
                </div>
                <div>
                <label class="block text-sm font-medium leading-6 text-gray-900">Description</label>
                <div class="mt-2">
                    <textarea v-model="product.description" rows="6" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
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
                        <div v-for="(url, index) in imageUrls" :key="index" class="relative group aspect-square rounded-lg border border-gray-200 overflow-hidden bg-gray-100">
                            <img :src="url" class="object-cover w-full h-full" />
                            <button @click="removeImage(index)" class="absolute top-1 right-1 bg-white rounded-full p-1 shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
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

            <!-- Pricing (If no Variants) -->
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
                            <input v-model="simpleProduct.price" type="number" step="0.01" class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="0.00" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Compare-at price</label>
                        <div class="relative mt-2 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input v-model="simpleProduct.compareAtPrice" type="number" step="0.01" class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="0.00" />
                        </div>
                    </div>
                    <div class="col-span-2 sm:col-span-1"> <!-- Cost per item row -->
                        <label class="block text-sm font-medium leading-6 text-gray-900">Cost per item</label>
                         <div class="relative mt-2 rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input v-model="simpleProduct.costPerItem" type="number" step="0.01" class="block w-full rounded-md border-0 py-1.5 pl-7 pr-12 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="0.00" />
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Customers won’t see this</p>
                    </div>
                </div>
            </BaseCard>

            <!-- Inventory (If no Variants) -->
            <BaseCard v-if="!hasVariants">
                <template #header>
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Inventory</h3>
                </template>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium leading-6 text-gray-900">Barcode (ISBN, UPC, GTIN, etc.)</label>
                        <input v-model="simpleProduct.barcode" type="text" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">SKU</label>
                        <input v-model="simpleProduct.sku" type="text" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Quantity</label>
                        <input v-model="simpleProduct.quantity" type="number" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                    <div class="col-span-2 flex items-center gap-2">
                        <input v-model="simpleProduct.trackQuantity" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600">
                        <label class="text-sm font-medium leading-6 text-gray-900">Track quantity</label>
                    </div>
                </div>
            </BaseCard>

            <!-- Variants Logic -->
            <BaseCard>
                <template #header>
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">Variants</h3>
                        <div class="flex items-center gap-2" v-if="!hasVariants">
                             <span class="text-sm text-indigo-600 cursor-pointer hover:underline" @click="hasVariants = true">+ Add options like size or color</span>
                        </div>
                        <div class="flex items-center gap-2" v-else>
                             <span class="text-xs text-red-600 cursor-pointer hover:underline" @click="clearVariants">Cancel variants</span>
                        </div>
                    </div>
                </template>
                
                <div v-if="hasVariants" class="space-y-6">
                    <!-- Option Setup -->
                    <div v-for="(opt, idx) in options" :key="idx" class="p-4 border rounded-md bg-gray-50">
                        <label class="block text-sm font-medium text-gray-700 mb-2">{{ opt.name }}</label>
                        <div class="flex gap-2 mb-2 flex-wrap">
                            <span v-for="(val, vIdx) in opt.values" :key="vIdx" class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                {{ val }}
                                <button @click="removeOptionValue(idx, vIdx)" class="ml-1 text-gray-400 hover:text-gray-600">×</button>
                            </span>
                        </div>
                        <div class="flex gap-2">
                             <input 
                                v-model="variantInput[idx]" 
                                @keydown.enter.prevent="addOptionValue(idx)" 
                                type="text" 
                                :placeholder="getPlaceholder(opt.name)" 
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            />
                            <button @click="addOptionValue(idx)" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                +
                            </button>
                        </div>
                    </div>

                    <!-- Variant Table -->
                    <div v-if="product.variants.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <th class="py-2 text-left text-xs font-semibold text-gray-500">Variant</th>
                                    <th class="py-2 text-left text-xs font-semibold text-gray-500">Price</th>
                                    <th class="py-2 text-left text-xs font-semibold text-gray-500">SKU</th>
                                    <th class="py-2 text-left text-xs font-semibold text-gray-500">Inventory</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="v in product.variants" :key="v.title">
                                    <td class="py-2 text-sm text-gray-900">{{ v.title }}</td>
                                    <td class="py-2"><input v-model="v.price" type="number" class="w-24 rounded border-gray-300 py-1 text-sm" placeholder="0.00" /></td>
                                    <td class="py-2"><input v-model="v.sku" type="text" class="w-24 rounded border-gray-300 py-1 text-sm" /></td>
                                    <td class="py-2"><input v-model="v.inventory" type="number" class="w-20 rounded border-gray-300 py-1 text-sm" /></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div v-else>
                    <p class="text-sm text-gray-500">This product has no variants.</p>
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
                <select v-model="product.status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
            </BaseCard>

            <!-- Organization -->
            <BaseCard>
                <template #header>
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Product organization</h3>
                </template>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                        <input v-model="product.vendor" type="text" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Category</label>
                         <input v-model="product.category" type="text" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium leading-6 text-gray-900">Tags</label>
                        <input v-model="product.tags" type="text" class="mt-2 block w-full rounded-md border-0 py-1.5 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Vintage, Cotton, Summer" />
                    </div>
                </div>
            </BaseCard>
        </div>
        </div>
    </div>
  </div>
</template>
