<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/composables/useApi'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import Badge from '@/components/ui/Badge.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'

const route = useRoute()
const router = useRouter()
const order = ref(null)
const loading = ref(true)

onMounted(async () => {
    try {
        const response = await api.get(`/orders/${route.params.id}`)
        order.value = response.data.order
    } catch (e) {
        console.error('Failed to load order', e)
    } finally {
        loading.value = false
    }
})
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
      <div class="flex items-center gap-4">
          <button @click="router.push('/admin/orders')" class="text-gray-500 hover:text-gray-700">
              <ArrowLeftIcon class="h-5 w-5" />
          </button>
          <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
              {{ order?.order_number || 'Loading...' }}
              <Badge v-if="order" :variant="order.payment_status === 'paid' ? 'success' : (order.payment_status === 'pending' ? 'warning' : 'critical')">
                  {{ order.payment_status }}
              </Badge>
               <Badge v-if="order" :variant="order.fulfillment_status === 'fulfilled' ? 'success' : 'neutral'">
                  {{ order.fulfillment_status }}
              </Badge>
          </h1>
      </div>
      <div class="text-sm text-gray-500" v-if="order">
          {{ new Date(order.created_at).toLocaleString() }}
      </div>
    </div>

    <div v-if="loading">
        <SkeletonLoader type="card" :count="3" />
    </div>

    <div v-else-if="order" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Line Items -->
            <BaseCard>
                <div class="overflow-hidden">
                    <table class="min-w-full text-left text-sm whitespace-nowrap">
                        <thead class="uppercase tracking-wider border-b-2 border-gray-100 bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 font-semibold text-gray-400">Product</th>
                                <th scope="col" class="px-6 py-3 font-semibold text-gray-400 text-right">Price</th>
                                <th scope="col" class="px-6 py-3 font-semibold text-gray-400 text-right">Quantity</th>
                                <th scope="col" class="px-6 py-3 font-semibold text-gray-400 text-right">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="item in order.items" :key="item.id" class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 flex items-center gap-4">
                                    <div class="h-12 w-12 rounded bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0">
                                        <img v-if="item.image" :src="item.image" class="h-full w-full object-cover">
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ item.product_name }}</div>
                                        <div class="text-gray-500 text-xs mt-0.5" v-if="item.variant_name">{{ item.variant_name }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">${{ item.price }}</td>
                                <td class="px-6 py-4 text-right">{{ item.quantity }}</td>
                                <td class="px-6 py-4 text-right font-medium">${{ item.total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </BaseCard>

            <!-- Payment Summary -->
            <BaseCard title="Payment">
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span>${{ order.subtotal }}</span>
                    </div>
                     <div class="flex justify-between text-gray-600">
                        <span>Discount</span>
                        <span>-${{ order.discount_amount || '0.00' }}</span>
                    </div>
                     <div class="flex justify-between text-gray-600">
                        <span>Shipping</span>
                        <span>${{ order.shipping_amount }}</span>
                    </div>
                     <div class="flex justify-between text-gray-600">
                        <span>Tax</span>
                        <span>${{ order.tax_amount }}</span>
                    </div>
                    <div class="pt-3 border-t border-gray-200 flex justify-between font-bold text-gray-900 text-base">
                        <span>Total</span>
                        <span>${{ order.total }}</span>
                    </div>
                </div>
            </BaseCard>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer -->
            <BaseCard title="Customer">
                <div class="text-sm text-gray-600" v-if="order.customer">
                    <div class="font-medium text-gray-900 mb-1">{{ order.customer.name }}</div>
                    <div>{{ order.customer.email }}</div>
                </div>
                 <div v-else class="text-sm text-gray-500 italic">No customer linked</div>
            </BaseCard>

            <!-- Shipping Address -->
             <BaseCard title="Shipping Address">
                <div class="text-sm text-gray-600" v-if="order.shipping_address">
                    <div class="font-medium text-gray-900 mb-1">{{ order.shipping_address.first_name }} {{ order.shipping_address.last_name }}</div>
                    <div>{{ order.shipping_address.address1 }}</div>
                    <div v-if="order.shipping_address.address2">{{ order.shipping_address.address2 }}</div>
                    <div>{{ order.shipping_address.city }}, {{ order.shipping_address.province }} {{ order.shipping_address.zip }}</div>
                    <div>{{ order.shipping_address.country }}</div>
                    <div class="mt-2">{{ order.shipping_address.phone }}</div>
                </div>
                <div v-else class="text-sm text-gray-500 italic">No shipping address</div>
            </BaseCard>

            <!-- Notes -->
             <BaseCard title="Notes" v-if="order.notes">
                <p class="text-sm text-gray-600">{{ order.notes }}</p>
            </BaseCard>
        </div>
    </div>
  </div>
</template>
