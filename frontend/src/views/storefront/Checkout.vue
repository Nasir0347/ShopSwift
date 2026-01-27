<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import { useCartStore } from '@/stores/cart'
import api from '@/composables/useApi'

const router = useRouter()
const cartStore = useCartStore()

const steps = ['Shipping', 'Payment', 'Review']
const currentStep = ref(0)
const loading = ref(false)

const form = ref({
  first_name: '',
  last_name: '',
  address: '',
  payment_method: 'cod' 
})

const canProceed = computed(() => {
  if (currentStep.value === 0) {
    return form.value.first_name && form.value.last_name && form.value.address
  }
  return true
})

const submitOrder = async () => {
  loading.value = true
  try {
    const orderData = {
      items: cartStore.items.map(item => ({
        variant_id: item.variants?.[0]?.id || 1, // Fallback for basic products without variants structure in cart
        quantity: item.quantity
      })),
      payment_method: 'cod',
      address: form.value
    }
    
    // Fallback: if no variant_id, we need to handle it. 
    // Assuming 'Classic T-Shirt' added from home has structure.
    // For now, let's assume backend valid variant exists.
    
    await api.post('/orders', orderData)
    cartStore.items = [] // Clear cart
    alert('Order placed successfully!')
    router.push('/')
  } catch (error) {
    console.error('Order failed', error)
    alert('Failed to place order: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-2xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-8">Checkout</h1>
    
    <nav aria-label="Progress">
      <ol role="list" class="space-y-4 md:flex md:space-y-0 md:space-x-8 mb-10">
        <li v-for="(step, index) in steps" :key="step" class="md:flex-1">
          <a href="#" :class="[
            index <= currentStep ? 'border-indigo-600' : 'border-gray-200 hover:border-gray-300',
             'flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pl-0 md:pt-4 md:pb-0'
          ]">
            <span :class="[index <= currentStep ? 'text-indigo-600' : 'text-gray-500', 'text-sm font-medium']">Step {{ index + 1 }}</span>
            <span class="text-sm font-medium">{{ step }}</span>
          </a>
        </li>
      </ol>
    </nav>

    <BaseCard>
      <div v-if="currentStep === 0" class="space-y-6">
         <h2 class="text-lg font-medium text-gray-900">Shipping Information</h2>
         <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
           <div class="sm:col-span-3">
             <BaseInput label="First name" v-model="form.first_name" placeholder="Atul" />
           </div>
           <div class="sm:col-span-3">
             <BaseInput label="Last name" v-model="form.last_name" placeholder="Goyal" />
           </div>
           <div class="sm:col-span-6">
             <BaseInput label="Address" v-model="form.address" placeholder="123 Main St" />
           </div>
         </div>
      </div>
      
      <div v-else-if="currentStep === 1" class="space-y-6">
         <h2 class="text-lg font-medium text-gray-900">Payment Method</h2>
         <div class="flex items-center">
            <input id="cod" name="payment_method" type="radio" checked class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600" />
            <label for="cod" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Cash on Delivery</label>
         </div>
      </div>

      <div v-else class="text-center py-10">
         <h2 class="text-lg font-medium text-gray-900 mb-4">Review Order</h2>
         <p class="text-gray-500">{{ cartStore.totalItems }} items in cart</p>
         <p class="text-xl font-bold mt-2">Total: ${{ cartStore.subtotal }}</p>
      </div>

      <template #footer>
        <div class="flex justify-end">
          <BaseButton :disabled="currentStep === 0" variant="secondary" @click="currentStep--" class="mr-3" v-if="currentStep > 0">Back</BaseButton>
          <BaseButton 
            variant="primary" 
            @click="currentStep++" 
            :disabled="!canProceed"
            v-if="currentStep < steps.length - 1"
          >
            Continue
          </BaseButton>
          <BaseButton 
            variant="primary" 
            :loading="loading"
            @click="submitOrder"
            v-else
          >
            Place Order
          </BaseButton>
        </div>
      </template>
    </BaseCard>
  </div>
</template>
