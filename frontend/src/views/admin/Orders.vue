<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import Badge from '@/components/ui/Badge.vue'
import api from '@/composables/useApi'
import { MagnifyingGlassIcon, FunnelIcon, ArrowsUpDownIcon } from '@heroicons/vue/24/outline'

const orders = ref([])
const activeTab = ref('All')
const searchQuery = ref('')
const selectedOrders = ref([])
const router = useRouter()

const tabs = ['All', 'Unfulfilled', 'Unpaid', 'Open', 'Closed']

const fetchOrders = async () => {
  try {
    const response = await api.get('/orders')
    orders.value = response.data.orders.data || []
  } catch (error) {
    console.error('Failed to fetch orders', error)
  }
}

const filteredOrders = computed(() => {
  let result = orders.value

  // Tab Filtering (Mock logic based on status)
  if (activeTab.value === 'Unpaid') {
    result = result.filter(o => o.payment_status === 'pending' || o.payment_status === 'failed')
  } else if (activeTab.value === 'Closed') {
    result = result.filter(o => o.status === 'completed' || o.status === 'cancelled')
  } else if (activeTab.value === 'Open') {
    result = result.filter(o => o.status !== 'completed' && o.status !== 'cancelled')
  } 
  // 'Unfulfilled' would check fulfillment status if we had it, assuming 'pending' order status = unfulfilled
  else if (activeTab.value === 'Unfulfilled') {
     result = result.filter(o => o.status === 'pending')
  }

  // Search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(o => 
      o.id.toString().includes(query) ||
      o.customer?.name?.toLowerCase().includes(query) ||
      o.customer?.email?.toLowerCase().includes(query)
    )
  }

  return result
})

const toggleSelection = (id) => {
  if (selectedOrders.value.includes(id)) {
    selectedOrders.value = selectedOrders.value.filter(i => i !== id)
  } else {
    selectedOrders.value.push(id)
  }
}

const toggleAll = () => {
  if (selectedOrders.value.length === filteredOrders.value.length) {
    selectedOrders.value = []
  } else {
    selectedOrders.value = filteredOrders.value.map(o => o.id)
  }
}

// Helper for status colors
const getPaymentStatusColor = (status) => {
  switch (status) {
    case 'paid': return 'bg-gray-100 text-gray-800' // Shopify style for Paid is often grey/neutral
    case 'pending': return 'bg-yellow-100 text-yellow-800'
    case 'failed': return 'bg-red-100 text-red-800'
    default: return 'bg-gray-100 text-gray-800'
  }
}

const getFulfillmentStatusColor = (status) => {
    // Mapping order status to fulfillment for demo
    if (status === 'completed') return 'bg-gray-100 text-gray-800' // Fulfilled
    return 'bg-yellow-100 text-yellow-800' // Unfulfilled
}

onMounted(fetchOrders)
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="sm:flex sm:items-center">
      <div class="sm:flex-auto">
        <h1 class="text-xl font-bold leading-6 text-gray-900">Orders</h1>
      </div>
      <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none space-x-3">
        <BaseButton variant="secondary">Export</BaseButton>
        <BaseButton variant="primary">Create order</BaseButton>
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
            @click="activeTab = tab"
            :class="[
              activeTab === tab ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700',
              'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium'
            ]"
          >
            {{ tab }}
          </button>
        </nav>
      </div>

      <!-- Search & Filters -->
      <div class="p-4 border-b border-gray-200 flex gap-4">
        <div class="relative flex-grow max-w-md">
          <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
          </div>
          <input 
            v-model="searchQuery"
            type="text" 
            class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
            placeholder="Filter orders" 
          />
        </div>
        <BaseButton variant="secondary" class="!px-3">
            <FunnelIcon class="h-5 w-5 text-gray-500" />
        </BaseButton>
        <BaseButton variant="secondary" class="!px-3">
            <ArrowsUpDownIcon class="h-5 w-5 text-gray-500" />
        </BaseButton>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-300 text-left">
          <thead class="bg-gray-50">
            <tr>
              <th scope="col" class="py-3.5 pl-4 pr-3 sm:pl-6 w-10">
                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" @change="toggleAll" :checked="selectedOrders.length === filteredOrders.length && filteredOrders.length > 0" />
              </th>
              <th scope="col" class="py-3.5 pl-4 pr-3 text-xs font-semibold uppercase tracking-wide text-gray-500 sm:pl-0">Order</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Customer</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Total</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Payment</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500">Fulfillment</th>
              <th scope="col" class="px-3 py-3.5 text-xs font-semibold uppercase tracking-wide text-gray-500 text-right">Items</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="order in filteredOrders" :key="order.id" @click="router.push(`/admin/orders/${order.id}`)" class="hover:bg-gray-50 transition-colors cursor-pointer">
              <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-6">
                <input type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" :checked="selectedOrders.includes(order.id)" @change="toggleSelection(order.id)" @click.stop />
              </td>
              <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-gray-900 sm:pl-0 cursor-pointer">
                {{ order.order_number || ('#' + order.id) }}
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ new Date(order.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric' }) }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">{{ order.customer?.name || 'Guest' }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">${{ order.total }}</td>
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                <Badge :variant="order.payment_status === 'paid' ? 'success' : (order.payment_status === 'pending' ? 'warning' : 'critical')">
                    {{ order.payment_status }}
                </Badge>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm">
                 <Badge :variant="order.status === 'completed' ? 'success' : 'warning'">
                    {{ order.status === 'completed' ? 'Fulfilled' : 'Unfulfilled' }}
                </Badge>
              </td>
              <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-right">{{ order.items?.length || 0 }} items</td>
            </tr>
            <tr v-if="filteredOrders.length === 0">
                <td colspan="8" class="text-center py-12 text-gray-500">
                    No orders found matching your filters.
                </td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <!-- Footer / Pagination -->
      <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-b-lg">
         <div class="flex flex-1 justify-between sm:hidden">
            <a href="#" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
            <a href="#" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
         </div>
         <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
               <p class="text-sm text-gray-700">Showing <span class="font-medium">1</span> to <span class="font-medium">{{ filteredOrders.length }}</span> of <span class="font-medium">{{ orders.length }}</span> results</p>
            </div>
            <div>
               <!-- Simple pagination placeholder -->
               <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                  <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                     <span class="sr-only">Previous</span>
                     <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                  </a>
                  <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-indigo-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">1</a>
                  <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                     <span class="sr-only">Next</span>
                     <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                  </a>
               </nav>
            </div>
         </div>
      </div>

    </div>
  </div>
</template>
