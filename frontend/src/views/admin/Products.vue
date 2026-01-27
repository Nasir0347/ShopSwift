<script setup>
import { ref, computed, onMounted } from 'vue'
import { 
  MagnifyingGlassIcon, 
  FunnelIcon, 
  ArrowsUpDownIcon, 
  ArrowDownTrayIcon, 
  ArrowUpTrayIcon,
  EyeIcon,
  PhotoIcon
} from '@heroicons/vue/24/outline'
import BaseButton from '@/components/ui/BaseButton.vue'
import api from '@/composables/useApi' 
import { useRouter } from 'vue-router'

const router = useRouter()
const products = ref([])
const selectedProducts = ref([])
const searchQuery = ref('')
const currentTab = ref('All')
const tabs = ['All', 'Active', 'Draft', 'Archived']
const showFilters = ref(false)
const sortOrder = ref('desc') // desc or asc
const sortBy = ref('created_at') // created_at or title
const filterVendor = ref('')
const filterCategory = ref('')

// File Input Ref
const fileInput = ref(null)

onMounted(async () => {
    await fetchProducts()
})

const fetchProducts = async () => {
    try {
        const response = await api.get('/products')
        const productData = response.data.data.data || []
        // Map backend data to frontend model
        products.value = productData.map(p => ({
            id: p.id,
            title: p.title,
            slug: p.slug, // Added Slug
            status: p.status, 
            inventory: p.variants?.reduce((acc, v) => acc + (v.inventory_quantity || (v.inventory?.quantity || 0)), 0) || 0, // Robust inventory sum
            variantsCount: p.variants?.length || 0, // Added variants count
            vendor: p.vendor || 'ShopSwift',
            category: p.category?.name || 'Uncategorized',
            image: p.images?.[0]?.image_path || null
        }))
    } catch (e) {
        console.error('Failed to fetch products', e)
    }
}

const handleExport = async () => {
    try {
        const response = await api.get('/products/export', { responseType: 'blob' })
        const url = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = url
        link.setAttribute('download', `products_export_${new Date().toISOString().slice(0,10)}.csv`)
        document.body.appendChild(link)
        link.click()
        link.remove()
    } catch (e) {
        console.error('Export failed', e)
        alert('Export failed')
    }
}

const triggerImport = () => {
    fileInput.value.click()
}

const handleImportClient = async (event) => {
    const file = event.target.files[0]
    if (!file) return
    
    const formData = new FormData()
    formData.append('file', file)
    
    try {
        await api.post('/products/import', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })
        alert('Import successful')
        await fetchProducts() // Refresh list
    } catch (e) {
        console.error('Import failed', e)
        alert('Import failed: ' + (e.response?.data?.message || e.message))
    } finally {
        event.target.value = '' // Reset input
    }
}

const filteredProducts = computed(() => {
  let result = products.value

  // 1. Tab Filter
  if (currentTab.value !== 'All') {
    result = result.filter(p => p.status.toLowerCase() === currentTab.value.toLowerCase())
  }

  // 2. Search Filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(p => 
      p.title.toLowerCase().includes(q) || 
      p.vendor?.toLowerCase().includes(q) ||
      p.category?.toLowerCase().includes(q)
    )
  }

  // 3. Custom Filters
  if (filterVendor.value) {
      result = result.filter(p => p.vendor === filterVendor.value)
  }
  if (filterCategory.value) {
      result = result.filter(p => p.category === filterCategory.value)
  }

  // 4. Sorting
  return result.sort((a, b) => {
      let valA = a[sortBy.value]
      let valB = b[sortBy.value]
      
      if (sortBy.value === 'title') {
          valA = valA.toLowerCase()
          valB = valB.toLowerCase()
      }
      
      if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1
      if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1
      return 0
  })
})

const vendors = computed(() => [...new Set(products.value.map(p => p.vendor).filter(Boolean))])
const categories = computed(() => [...new Set(products.value.map(p => p.category).filter(Boolean))])

const toggleSort = () => {
    if (sortBy.value === 'created_at') {
        if (sortOrder.value === 'desc') sortOrder.value = 'asc'
        else {
            sortBy.value = 'title'
            sortOrder.value = 'asc'
        }
    } else {
        if (sortOrder.value === 'asc') sortOrder.value = 'desc'
        else {
            sortBy.value = 'created_at'
            sortOrder.value = 'desc'
        }
    }
}

const toggleSelection = (id) => {
  if (selectedProducts.value.includes(id)) {
    selectedProducts.value = selectedProducts.value.filter(i => i !== id)
  } else {
    selectedProducts.value.push(id)
  }
}

const toggleAll = () => {
  if (selectedProducts.value.length === filteredProducts.value.length && filteredProducts.value.length > 0) {
    selectedProducts.value = []
  } else {
    selectedProducts.value = filteredProducts.value.map(p => p.id)
  }
}

const bulkUpdateStatus = async (status) => {
    if (!selectedProducts.value.length) return
    try {
        await Promise.all(selectedProducts.value.map(id => api.put(`/products/${id}`, { status })))
        products.value.forEach(p => {
            if (selectedProducts.value.includes(p.id)) p.status = status
        })
        selectedProducts.value = []
    } catch (error) {
        console.error('Bulk update failed', error)
    }
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-xl font-bold leading-6 text-gray-900">Products</h1>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex gap-2">
         <!-- Hidden File Input -->
         <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleImportClient" />
         
         <BaseButton variant="secondary" @click="handleExport">
            <ArrowUpTrayIcon class="h-4 w-4 mr-2" />
            Export
         </BaseButton>
         <BaseButton variant="secondary" @click="triggerImport">
            <ArrowDownTrayIcon class="h-4 w-4 mr-2" />
            Import
         </BaseButton>
        <BaseButton variant="primary" to="/admin/products/create">Add product</BaseButton>
      </div>
    </div>

    <!-- Main Content Card -->
    <div class="mt-4 bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-lg">
      
      <!-- Tabs -->
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-6 px-4" aria-label="Tabs">
          <button 
            v-for="tab in tabs" 
            :key="tab"
            @click="currentTab = tab"
            :class="[
              currentTab === tab ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
            ]"
          >
            {{ tab }}
          </button>
        </nav>
      </div>

      <!-- Bulk Actions Bar -->
      <div v-if="selectedProducts.length > 0" class="bg-gray-50 border-b border-gray-200 p-4 flex items-center justify-between">
          <span class="text-sm text-gray-700 font-medium">{{ selectedProducts.length }} selected</span>
          <div class="flex gap-2">
              <BaseButton variant="secondary" size="sm" @click="bulkUpdateStatus('active')">Set as Active</BaseButton>
              <BaseButton variant="secondary" size="sm" @click="bulkUpdateStatus('draft')">Set as Draft</BaseButton>
              <BaseButton variant="secondary" size="sm" @click="bulkUpdateStatus('archived')">Archive</BaseButton>
          </div>
      </div>

      <!-- Search & Filters -->
      <div v-else class="p-4 border-b border-gray-200 flex flex-col gap-4">
        <div class="flex gap-4">
            <div class="relative flex-grow max-w-md">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            </div>
            <input 
                v-model="searchQuery"
                type="text" 
                class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                placeholder="Filter products" 
            />
            </div>
            <div class="relative">
                <BaseButton variant="secondary" class="!px-3" @click="showFilters = !showFilters" :class="{'bg-gray-100 ring-gray-400': showFilters}">
                    <FunnelIcon class="h-5 w-5 text-gray-500" />
                </BaseButton>
                <!-- Filter Popover -->
                <div v-if="showFilters" class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Vendor</label>
                        <select v-model="filterVendor" class="mt-1 block w-full rounded-md border-gray-300 py-1.5 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm shadow-sm ring-1 ring-inset ring-gray-300 cursor-pointer">
                            <option value="">All</option>
                            <option v-for="v in vendors" :key="v" :value="v">{{ v }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select v-model="filterCategory" class="mt-1 block w-full rounded-md border-gray-300 py-1.5 text-base focus:border-indigo-500 focus:outline-none focus:ring-indigo-500 sm:text-sm shadow-sm ring-1 ring-inset ring-gray-300 cursor-pointer">
                            <option value="">All</option>
                            <option v-for="c in categories" :key="c" :value="c">{{ c }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end pt-2">
                        <span class="text-xs text-indigo-600 cursor-pointer hover:underline" @click="filterVendor = ''; filterCategory = ''">Clear all</span>
                    </div>
                </div>
            </div>
            <BaseButton variant="secondary" class="!px-3" @click="toggleSort">
                <div class="flex items-center gap-1">
                    <ArrowsUpDownIcon class="h-5 w-5 text-gray-500" />
                    <span class="text-xs text-gray-500 w-12 text-left">{{ sortBy === 'title' ? 'Title' : 'Date' }} {{ sortOrder === 'asc' ? '↑' : '↓' }}</span>
                </div>
            </BaseButton>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-300 text-left">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6 w-10">
                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" 
                       :checked="selectedProducts.length > 0 && selectedProducts.length === filteredProducts.length"
                       @change="toggleAll" />
              </th>
              <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-0 w-16"></th> <!-- Image Col -->
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Product</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Inventory</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Category</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Vendor</th>
              <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                <span class="sr-only">Actions</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="product in filteredProducts" :key="product.id" class="hover:bg-gray-50 transition-colors group">
              <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" 
                       :checked="selectedProducts.includes(product.id)"
                       @change="toggleSelection(product.id)" />
              </td>
              <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-0">
                <div class="h-10 w-10 flex-shrink-0 cursor-pointer" @click="router.push(`/admin/products/${product.id}`)">
                    <img v-if="product.image" :src="product.image" class="h-10 w-10 rounded-md object-cover border border-gray-200" />
                    <div v-else class="h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center border border-gray-200 overflow-hidden">
                        <PhotoIcon class="h-5 w-5 text-gray-400" />
                    </div>
                </div>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900 group-hover:text-indigo-600 cursor-pointer" @click="router.push(`/admin/products/${product.id}`)">
                  {{ product.title }}
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                <span :class="[
                    product.status === 'active' ? 'bg-green-100 text-green-800' : 
                    product.status === 'draft' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800',
                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ring-black/5 capitalize'
                ]">
                    {{ product.status }}
                </span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-600">
                  <span :class="{'text-red-600 font-medium': product.inventory <= 0}">
                      {{ product.inventory }} in stock
                  </span>
                  <span v-if="product.variantsCount > 1" class="block text-xs text-gray-400 font-normal">
                      for {{ product.variantsCount }} variants
                  </span>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ product.category }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ product.vendor }}</td>
              <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                 <!-- Preview / Eye Icon -->
                 <a 
                    v-if="product.status === 'active'"
                    :href="`/products/${product.slug}`" 
                    target="_blank"
                    class="text-gray-400 hover:text-indigo-600 transition-colors inline-block" 
                    title="View on Online Store"
                 >
                    <EyeIcon class="h-5 w-5" />
                 </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      
        <!-- Footer / Pagination -->
        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-b-lg">
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <div>
                <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">{{ filteredProducts.length }}</span> of <span class="font-medium">{{ products.length }}</span> results</p>
                </div>
            </div>
        </div>

    </div>
  </div>
</template>
